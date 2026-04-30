<?php

namespace App\Ai\Tools\Skill;

use App\Ai\Tools\BaseTool;
use App\Support\Settings\SkillsDiscoveryHelper;

class ListSkillsTool extends BaseTool
{
    protected string $name = "skill_list";

    protected string|null $description = "List all skills.";

    public function __invoke(): array
    {
        return SkillsDiscoveryHelper::toolsDescription();
    }
}
