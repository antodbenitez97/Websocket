<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Sala {{ $codigo }} · Fuego vs Hielo</title>
    @vite(['resources/css/app.css','resources/js/app.js'])
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.jsdelivr.net/npm/canvas-confetti@1.9.3/dist/confetti.browser.min.js"></script>
    <style>
        :root {
            --bg: #0b1120;
            --card: #1e293b;
            --line: #334155;
            --fuego: #fb923c;
            --hielo: #38bdf8;
            --ok: #4ade80;
            --txt: #e2e8f0;
            --muted: #94a3b8;
        }

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            min-height: 100vh;
            display: grid;
            place-items: center;
            padding: 16px;
            color: var(--txt);
            font-family: system-ui, sans-serif;
            background: radial-gradient(circle at 15% 20%, rgba(251, 146, 60, .15), transparent 45%),
                radial-gradient(circle at 85% 80%, rgba(56, 189, 248, .15), transparent 45%), var(--bg);
        }

        .card {
            background: var(--card);
            padding: 24px;
            border-radius: 22px;
            width: min(94vw, 420px);
            text-align: center;
            box-shadow: 0 20px 60px rgba(0, 0, 0, .5);
        }

        h1 {
            margin: 0 0 10px;
            font-size: 1.4rem;
        }

        .sala {
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 8px;
            margin-bottom: 16px;
            color: var(--muted);
            font-size: .9rem;
        }

        .sala code {
            color: var(--txt);
            letter-spacing: 3px;
            font-weight: 800;
            background: var(--bg);
            padding: 4px 10px;
            border-radius: 8px;
            border: 1px dashed var(--line);
        }

        .mini {
            background: var(--bg);
            color: var(--txt);
            border: 1px solid var(--line);
            padding: 4px 10px;
            border-radius: 8px;
            cursor: pointer;
        }

        .marcador {
            display: grid;
            grid-template-columns: 1fr auto 1fr;
            gap: 8px;
            align-items: stretch;
            margin-bottom: 14px;
        }

        .jug {
            background: var(--bg);
            border: 2px solid var(--line);
            border-radius: 14px;
            padding: 8px 6px;
            position: relative;
            transition: .2s;
        }

        .jug b {
            display: block;
            font-size: .85rem;
        }

        .jug strong {
            font-size: 1.8rem;
            display: block;
        }

        .jug.j1 {
            color: var(--fuego);
        }

        .jug.j2 {
            color: var(--hielo);
        }

        .jug.turno {
            border-color: currentColor;
            box-shadow: 0 0 14px -2px currentColor;
        }

        .punto {
            position: absolute;
            top: 8px;
            right: 8px;
            width: 9px;
            height: 9px;
            border-radius: 50%;
            background: #475569;
        }

        .jug.online .punto {
            background: var(--ok);
            box-shadow: 0 0 8px var(--ok);
        }

        .vos {
            display: none;
            font-size: .65rem;
            background: currentColor;
            border-radius: 6px;
            padding: 1px 6px;
            margin-top: 2px;
        }

        .vos span {
            color: var(--bg);
            font-weight: 800;
        }

        .jug.mio .vos {
            display: inline-block;
        }

        .vs {
            display: flex;
            flex-direction: column;
            justify-content: center;
            color: var(--muted);
            font-size: .75rem;
        }

        .vs strong {
            font-size: 1.2rem;
            color: var(--txt);
        }

        .estado {
            padding: 11px;
            border-radius: 12px;
            margin-bottom: 14px;
            background: var(--bg);
            color: var(--muted);
            font-weight: 600;
        }

        .estado.activo {
            color: var(--txt);
            outline: 2px solid var(--hielo);
        }

        .estado.ok {
            color: var(--ok);
            outline: 2px solid var(--ok);
        }

        #tablero {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 10px;
        }

        .celda {
            aspect-ratio: 1;
            font-size: 2.6rem;
            background: var(--bg);
            border: 2px solid var(--line);
            border-radius: 14px;
            cursor: pointer;
            color: var(--txt);
            transition: transform .12s, background .2s;
            position: relative;
        }

        .celda:not(:disabled):hover {
            transform: scale(1.05);
            background: #162033;
        }

        .celda:not(:disabled):hover::after {
            content: attr(data-prev);
            opacity: .3;
            position: absolute;
            inset: 0;
            display: grid;
            place-items: center;
        }

        .celda:disabled {
            cursor: not-allowed;
        }

        .celda.j1 {
            border-color: rgba(251, 146, 60, .6);
        }

        .celda.j2 {
            border-color: rgba(56, 189, 248, .6);
        }

        .celda.nueva {
            animation: pop .35s ease-out;
        }

        .celda.gana {
            background: rgba(74, 222, 128, .18);
            border-color: var(--ok);
            animation: latido 1s ease-in-out infinite;
        }

        @keyframes pop {
            0% {
                transform: scale(0) rotate(-40deg);
            }

            70% {
                transform: scale(1.25) rotate(8deg);
            }

            100% {
                transform: scale(1);
            }
        }

        @keyframes latido {
            50% {
                box-shadow: 0 0 0 6px rgba(74, 222, 128, .25);
            }
        }

        .ronda {
            color: var(--muted);
            font-size: .85rem;
            margin: 12px 0 0;
        }

        .fin {
            display: flex;
            flex-direction: column;
            gap: 10px;
            margin-top: 16px;
        }

        .fin[hidden] {
            display: none;
        }

        .btn {
            display: block;
            width: 100%;
            padding: 12px;
            border-radius: 12px;
            border: 1px solid var(--line);
            background: var(--bg);
            color: var(--txt);
            text-decoration: none;
            font-size: 1rem;
            cursor: pointer;
        }

        .btn.primario {
            background: linear-gradient(90deg, var(--fuego), var(--hielo));
            color: #0b1120;
            font-weight: 800;
            border: 0;
        }
    </style>
