<?php

namespace App\Http\Controllers;

use App\Events\PartidaActualizada;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class PartidaController extends Controller
{
    private const LINEAS = [
        [0,1,2],[3,4,5],[6,7,8],
        [0,3,6],[1,4,7],[2,5,8],
        [0,4,8],[2,4,6],
    ];

    public function lobby()
    {
        return view('lobby');
    }

    public function crear()
    {
        // Sin letras/números que se confundan (O/0, I/1)
        $codigo = substr(str_shuffle('ABCDEFGHJKLMNPQRSTUVWXYZ23456789'), 0, 5);

        $this->guardar($codigo, [
            'tablero'   => array_fill(0, 9, null),
            'turno'     => 1,
            'empieza'   => 1,
            'ganador'   => null,
            'linea'     => null,
            'empate'    => false,
            'ronda'     => 1,
            'marcador'  => ['j1' => 0, 'j2' => 0, 'empates' => 0],
            'jugadores' => [1 => $this->jugadorId(), 2 => null],
        ]);

        return redirect()->route('partida', $codigo);
    }

    public function mostrar(string $codigo)
    {
        $codigo = strtoupper($codigo);
        $id = $this->jugadorId();

        $partida = $this->conBloqueo($codigo, function () use ($codigo, $id) {
            $partida = $this->obtener($codigo);

            // Si hay lugar, quien entra se une como Jugador 2
            if (!in_array($id, $partida['jugadores'], true) && $partida['jugadores'][2] === null) {
                $partida['jugadores'][2] = $id;
                $this->guardar($codigo, $partida);
                broadcast(new PartidaActualizada($codigo, $this->publico($partida)));
            }
            return $partida;
        });

        $marca = array_search($id, $partida['jugadores'], true);

        return view('partida', [
            'codigo' => $codigo,
            'marca'  => $marca === false ? null : $marca,
            'estado' => $this->publico($partida),
        ]);
    }

    public function jugar(Request $request, string $codigo)
    {
        $codigo = strtoupper($codigo);
        $data = $request->validate(['celda' => ['required', 'integer', 'between:0,8']]);
        $id = $this->jugadorId();

        return $this->conBloqueo($codigo, function () use ($codigo, $data, $id) {
            $partida = $this->obtener($codigo);
            $marca = array_search($id, $partida['jugadores'], true);

            // El servidor valida TODO
            abort_if($marca === false, 403, 'Vos no sos parte de esta partida');
            abort_if($partida['jugadores'][2] === null, 422, 'Todavía falta tu rival');
            abort_if($partida['ganador'] || $partida['empate'], 422, 'La ronda ya terminó');
            abort_if($partida['turno'] !== $marca, 422, 'Pará, todavía no es tu turno');
            abort_if($partida['tablero'][$data['celda']] !== null, 422, 'Esa casilla ya está ocupada');

            $partida['tablero'][$data['celda']] = $marca;

            foreach (self::LINEAS as [$a, $b, $c]) {
                $t = $partida['tablero'];
                if ($t[$a] && $t[$a] === $t[$b] && $t[$a] === $t[$c]) {
                    $partida['ganador'] = $t[$a];
                    $partida['linea'] = [$a, $b, $c];
                    break;
                }
            }

            if ($partida['ganador']) {
                $partida['marcador']["j{$marca}"]++;
            } elseif (!in_array(null, $partida['tablero'], true)) {
                $partida['empate'] = true;
                $partida['marcador']['empates']++;
            } else {
                $partida['turno'] = $marca === 1 ? 2 : 1;
            }

            $this->guardar($codigo, $partida);
            broadcast(new PartidaActualizada($codigo, $this->publico($partida)));

            return response()->json(['ok' => true]);
        });
    }

    public function revancha(string $codigo)
    {
        $codigo = strtoupper($codigo);
        $id = $this->jugadorId();

        return $this->conBloqueo($codigo, function () use ($codigo, $id) {
            $partida = $this->obtener($codigo);

            abort_if(!in_array($id, $partida['jugadores'], true), 403, 'Vos no sos parte de esta partida');
            abort_if(!$partida['ganador'] && !$partida['empate'], 422, 'La ronda todavía no terminó');

            // Se alterna quién empieza y se conserva el marcador
            $partida['empieza'] = $partida['empieza'] === 1 ? 2 : 1;
            $partida['turno']   = $partida['empieza'];
            $partida['tablero'] = array_fill(0, 9, null);
            $partida['ganador'] = null;
            $partida['linea']   = null;
            $partida['empate']  = false;
            $partida['ronda']++;

            $this->guardar($codigo, $partida);
            broadcast(new PartidaActualizada($codigo, $this->publico($partida)));

            return response()->json(['ok' => true]);
        });
    }

    // ---------- helpers ----------

    // Identidad anónima del jugador (no es el ID de sesión, así no se filtra)
    private function jugadorId(): string
    {
        if (!session()->has('jugador_id')) {
            session()->put('jugador_id', (string) Str::uuid());
        }
        return session('jugador_id');
    }

    // Evita que dos jugadas simultáneas se pisen
    private function conBloqueo(string $codigo, callable $fn)
    {
        return Cache::lock("lock:partida:$codigo", 5)->block(3, $fn);
    }

    private function obtener(string $codigo): array
    {
        return Cache::get("partida:$codigo") ?? abort(404, 'Esa sala no existe o ya venció');
    }

    private function guardar(string $codigo, array $partida): void
    {
        Cache::put("partida:$codigo", $partida, now()->addHours(2));
    }

    // Lo que viaja al navegador (sin identificadores de nadie)
    private function publico(array $p): array
    {
        return [
            'tablero'    => $p['tablero'],
            'turno'      => $p['turno'],
            'ganador'    => $p['ganador'],
            'linea'      => $p['linea'],
            'empate'     => $p['empate'],
            'ronda'      => $p['ronda'],
            'marcador'   => $p['marcador'],
            'rivalListo' => $p['jugadores'][2] !== null,
        ];
    }
}