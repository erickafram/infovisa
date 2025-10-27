<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\CnpjController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

// Rotas da API para consulta de CNPJ
Route::post('/consultar-cnpj', [CnpjController::class, 'consultar']);
Route::get('/verificar-cnpj/{cnpj}', [CnpjController::class, 'verificarExistente']);

// Rota para verificar competência de atividades
Route::post('/verificar-competencia', [CnpjController::class, 'verificarCompetencia']);
