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

        $settings = SettingsHelper::getSettings();
        $providers = $settings['providers'] ?? [];

        $action = 'Add New Provider';
        if (! empty($providers)) {
            $action = $this->choice('What would you like to do?', [
                'Add New Provider',
                'Select Active Provider',
                'Delete Provider',
            ], 0);
        }

        if ($action === 'Add New Provider') {
            $this->addProvider($providers);
        } elseif ($action === 'Select Active Provider') {
            $this->selectActiveProvider($providers);
        } elseif ($action === 'Delete Provider') {
            $this->deleteProvider($providers);
        }
    }

    private function addProvider(array $providers): void
    {
        $providerType = $this->choice('Select LLM Provider', ['openai', 'anthropic', 'gemini'], 0);
        $key = $this->secret('Enter your API Key');
        $model = $this->ask('Enter the default model name');
        $name = $this->ask('Enter a unique name for this provider configuration');

        $providers[] = [
            'provider' => $providerType,
            'key' => $key,
            'model' => $model,
            'name' => $name,
        ];

        SettingsHelper::updateSettings([
            'providers' => $providers,
            'active_provider' => $name,
        ]);

        $this->info("✅ Provider '{$name}' added and set as active.");
    }

    private function selectActiveProvider(array $providers): void
    {
        $options = array_map(fn ($p) => $p['name'] ?? 'Unnamed', $providers);
        $selected = $this->choice('Select the active provider', $options);

        SettingsHelper::updateSettings(['active_provider' => $selected]);

        $this->info("✅ Active provider set to '{$selected}'.");
    }

    private function deleteProvider(array &$providers): void
    {
        $options = array_map(fn ($p) => $p['name'] ?? 'Unnamed', $providers);
        $selected = $this->choice('Select a provider to delete', $options);

        $providers = array_values(array_filter($providers, fn ($p) => ($p['name'] ?? '') !== $selected));

        $update = ['providers' => $providers];
        if (SettingsHelper::getSettings()['active_provider'] === $selected) {
            $update['active_provider'] = $providers[0]['name'] ?? null;
        }

        SettingsHelper::updateSettings($update);

        $this->info("✅ Provider '{$selected}' deleted.");
    }
}
