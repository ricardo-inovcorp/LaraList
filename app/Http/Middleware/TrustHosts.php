<?php

namespace App\Http\Middleware;

use Illuminate\Http\Middleware\TrustHosts as Middleware;

class TrustHosts extends Middleware
{
    /**
     * Get the host patterns that should be trusted.
     *
     * @return array<int, string|null>
     */
    public function hosts(): array
    {
        return [
            $this->allSubdomainsOfApplicationUrl(),
            '*.ngrok-free.app', // Autoriza domínios ngrok
            'localhost',
            '127.0.0.1',
            '10.0.2.2', // Emulador Android
        ];
    }
} 