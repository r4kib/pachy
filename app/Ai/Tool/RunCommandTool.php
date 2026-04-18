<?php

namespace App\Ai\Tool;

use NeuronAI\Tools\Tool;
use NeuronAI\Tools\ToolProperty;
use NeuronAI\Tools\PropertyType;
use function Laravel\Prompts\confirm;
use function shell_exec;

class RunCommandTool extends Tool
{
    public function __construct()
    {
        parent::__construct(
            'run_command',
            'Run a system command. Requires user permission.'
        );
    }

    protected function properties(): array
    {
        return [
            new ToolProperty(
                name: 'command',
                type: PropertyType::STRING,
                description: 'The command to run',
                required: true
            )
        ];
    }

    public function __invoke(string $command): string
    {
        if (!confirm("Execute command: `{$command}`?")) {
            return "Command execution denied by user.";
        }

        $output = shell_exec($command . " 2>&1");
        return "Output:\n" . $output;
    }
}
