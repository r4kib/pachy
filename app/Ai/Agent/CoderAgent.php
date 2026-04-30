<?php

namespace App\Ai\Agent;

use App\Ai\Agent\Middleware\HumanApproval;
use App\Ai\Prompts\CoderSystemPrompt;
use App\Ai\Tools\FileSystem\FileSystemToolkit;
use App\Ai\Tools\Interaction\InteractionToolkit;
use App\Ai\Tools\MultiCallTool;
use App\Ai\Tools\Skill\SkillToolkit;
use App\Support\Settings\McpSettingHelper;
use App\Support\Settings\SettingsHelper;
use NeuronAI\Agent\Agent;
use NeuronAI\Agent\Nodes\ToolNode;
use NeuronAI\Providers\AIProviderInterface;

class CoderAgent extends Agent
{
    public function __construct()
    {
        parent::__construct();
    }

    protected function provider(): AIProviderInterface
    {
        return SettingsHelper::getProvider();
    }

    protected function instructions(): string
    {
        return (string) new CoderSystemPrompt;
    }

    protected function tools(): array
    {
        $allTools = [
            ...FileSystemToolkit::make()->provide(),
            ...InteractionToolkit::make()->provide(),
            ...SkillToolkit::make()->provide(),
            ...McpSettingHelper::getMcp(),
        ];

        return [
            ...$allTools,
            new MultiCallTool($allTools),
        ];
    }

    protected function middleware(): array
    {
        return [
            ToolNode::class => [
                new HumanApproval,
            ],
        ];
    }
}
