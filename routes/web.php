<?php

use App\Http\Controllers\TarefaController;
use App\Http\Controllers\CategoriaController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Welcome');
})->name('home');

Route::get('dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/', function () {
        return redirect()->route('tarefas.index');
    })->name('home');
    Route::resource('tarefas', TarefaController::class);
    Route::patch('tarefas/{tarefa}/toggle-concluida', [TarefaController::class, 'toggleConcluida'])->name('tarefas.toggle-concluida');
    
    // Rotas para categorias
    Route::get('/categorias', [CategoriaController::class, 'index'])->name('categorias.index');
    Route::post('/categorias', [CategoriaController::class, 'store'])->name('categorias.store');
});

require __DIR__.'/settings.php';
require __DIR__.'/auth.php';
