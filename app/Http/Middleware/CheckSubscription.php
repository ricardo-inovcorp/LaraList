<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;
use App\Models\Subscription;

class CheckSubscription
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string $accessLevel = 'any'): Response
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $user = Auth::user();
        $subscription = $user->subscription;

        // If no subscription exists, redirect to subscription page
        if (!$subscription) {
            return redirect()->route('subscription.index')
                ->with('message', 'Você precisa de uma assinatura para acessar esta funcionalidade.');
        }

        if ($accessLevel === 'premium' && $subscription->plan_type !== 'premium') {
            return redirect()->route('subscription.index')
                ->with('message', 'Esta funcionalidade requer uma assinatura premium.');
        }

        // Check if subscription is active or on trial
        if (!$user->hasActiveSubscription() && !$user->onTrial()) {
            return redirect()->route('subscription.index')
                ->with('message', 'Sua assinatura expirou ou foi cancelada.');
        }

        return $next($request);
    }
} 