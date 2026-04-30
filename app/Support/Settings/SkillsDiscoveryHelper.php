<?php

namespace App\Support\Settings;

use Illuminate\Support\Facades\File;

class SkillsDiscoveryHelper
{
    public static function getGlobalSkillsPath(): string
    {
        $homeDir = getenv('HOME') ?: getenv('USERPROFILE');

        return $homeDir . DIRECTORY_SEPARATOR . '.pachy' . DIRECTORY_SEPARATOR . 'skills';
    }

    public static function getLocalSkillsPath(): string
    {
        return getcwd() . DIRECTORY_SEPARATOR . '.agents' . DIRECTORY_SEPARATOR . 'skills';
    }

    public static function getSystemSkillsPath(): string
    {
        return base_path('resources' . DIRECTORY_SEPARATOR . 'skills');
    }

    public static function discover(): array
    {
        $skills = [];

        if (File::isDirectory(self::getSystemSkillsPath())) {
            $skills = array_merge($skills, self::scanDirectory(self::getSystemSkillsPath(), 'system'));
        }


        if (File::isDirectory(self::getGlobalSkillsPath())) {
            $skills = array_merge($skills, self::scanDirectory(self::getGlobalSkillsPath(), 'global'));
        }


        if (File::isDirectory(self::getLocalSkillsPath())) {
            $skills = array_merge($skills, self::scanDirectory(self::getLocalSkillsPath(), 'local'));
        }

        return $skills;
    }

    public static function toolsDescription(): array
    {
        $skills = self::discover();
        $friendlyArray = [];
        foreach ($skills as $skill) {
            $friendlyArray[$skill['type'] . ':' . $skill['name']] = $skill['description'];
        }

        return $friendlyArray;
    }

    public static function load(string $name): string
    {
        $skills = self::discover();
        $content = '';

        foreach ($skills as $skill) {
            if (($skill['type'] . ':' . $skill['name']) !== $name) {
                continue;
            }

            $skillContent = File::get($skill['path']);

            $skillContent = preg_replace('/^---[\s\S]*?---/', '', $skillContent);
            $content .= "\n\n" . trim($skillContent);
        }

        return trim($content);
    }

    private static function scanDirectory(string $path, string $type): array
    {
        $skills = [];
        $folders = File::directories($path);

        foreach ($folders as $folder) {
            $skillFile = $folder . DIRECTORY_SEPARATOR . 'SKILL.md';
            if (File::exists($skillFile)) {
                $skills[] = self::parseSkill($skillFile, $type);
            }
        }

        return $skills;
    }

    private static function parseSkill(string $path, string $type): array
    {
        $content = File::get($path);
        $name = basename(dirname($path));

        $description = '';
        if (preg_match('/description:\s*"(.*)"/', $content, $matches)) {
            $description = $matches[1];
        } elseif (preg_match('/description:\s*(.*)/', $content, $matches)) {
            $description = trim($matches[1]);
        }

        return [
            'name' => $name,
            'description' => $description,
            'path' => $path,
            'type' => $type,
        ];
    }
}
