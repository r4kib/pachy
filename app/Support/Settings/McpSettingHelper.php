<?php

namespace App\Support\Settings;

use Illuminate\Support\Facades\File;
use NeuronAI\MCP\McpConnector;

class McpSettingHelper
{
    private static function localMcpSettings()
    {
        $path = getcwd().DIRECTORY_SEPARATOR.'.pachy'.DIRECTORY_SEPARATOR.'mcp.json';
        if (File::exists($path)) {
            return json_decode(File::get($path), true);
        }
        return [];
    }

    public static function getMcp():array
    {
        $config = self::localMcpSettings();
        foreach ($config['mcp'] as $key => $mcp) {
            if (($mcp['enabled'] ?? true) === false) {
                continue;
            }

            $mcp = self::resolveEnv($mcp);

            $connector = McpConnector::make($mcp);

            if (!empty($mcp['only'])) {
                $connector->only($mcp['only']);
            }

            if (!empty($mcp['exclude'])) {
                $connector->exclude($mcp['exclude']);
            }

            $tools[] = $connector->tools();
        }

        return $tools;
    }

    private static function resolveEnv(array $config): array
        {
            foreach ($config as $key => $value) {

                if (is_array($value)) {
                    $config[$key] = self::resolveEnv($value);
                    continue;
                }

                if (is_string($value) && str_starts_with($value, 'env:')) {
                    $envKey = substr($value, 4);
                    $config[$key] = env($envKey);
                }

                if (is_string($value) && str_starts_with($value, 'env_bool:')) {
                    $envKey = substr($value, 9);
                    $config[$key] = filter_var(env($envKey), FILTER_VALIDATE_BOOLEAN);
                }
            }

            return $config;

    }
}
