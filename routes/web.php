<?php

use App\Http\Controllers\EventoController;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\ModeracaoController;
use App\Http\Controllers\PerguntaController;
use App\Http\Controllers\RegisterController;
use Illuminate\Support\Facades\Route;


Route::middleware('auth')->group(function () {
    Route::get('/eventos/create', [EventoController::class, 'create'])->name('eventos.create');
    Route::post('/eventos', [EventoController::class, 'store'])->name('eventos.store');
    Route::patch('/eventos/{evento}/status', [EventoController::class, 'updateStatus'])
        ->name('eventos.status');
    Route::get('/user/eventos', [EventoController::class, 'userEventos'])->name('user.eventos');
    Route::post('/perguntas/{pergunta}/moderar', [ModeracaoController::class, 'store'])
        ->name('moderacoes.store');
    Route::get('/eventos/{evento}/moderacoes', [ModeracaoController::class, 'index'])
        ->name('moderacoes.index');
    Route::patch('/moderacoes/{moderacao}/status', [ModeracaoController::class, 'updateStatus'])
        ->name('moderacoes.status');

});

Route::get('/', [EventoController::class, 'index'])->name('eventos.index');
Route::get('/eventos/{id}', [EventoController::class, 'show'])->name('eventos.show');
Route::post('/eventos/{id}/perguntas', [PerguntaController::class, 'store'])
    ->middleware('auth')
    ->name('eventos.perguntas.store');
Route::post('/perguntas/{pergunta}/votar', [PerguntaController::class, 'votar'])
    ->middleware('auth')
    ->name('perguntas.votar');


Route::get('/register', [RegisterController::class, 'create'])->name('register.create');
Route::post('/register', [RegisterController::class, 'store'])->name('register.store');
Route::get('/login', [LoginController::class, 'create'])->name('login.create');
Route::post('/login', [LoginController::class, 'store'])->name('login.store');
Route::post('/logout', [LoginController::class, 'logout'])->name('auth.logout');


