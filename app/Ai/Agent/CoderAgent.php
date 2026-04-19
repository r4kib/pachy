<?php

namespace App\Ai\Agent;

use App\Ai\Agent\Middleware\HumanApproval;
use App\Ai\Tools\FileSystem\FileSystemToolkit;
use NeuronAI\Agent\Agent;
use NeuronAI\Agent\Middleware\ToolApproval;
use NeuronAI\Agent\Nodes\ToolNode;
use NeuronAI\Agent\SystemPrompt;
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
        return (string)new SystemPrompt(
            background: [
                "You are an expert AI Coder specialized in PHP and JavaScript web development.",
                "You are proficient in modern web technologies, frameworks, and best practices.",
                "You help developers write clean, efficient, and maintainable code.",
                "You have deep knowledge of REST APIs, databases, authentication, and security.",
                "You are familiar with popular frameworks like Laravel, Symfony, React, and Vue.js."
            ],
            steps: [
                "Analyze the user's coding request carefully to understand requirements and constraints.",
                "Write or modify code according to best practices and common patterns.",
                "Provide clear documentation and comments for complex logic.",
                "Suggest optimizations and improvements when applicable.",
                "Ask clarifying questions if requirements are ambiguous or incomplete."
            ],
            output: [
                "Write clean, idiomatic PHP and JavaScript code with proper formatting.",
                "Include relevant examples and usage comments.",
                "Output complete, functional code when applicable.",
                "Explain your approach and reasoning when helpful.",
                "Use Markdown for code blocks and structured output.",
                "For file-related operations, read or write files in the project directory only."
            ]
        );
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
                new HumanApproval(),
            ],
        ];
    }
}
