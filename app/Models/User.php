<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Carbon\Carbon;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * Obter as tarefas do utilizador.
     */
    public function tarefas(): HasMany
    {
        return $this->hasMany(Tarefa::class, 'utilizador_id');
    }
    
    /**
     * Get the user's subscription.
     */
    public function subscription(): HasOne
    {
        return $this->hasOne(Subscription::class)->latest();
    }
    
    /**
     * Check if user has an active subscription.
     */
    public function hasActiveSubscription(): bool
    {
        return $this->subscription && !$this->subscription->hasExpired();
    }
    
    /**
     * Check if user is on trial period.
     */
    public function onTrial(): bool
    {
        return $this->subscription && $this->subscription->onTrial();
    }
    
    /**
     * Start free trial for the user.
     */
    public function startFreeTrial(int $days = 14): void
    {
        $this->subscription()->updateOrCreate(
            ['user_id' => $this->id],
            [
                'plan_type' => 'free',
                'trial_ends_at' => Carbon::now()->addDays($days),
                'is_active' => true,
            ]
        );
    }

    /**
     * Obtém as categorias do usuário.
     */
    public function categorias(): HasMany
    {
        return $this->hasMany(Categoria::class, 'utilizador_id');
    }
}
