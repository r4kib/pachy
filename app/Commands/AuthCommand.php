<?php

namespace App\Commands;

use App\Support\Settings\SettingsHelper;
use LaravelZero\Framework\Commands\Command;

class AuthCommand extends Command
{
    protected $signature = 'auth';

    protected $description = 'Configure authentication settings for Pachy';

    public function handle()
    {
        $this->info('🔑 Configure Authentication');

        $provider = $this->choice('Select LLM Provider', ['openai', 'anthropic', 'gemini'], 0);
        $key = $this->secret('Enter your API Key');
        $model = $this->ask('Enter the default model name', '');
        $name = $this->ask('Enter the name of the provider', '');

        $providers = SettingsHelper::getSettings()['providers'] ?? [];
        $providers[] = [
            'provider' => $provider,
            'key' => $key,
            'model' => $model,
            'name' => $name,
        ];
        SettingsHelper::updateSettings(['providers' => $providers]);

        $this->info('✅ Settings saved to ~/.pachy/settings.json');
    }
}
