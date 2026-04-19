<?php

namespace App\Ai\Agent;

use App\Ai\Agent\Middleware\HumanApproval;
use App\Ai\Prompts\CoderSystemPrompt;
use App\Ai\Tools\FileSystem\FileSystemToolkit;
use NeuronAI\Agent\Agent;
use NeuronAI\Agent\Nodes\ToolNode;
use NeuronAI\Providers\AIProviderInterface;
use NeuronAI\Providers\Gemini\Gemini;

class CoderAgent extends Agent
{
    public function __construct()
    {
        parent::__construct();
    }

    protected function provider(): AIProviderInterface
    {
        return new Gemini(
            key: config('services.gemini.key'),
            model: config('services.gemini.model'),
        );
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
