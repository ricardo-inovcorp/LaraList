// Rotas para gerenciar subscrições de notificações push
Route::post('/push-subscriptions', [App\Http\Controllers\PushSubscriptionController::class, 'store'])
    ->middleware('auth:sanctum');
Route::delete('/push-subscriptions', [App\Http\Controllers\PushSubscriptionController::class, 'destroy'])
    ->middleware('auth:sanctum');
Route::get('/vapid-public-key', [App\Http\Controllers\PushSubscriptionController::class, 'getVapidPublicKey']); 