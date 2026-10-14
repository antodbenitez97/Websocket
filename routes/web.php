<?php

use App\Http\Controllers\PartidaController;
use Illuminate\Support\Facades\Route;

Route::get('/', [PartidaController::class, 'lobby'])->name('lobby');
Route::post('/partidas', [PartidaController::class, 'crear'])->name('crear');
Route::get('/partidas/{codigo}', [PartidaController::class, 'mostrar'])->name('partida');
Route::post('/partidas/{codigo}/jugar', [PartidaController::class, 'jugar'])->name('jugar');
Route::post('/partidas/{codigo}/revancha', [PartidaController::class, 'revancha'])->name('revancha');