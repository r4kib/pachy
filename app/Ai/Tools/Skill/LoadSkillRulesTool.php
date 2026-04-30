<?php

namespace App\Ai\Tools\Skill;

use App\Ai\Tools\BaseTool;
use App\Support\Settings\SkillsDiscoveryHelper;
use Illuminate\Support\Facades\File;
use NeuronAI\Tools\PropertyType;
use NeuronAI\Tools\ToolProperty;

class LoadSkillRulesTool extends BaseTool
{
    public function __construct()
    {
        parent::__construct(
            name: 'skill_rules_load',
            description: "Load all rules from a skill's rules directory. (Dont use this if you don't need all rules)",
        );
    }

    /**
     * @return ToolProperty[]
     */
    protected function properties(): array
    {
        return [
            new ToolProperty(
                name: 'skill_name',
                type: PropertyType::STRING,
                description: 'The name of the skill (e.g., local:laravel-best-practices).',
                required: true,
            ),
        ];
    }

    public function __invoke(string $skill_name): string
    {
        $skills = SkillsDiscoveryHelper::discover();
        $content = "";
        $found = false;

        foreach ($skills as $skill) {
            if (($skill['type'] . ':' . $skill['name']) !== $skill_name) {
                continue;
            }

            $found = true;
            $rulesDir = dirname($skill['path']) . DIRECTORY_SEPARATOR . 'rules';

            if (!File::isDirectory($rulesDir)) {
                return "No rules directory found for skill '{$skill_name}'.";
            }

            $files = File::files($rulesDir);
            foreach ($files as $file) {
                if ($file->getExtension() === 'md') {
                    $content .= "\n\n--- Rule: " . $file->getFilename() . " ---\n";
                    $content .= File::get($file->getPathname());
                }
            }
        }

        if (!$found) {
            return "Skill '{$skill_name}' not found.";
        }

        if (empty($content)) {
            return "No markdown rules found in the rules directory of skill '{$skill_name}'.";
        }

        return trim($content);
    }
}
