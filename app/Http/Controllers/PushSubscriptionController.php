<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use NotificationChannels\WebPush\PushSubscription;

class PushSubscriptionController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Store a new push subscription.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $this->validate($request, ['subscription' => 'required']);

        $subscription = json_decode($request->subscription, true);

        $endpoint = $subscription['endpoint'];
        $keys = $subscription['keys'];

        $user = Auth::user();

        // Salvar ou atualizar a subscrição
        $user->updatePushSubscription(
            $endpoint,
            $keys['p256dh'],
            $keys['auth']
        );

        return response()->json(['success' => true]);
    }

    /**
     * Remove the specified push subscription.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request)
    {
        $this->validate($request, ['endpoint' => 'required']);

        $user = Auth::user();
        
        // Remover uma subscrição específica se endpoint for fornecido
        if ($request->has('endpoint')) {
            $user->deletePushSubscription($request->endpoint);
            return response()->json(['success' => true]);
        }

        // Caso contrário, remover todas as subscrições do usuário
        PushSubscription::where('subscribable_id', $user->id)
            ->where('subscribable_type', get_class($user))
            ->delete();

        return response()->json(['success' => true]);
    }

    /**
     * Obter as chaves VAPID públicas.
     *
     * @return \Illuminate\Http\Response
     */
    public function getVapidPublicKey()
    {
        return response()->json([
            'vapidPublicKey' => config('webpush.vapid.public_key')
        ]);
    }
} 