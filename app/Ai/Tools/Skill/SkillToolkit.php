<?php

namespace App\Ai\Tools\Skill;

use NeuronAI\Tools\Toolkits\AbstractToolkit;

/**
 * @method static static make()
 */
class SkillToolkit extends AbstractToolkit
{
    public function guidelines(): ?string
    {
        return 'Use these tools to discover and read skills that provide instructions on how to do specific tasks properly.';
    }

    public function provide(): array
    {
        return [
            ListSkillsTool::make(),
            ReadSkillTool::make(),
            ListSkillRulesTool::make(),
            ReadSkillRuleTool::make(),
            LoadSkillRulesTool::make(),
        ];

    }
}
