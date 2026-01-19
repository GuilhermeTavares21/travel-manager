<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PedidoController;

Route::middleware('auth:api')->group(function () {
    Route::get('/pedidos',               [PedidoController::class, 'index']);
    Route::post('/pedidos',              [PedidoController::class, 'store']);
    Route::get('/pedidos/{id}',          [PedidoController::class, 'show']);
    Route::patch('/pedidos/{id}/status', [PedidoController::class, 'updateStatus']);
});
    