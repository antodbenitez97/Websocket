<?php
use Illuminate\Support\Facades\Cache;

// Presencia: solo entran los 2 jugadores de la partida
Broadcast::channel('presencia.{codigo}', function ($user, string $codigo) {
    $partida = Cache::get('partida:' . strtoupper($codigo));
    if (!$partida) return false;

    $marca = array_search($user->id, $partida['jugadores'], true);
    if ($marca === false) return false;

    return ['marca' => $marca, 'nombre' => "Jugador $marca"];
});