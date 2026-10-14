<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;

class PartidaActualizada implements ShouldBroadcastNow
{
    public function __construct(
        public string $codigo,
        public array $estado
    ) {}

    public function broadcastOn(): array
    {
        return [new Channel("partida.{$this->codigo}")];
    }

    public function broadcastAs(): string
    {
        return 'PartidaActualizada';
    }

    public function broadcastWith(): array
    {
        return ['estado' => $this->estado];
    }
}