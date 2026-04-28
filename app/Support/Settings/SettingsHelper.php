<?php

namespace App\Support\Settings;

use App\Data\SettingsData;
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
        $dir = $homeDir . DIRECTORY_SEPARATOR . '.pachy';

        if (!File::isDirectory($dir)) {
            File::makeDirectory($dir, 0755, true);
        }
        $filePath = $dir . DIRECTORY_SEPARATOR . $filename;
        if (!File::exists($filePath)) {
            File::put($filePath, json_encode(['updated'=> time()], JSON_PRETTY_PRINT));
        }
        return $filePath;
    }

    public static function getSettings(): array
    {
        return json_decode(File::get(self::globalSettingsPath()), true) ?? [];
    }


    public static function storeSettings(array $settings): void
    {
        File::put(self::globalSettingsPath(), json_encode( $settings, JSON_PRETTY_PRINT));
    }

    public static function updateSettings(array $settings): void
    {
        File::put(self::globalSettingsPath(), json_encode( array_replace_recursive(self::getSettings(),$settings), JSON_PRETTY_PRINT));
    }
    public static function getProvider()
    {
        $settings = self::getSettings();
        if (isset($settings['providers'][0])) {
            $provider = $settings['providers'][0];
            switch ($provider['provider']) {
                case('gemini'):
                    return new Gemini(
                        key: $provider['key'],
                        model: $provider['model'],
                    );
                case('anthropic'):
                    return new Anthropic(
                        key: $provider['key'],
                        model: $provider['model'],
                    );
                case('openai'):
                    return new OpenAI(
                        key: $provider['key'],
                        model: $provider['model'],
                    );
                default:
                    throw new \Exception("No valid provider found.");

            }

        }

    }
}
