<?php

namespace App\Support\Settings;

use Illuminate\Support\Facades\File;
use NeuronAI\Providers\Anthropic\Anthropic;
use NeuronAI\Providers\Gemini\Gemini;
use NeuronAI\Providers\OpenAI\OpenAI;

class SettingsHelper
{
    public static function globalSettingsPath(): string
    {
        $filename = 'settings.json';
        $homeDir = getenv('HOME') ?: getenv('USERPROFILE');
        $dir = $homeDir.DIRECTORY_SEPARATOR.'.pachy';

        if (! File::isDirectory($dir)) {
            File::makeDirectory($dir, 0755, true);
        }
        $filePath = $dir.DIRECTORY_SEPARATOR.$filename;
        if (! File::exists($filePath)) {
            File::put($filePath, json_encode(['updated' => time()], JSON_PRETTY_PRINT));
        }

        return $filePath;
    }

    public static function getSettings(): array
    {
        return json_decode(File::get(self::globalSettingsPath()), true) ?? [];
    }

    public static function storeSettings(array $settings): void
    {
        File::put(self::globalSettingsPath(), json_encode($settings, JSON_PRETTY_PRINT));
    }

    public static function updateSettings(array $settings): void
    {
        File::put(self::globalSettingsPath(), json_encode(array_replace_recursive(self::getSettings(), $settings), JSON_PRETTY_PRINT));
    }

    public static function getProviderSetting(): array
    {
        $settings = self::getSettings();
        $providers = $settings['providers'] ?? [];

        if (empty($providers)) {
            return [];
        }

        $activeName = $settings['active_provider'] ?? null;

        if ($activeName) {
            foreach ($providers as $provider) {
                if (($provider['name'] ?? '') === $activeName) {
                    return $provider;
                }
            }
        }

        return $providers[0] ?? [];
    }

    public static function getProvider()
    {

        $provider = self::getProviderSetting();

        return match ($provider['provider']) {
            'gemini' => new Gemini(
                key: $provider['key'],
                model: $provider['model'],
            ),
            'anthropic' => new Anthropic(
                key: $provider['key'],
                model: $provider['model'],
            ),
            'openai' => new OpenAI(
                key: $provider['key'],
                model: $provider['model'],
            ),
            default => throw new \Exception('No valid provider found.'),
        };
    }
}
