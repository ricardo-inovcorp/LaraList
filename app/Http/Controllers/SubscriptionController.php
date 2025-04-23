<?php

namespace App\Http\Controllers;

use App\Models\Subscription;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;
use Carbon\Carbon;

class SubscriptionController extends Controller
{
    /**
     * Display subscription page.
     */
    public function index()
    {
        $user = Auth::user();
        $subscription = $user->subscription;

        // Calcular dias restantes do período de avaliação
        $trialDaysLeft = 0;
        if ($subscription && $subscription->trial_ends_at) {
            $now = Carbon::now();
            $trialEnd = Carbon::parse($subscription->trial_ends_at);
            
            $trialDaysLeft = $now->diffInDays($trialEnd, false);
        }

        // Log para debugging
        Log::info('Subscription Controller - User Info', [
            'user_id' => $user->id,
            'has_subscription' => $subscription ? 'yes' : 'no',
            'trial_days_left' => $trialDaysLeft,
            'subscription_data' => $subscription
        ]);
        
        return Inertia::render('Subscription/Index', [
            'subscription' => $subscription,
            'onTrial' => $user->onTrial(),
            'hasActiveSubscription' => $user->hasActiveSubscription(),
            'trialDaysLeft' => $trialDaysLeft,
        ]);
    }
    
    /**
     * Start free trial.
     */
    public function startFreeTrial(Request $request)
    {
        $user = Auth::user();
        
        // If user already has a subscription, redirect
        if ($user->hasActiveSubscription()) {
            return redirect()->route('subscription.index')
                ->with('message', 'Você já possui uma assinatura ativa.');
        }
        
        // Start a free trial
        $user->startFreeTrial();
        
        return redirect()->route('subscription.index')
            ->with('message', 'Seu período de avaliação gratuita de 14 dias foi iniciado!');
    }
    
    /**
     * Process subscription payment via PayPal.
     */
    public function processPayment(Request $request)
    {
        $user = Auth::user();
        
        // Validate payment details
        $validated = $request->validate([
            'payment_id' => 'required|string',
            'plan_type' => 'required|string|in:premium',
        ]);
        
        // Update or create subscription
        $user->subscription()->updateOrCreate(
            ['user_id' => $user->id],
            [
                'plan_type' => $validated['plan_type'],
                'payment_method' => 'paypal',
                'payment_id' => $validated['payment_id'],
                'subscription_ends_at' => Carbon::now()->addMonth(),
                'is_active' => true,
            ]
        );
        
        return redirect()->route('subscription.index')
            ->with('message', 'Assinatura realizada com sucesso!');
    }
    
    /**
     * Cancel subscription.
     */
    public function cancel()
    {
        $user = Auth::user();
        $subscription = $user->subscription;
        
        if ($subscription) {
            $subscription->update([
                'is_active' => false,
            ]);
        }
        
        return redirect()->route('subscription.index')
            ->with('message', 'Sua assinatura foi cancelada.');
    }
} 