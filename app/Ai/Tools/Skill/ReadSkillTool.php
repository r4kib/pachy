<?php

namespace App\Ai\Tools\Skill;

use App\Ai\Tools\BaseTool;
use App\Support\Settings\SkillsDiscoveryHelper;
use NeuronAI\Tools\PropertyType;
use NeuronAI\Tools\ToolProperty;

class ReadSkillTool extends BaseTool
{
    public function __construct()
    {
        parent::__construct(
            name: 'skill_read',
            description: "Read a specific skill content by its name.",
        );
    }
    /**
     * @return ToolProperty[]
     */
    protected function properties(): array
    {
        return [
            new ToolProperty(
                name: 'name',
                type: PropertyType::STRING,
                description: 'The name of the skill to read.',
                required: true,
            ),
        ];
    }

    public function __invoke(string $name): string
    {
        $content = SkillsDiscoveryHelper::load($name);

        if (empty($content)) {
            return "Skill '{$name}' not found.";
        }

        return $content;
    }
}
