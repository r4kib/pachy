<?php

namespace App\Ai\Tool;

use NeuronAI\Tools\Tool;
use NeuronAI\Tools\ToolProperty;
use NeuronAI\Tools\PropertyType;
use function shell_exec;
use function fopen;
use function fgets;
use function fclose;
use function trim;
use function strtolower;

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
        echo "\n⚠️  AI REQUEST: Execute command: `{$command}`\n";
        echo "Do you allow this? (y/n): ";

        $handle = fopen("php://stdin", "r");
        $response = trim(fgets($handle));
        fclose($handle);

        if (strtolower($response) !== 'y') {
            return "Command execution denied by user.";
        }

        $output = shell_exec($command . " 2>&1");
        return "Output:\n" . $output;
    }
}
