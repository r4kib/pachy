<?php

namespace App\Ai\Agent;

use App\Ai\Agent\Middleware\HumanApproval;
use App\Ai\Prompts\CoderSystemPrompt;
use App\Ai\Tools\FileSystem\FileSystemToolkit;
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
        return [
            FileSystemToolkit::make(),
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
