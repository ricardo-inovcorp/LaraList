<?php

use App\Http\Controllers\TarefaController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Welcome');
})->name('home');

Route::get('dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

// Rotas para gestão de tarefas
Route::middleware(['auth', 'verified'])->group(function () {
    Route::resource('tarefas', TarefaController::class);
    Route::patch('tarefas/{tarefa}/toggle-concluida', [TarefaController::class, 'toggleConcluida'])->name('tarefas.toggle-concluida');
});

require __DIR__.'/settings.php';
require __DIR__.'/auth.php';
