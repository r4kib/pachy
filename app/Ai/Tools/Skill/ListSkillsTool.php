<?php

namespace App\Ai\Tools\Skill;

use App\Ai\Tools\BaseTool;
use App\Support\Settings\SkillsDiscoveryHelper;

class ListSkillsTool extends BaseTool
{
    public function __construct()
    {
        parent::__construct(
            name: "skill_list",
            description: "List all skills.",
        );
    }

    public function __invoke(): array
    {
        return SkillsDiscoveryHelper::toolsDescription();
    }
}
