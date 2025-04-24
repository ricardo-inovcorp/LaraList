<?php

use App\Http\Controllers\TarefaController;
use App\Http\Controllers\CategoriaController;
use App\Http\Controllers\SubscriptionController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Http\Controllers\DashboardController;
use Illuminate\Support\Facades\Auth;
use App\Http\Middleware\CheckSubscription;

Route::get('/', function () {
    return Inertia::render('Welcome');
})->name('home');

Route::middleware('auth')->group(function () {
    Route::get('/', function () {
        return redirect()->route('tarefas.index');
    })->name('home');
    
    // Dashboard route (acessível para todos usuários autenticados)
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    
    // Rota para API de estatísticas do dashboard
    Route::get('/api/dashboard/stats', [DashboardController::class, 'stats'])->name('api.dashboard.stats');
    
    // Rotas que exigem assinatura ativa ou período de avaliação
    Route::middleware(CheckSubscription::class)->group(function () {
        Route::get('/tarefas', [TarefaController::class, 'index'])->name('tarefas.index');
        Route::get('/tarefas/create', [TarefaController::class, 'create'])->name('tarefas.create');
        Route::post('/tarefas', [TarefaController::class, 'store'])->name('tarefas.store');
        Route::get('/tarefas/{tarefa}', [TarefaController::class, 'show'])->name('tarefas.show');
        Route::get('/tarefas/{tarefa}/edit', [TarefaController::class, 'edit'])->name('tarefas.edit');
        Route::put('/tarefas/{tarefa}', [TarefaController::class, 'update'])->name('tarefas.update');
        Route::delete('/tarefas/{tarefa}', [TarefaController::class, 'destroy'])->name('tarefas.destroy');
        Route::patch('tarefas/{tarefa}/toggle-concluida', [TarefaController::class, 'toggleConcluida'])->name('tarefas.toggle-concluida');
    });
    
    // Rotas para categorias - registradas individualmente com middleware
    Route::get('/categorias', [CategoriaController::class, 'index'])
        ->name('categorias.index');
    
    Route::post('/categorias', [CategoriaController::class, 'store'])
        ->name('categorias.store');
    
    Route::post('/api/categorias', [CategoriaController::class, 'store'])
        ->name('api.categorias.store');
    
    // Subscription routes
    Route::get('/subscription', [SubscriptionController::class, 'index'])->name('subscription.index');
    Route::post('/subscription/start-trial', [SubscriptionController::class, 'startFreeTrial'])->name('subscription.start-trial');
    Route::post('/subscription/payment', [SubscriptionController::class, 'processPayment'])->name('subscription.payment');
    Route::post('/subscription/cancel', [SubscriptionController::class, 'cancel'])->name('subscription.cancel');
});

require __DIR__.'/settings.php';
require __DIR__.'/auth.php';
