<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Minishlink\WebPush\VAPID;

class GenerateVapidKeys extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'webpush:vapid';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Gerar chaves VAPID para push notifications';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Gerando chaves VAPID...');

        try {
            $vapid = VAPID::createVapidKeys();

            $this->info('Chaves VAPID geradas com sucesso!');
            $this->newLine();
            $this->info('Adicione estas chaves ao seu .env:');
            $this->newLine();
            $this->info('VAPID_PUBLIC_KEY=' . $vapid['publicKey']);
            $this->info('VAPID_PRIVATE_KEY=' . $vapid['privateKey']);
            $this->info('VITE_VAPID_PUBLIC_KEY=' . $vapid['publicKey']);
            $this->newLine();
            
            // Perguntar se deseja salvar automaticamente no .env
            if ($this->confirm('Deseja salvar estas chaves automaticamente no arquivo .env?', true)) {
                $this->updateEnvFile($vapid);
                $this->info('Chaves salvas com sucesso no .env.');
            }

            return Command::SUCCESS;
        } catch (\Exception $e) {
            $this->error('Erro ao gerar chaves VAPID: ' . $e->getMessage());
            return Command::FAILURE;
        }
    }

    /**
     * Atualizar o arquivo .env com as chaves VAPID
     */
    protected function updateEnvFile(array $vapid): void
    {
        $envFile = app()->environmentFilePath();
        $envContent = file_get_contents($envFile);

        // Verificar se as chaves VAPID já existem no .env
        if (strpos($envContent, 'VAPID_PUBLIC_KEY=') !== false) {
            // Substituir os valores existentes
            $envContent = preg_replace('/VAPID_PUBLIC_KEY=.*/', 'VAPID_PUBLIC_KEY=' . $vapid['publicKey'], $envContent);
            $envContent = preg_replace('/VAPID_PRIVATE_KEY=.*/', 'VAPID_PRIVATE_KEY=' . $vapid['privateKey'], $envContent);
            $envContent = preg_replace('/VITE_VAPID_PUBLIC_KEY=.*/', 'VITE_VAPID_PUBLIC_KEY=' . $vapid['publicKey'], $envContent);
        } else {
            // Adicionar novas chaves ao final do arquivo
            $envContent .= "\n# PWA Push Notifications\n";
            $envContent .= "VAPID_PUBLIC_KEY=" . $vapid['publicKey'] . "\n";
            $envContent .= "VAPID_PRIVATE_KEY=" . $vapid['privateKey'] . "\n";
            $envContent .= "VITE_VAPID_PUBLIC_KEY=" . $vapid['publicKey'] . "\n";
        }

        file_put_contents($envFile, $envContent);
    }
} 