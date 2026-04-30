<?php

namespace App\Ai\Tools\Skill;

use App\Ai\Tools\BaseTool;
use App\Support\Settings\SkillsDiscoveryHelper;
use Illuminate\Support\Facades\File;
use NeuronAI\Tools\PropertyType;
use NeuronAI\Tools\ToolProperty;

class ReadSkillRuleTool extends BaseTool
{
    public function __construct()
    {
        parent::__construct(
            name: 'skill_rule_read',
            description: "Read a specific rule content from a skill's rules directory.",
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
            new ToolProperty(
                name: 'rule_name',
                type: PropertyType::STRING,
                description: 'The name of the rule file (e.g., db-performance.md).',
                required: true,
            ),
        ];
    }

    public function __invoke(string $skill_name, string $rule_name): string
    {
        $skills = SkillsDiscoveryHelper::discover();
        foreach ($skills as $skill) {
            if (($skill['type'] . ':' . $skill['name']) !== $skill_name) {
                continue;
            }

            $skillDir = dirname($skill['path']);
            $rulePath = $skillDir . DIRECTORY_SEPARATOR . 'rules' . DIRECTORY_SEPARATOR . $rule_name;

            if (!File::exists($rulePath)) {
                // Try adding .md if not present
                if (!str_ends_with($rule_name, '.md')) {
                    $rulePath .= '.md';
                }
            }

            if (!File::exists($rulePath)) {
                return "Rule '{$rule_name}' not found in skill '{$skill_name}'.";
            }

            $content = File::get($rulePath);
            return $content . "\n\n[Rule read successfully from {$rulePath}]";
        }

        return "Skill '{$skill_name}' not found.";
    }
}