</head>

<body>
    <div class="card">
        <h1>🔥 Fuego vs Hielo ❄️</h1>

        <div class="sala">
            Sala <code>{{ $codigo }}</code>
            <button id="copiar" class="mini" type="button">Copiar</button>
            <button id="sonido" class="mini" type="button">🔊</button>
        </div>

        <div class="marcador">
            <div id="p1" class="jug j1">
                <i class="punto"></i><b>🔥 Jugador 1</b><strong id="s1">0</strong><em class="vos"><span>VOS</span></em>
            </div>
            <div class="vs"><span>Empates</span><strong id="se">0</strong></div>
            <div id="p2" class="jug j2">
                <i class="punto"></i><b>❄️ Jugador 2</b><strong id="s2">0</strong><em class="vos"><span>VOS</span></em>
            </div>
        </div>

        <div id="mensaje" class="estado"></div>
        <div id="tablero"></div>
        <p class="ronda">Ronda <span id="ronda">1</span></p>

        <div id="fin" class="fin" hidden>
            <button id="btnRevancha" class="btn primario" type="button">⚔️ Pedir revancha</button>
            <form method="POST" action="{{ route('crear') }}">
                @csrf
                <button type="submit" class="btn">Crear otra sala</button>
            </form>
            <a class="btn" href="{{ route('lobby') }}">Volver al inicio</a>
        </div>
    </div>

    <script>
        const codigo = @json($codigo);
        const miMarca = @json($marca); // 1, 2 o null (espectador)
        let estado = @json($estado);

        const ICONO = {
            1: '🔥',
            2: '❄️'
        };
        const NOMBRE = {
            1: 'Jugador 1',
            2: 'Jugador 2'
        };
        const URL_JUGAR = @json(route('jugar', $codigo));
        const URL_REVANCHA = @json(route('revancha', $codigo));
        const URL_LOBBY = @json(route('lobby'));
        const csrf = document.querySelector('meta[name=csrf-token]').content;
        const $ = (id) => document.getElementById(id);
        const TEMA = {
            background: '#1e293b',
            color: '#e2e8f0'
        };

        let conectados = new Set();
        let tableroAntes = [...estado.tablero];

        // ---------- sonido (WebAudio, sin archivos) ----------
        let audioCtx = null;
        let mute = false;
        try {
            mute = localStorage.getItem('mute') === '1';
        } catch {}

        document.addEventListener('pointerdown', () => {
            audioCtx ??= new(window.AudioContext || window.webkitAudioContext)();
            audioCtx.resume();
        }, {
            once: true
        });

        function tono(freq, dur = 0.12, tipo = 'sine', delay = 0) {
            if (mute || !audioCtx) return;
            const o = audioCtx.createOscillator(),
                g = audioCtx.createGain();
            const t = audioCtx.currentTime + delay;
            o.type = tipo;
            o.frequency.value = freq;
            g.gain.setValueAtTime(0.15, t);
            g.gain.exponentialRampToValueAtTime(0.001, t + dur);
            o.connect(g);
            g.connect(audioCtx.destination);
            o.start(t);
            o.stop(t + dur);
        }
        const sonido = {
            jugada: (m) => tono(m === 1 ? 520 : 390, 0.1, 'triangle'),
            ronda: () => {
                tono(440, 0.12);
                tono(660, 0.12, 'sine', 0.12);
            },
            ganar: () => [523, 659, 784, 1046].forEach((f, i) => tono(f, 0.2, 'triangle', i * 0.12)),
            perder: () => [392, 330, 262].forEach((f, i) => tono(f, 0.25, 'sawtooth', i * 0.15)),
            empate: () => {
                tono(330, 0.2);
                tono(330, 0.2, 'sine', 0.22);
            },
        };

        // ---------- extras visuales ----------
        function confeti() {
            if (typeof confetti !== 'function') return;
            const hasta = Date.now() + 1500;
            (function frame() {
                confetti({
                    particleCount: 6,
                    angle: 60,
                    spread: 70,
                    origin: {
                        x: 0
                    }
                });
                confetti({
                    particleCount: 6,
                    angle: 120,
                    spread: 70,
                    origin: {
                        x: 1
                    }
                });
                if (Date.now() < hasta) requestAnimationFrame(frame);
            })();
        }

        function toast(icon, title) {
            if (!window.Swal) return;
            Swal.fire({
                toast: true,
                position: 'top',
                icon,
                title,
                showConfirmButton: false,
                timer: 2200,
                timerProgressBar: true,
                ...TEMA
            });
        }

        // ---------- dibujar ----------
        function render() {
            const terminada = !!estado.ganador || estado.empate;
            const miTurno = !!miMarca && estado.rivalListo && !terminada && estado.turno === miMarca;

            tablero.innerHTML = '';
            estado.tablero.forEach((valor, i) => {
                const b = document.createElement('button');
                b.className = 'celda' +
                    (valor ? ' j' + valor : '') +
                    (valor && tableroAntes[i] !== valor ? ' nueva' : '') +
                    (estado.linea?.includes(i) ? ' gana' : '');
                b.textContent = valor ? ICONO[valor] : '';
                b.dataset.prev = miMarca ? ICONO[miMarca] : '';
                b.disabled = valor !== null || !miTurno;
                b.onclick = () => jugar(i);
                tablero.appendChild(b);
            });

            // marcador
            $('s1').textContent = estado.marcador.j1;
            $('s2').textContent = estado.marcador.j2;
            $('se').textContent = estado.marcador.empates;
            $('ronda').textContent = estado.ronda;
            [1, 2].forEach(n => {
                const p = $('p' + n);
                p.classList.toggle('turno', estado.rivalListo && !terminada && estado.turno === n);
                p.classList.toggle('online', conectados.has(n));
                p.classList.toggle('mio', miMarca === n);
            });

            // mensaje
            let texto, clase = '';
            if (!estado.rivalListo) {
                texto = '⏳ Esperando al Jugador 2... pasale el código ' + codigo;
            } else if (estado.ganador) {
                if (!miMarca) texto = '🏆 Ganó el ' + NOMBRE[estado.ganador];
                else if (estado.ganador === miMarca) {
                    texto = '🏆 ¡Ganaste la ronda!';
                    clase = 'ok';
                } else texto = '💥 Esta ronda fue para el ' + NOMBRE[estado.ganador];
            } else if (estado.empate) {
                texto = '🤝 Empate, ¡qué parejo!';
            } else if (miTurno) {
                texto = '🎯 ¡Es tu turno!';
                clase = 'activo';
            } else {
                texto = '⌛ Juega el ' + NOMBRE[estado.turno] + '...';
            }
            $('mensaje').textContent = texto;
            $('mensaje').className = 'estado ' + clase;

            $('fin').hidden = !(terminada && miMarca);
        }

        // ---------- efectos al cambiar el estado ----------
        function efectos(antes, ahora) {
            const eraFin = !!antes.ganador || antes.empate;
            const esFin = !!ahora.ganador || ahora.empate;

            if (ahora.ronda > antes.ronda) {
                sonido.ronda();
                toast('info', 'Ronda ' + ahora.ronda + ' · empieza el ' + NOMBRE[ahora.turno]);
                return;
            }

            const celdaNueva = ahora.tablero.findIndex((v, i) => v && !antes.tablero[i]);
            if (celdaNueva >= 0 && !esFin) sonido.jugada(ahora.tablero[celdaNueva]);

            if (esFin && !eraFin) finDeRonda(ahora);
        }

        function finDeRonda(ahora) {
            let icon, title, text = '';
            if (ahora.empate) {
                sonido.empate();
                icon = 'info';
                title = '¡Empate!';
                text = 'Nadie pudo con nadie.';
            } else if (ahora.ganador === miMarca) {
                sonido.ganar();
                confeti();
                icon = 'success';
                title = '¡Ganaste! 🎉';
                text = 'Esta ronda es tuya.';
            } else {
                sonido.perder();
                icon = 'error';
                title = 'Perdiste esta ronda';
                text = 'Ganó el ' + NOMBRE[ahora.ganador] + '. ¡Pedí la revancha!';
            }
            if (!miMarca || !window.Swal) return;

            Swal.fire({
                icon,
                title,
                text,
                ...TEMA,
                showDenyButton: true,
                showCancelButton: true,
                confirmButtonText: '⚔️ Revancha',
                denyButtonText: 'Inicio',
                cancelButtonText: 'Ver tablero',
            }).then(r => {
                if (r.isConfirmed) revancha();
                else if (r.isDenied) location.href = URL_LOBBY;
            });
        }

        // ---------- peticiones al servidor ----------
        function post(url, body = {}) {
            return fetch(url, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrf
                },
                body: JSON.stringify(body),
            });
        }

        async function jugar(celda) {
            const r = await post(URL_JUGAR, {
                celda
            });
            if (!r.ok) {
                const err = await r.json().catch(() => ({}));
                toast('warning', err.message ?? 'Jugada inválida');
            }
        }

        async function revancha() {
            // Si el rival ya la pidió, el servidor responde 422 y no pasa nada: el tablero ya se reinició
            await post(URL_REVANCHA);
        }

        // ---------- botones ----------
        $('btnRevancha').onclick = revancha;

        $('copiar').onclick = async (e) => {
            await navigator.clipboard.writeText(codigo);
            e.target.textContent = '¡Copiado!';
            setTimeout(() => e.target.textContent = 'Copiar', 1500);
        };

        const btnSonido = $('sonido');
        btnSonido.textContent = mute ? '🔇' : '🔊';
        btnSonido.onclick = () => {
            mute = !mute;
            try {
                localStorage.setItem('mute', mute ? '1' : '0');
            } catch {}
            btnSonido.textContent = mute ? '🔇' : '🔊';
        };

        // ---------- tiempo real con Echo + Reverb ----------
        const tablero = $('tablero');

        document.addEventListener('DOMContentLoaded', () => {
            render();

            // Canal público: el estado del juego
            window.Echo.channel('partida.' + codigo)
                .listen('.PartidaActualizada', (e) => {
                    const antes = estado;
                    estado = e.estado;
                    render();
                    efectos(antes, estado);
                    tableroAntes = [...estado.tablero];
                });

            // Canal de presencia: quién está conectado (solo los 2 jugadores)
            window.Echo.join('presencia.' + codigo)
                .here((usuarios) => {
                    conectados = new Set(usuarios.map(u => u.marca));
                    render();
                })
                .joining((u) => {
                    conectados.add(u.marca);
                    render();
                    if (u.marca !== miMarca) toast('success', NOMBRE[u.marca] + ' se conectó 🟢');
                })
                .leaving((u) => {
                    conectados.delete(u.marca);
                    render();
                    if (u.marca !== miMarca) toast('warning', NOMBRE[u.marca] + ' se desconectó 🔴');
                })
                .error(() => {
                    /* espectadores: no tienen presencia */ });
        });
    </script>
</body>

</html>