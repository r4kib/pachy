<?php

namespace App\Ai\Tools\Skill;

use App\Ai\Tools\BaseTool;
use App\Support\Settings\SkillsDiscoveryHelper;
use Illuminate\Support\Facades\File;
use NeuronAI\Tools\PropertyType;
use NeuronAI\Tools\ToolProperty;

class ListSkillRulesTool extends BaseTool
{
    public function __construct()
    {
        parent::__construct(
            name: 'skill_rules_list',
            description: "List all rules associated with a specific skill.",
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

    public function __invoke(string $skill_name): array|string
    {
        $skills = SkillsDiscoveryHelper::discover();
        foreach ($skills as $skill) {
            if (($skill['type'] . ':' . $skill['name']) !== $skill_name) {
                continue;
            }

            $rulesDir = dirname($skill['path']) . DIRECTORY_SEPARATOR . 'rules';
            if (!File::isDirectory($rulesDir)) {
                return "No rules directory found for skill '{$skill_name}'.";
            }

            $files = File::files($rulesDir);
            return array_map(fn($file) => $file->getFilename(), $files);
        }

        return "Skill '{$skill_name}' not found.";
    }
}
