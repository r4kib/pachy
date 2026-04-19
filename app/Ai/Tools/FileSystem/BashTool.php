<?php

declare(strict_types=1);

namespace App\Ai\Tools\FileSystem;

use Illuminate\Support\Facades\Process;
use NeuronAI\Tools\PropertyType;
use NeuronAI\Tools\Tool;
use NeuronAI\Tools\ToolProperty;

use function getcwd;

/**
 * Execute a bash command and return its output.
 */
class BashTool extends Tool
{
    public function __construct()
    {
        parent::__construct(
            name: 'bash',
            description: 'Execute a bash command and return its output. Use for running scripts, build tools, tests, linters, or any shell operation.',
        );
    }

    protected function properties(): array
    {
        return [
            ToolProperty::make(
                name: 'command',
                type: PropertyType::STRING,
                description: 'The bash command to execute.',
                required: true,
            ),
            ToolProperty::make(
                name: 'working_directory',
                type: PropertyType::STRING,
                description: 'The working directory to run the command in. Defaults to the current working directory.',
                required: false,
            ),
        ];
    }

    public function __invoke(string $command, ?string $working_directory = null): array
    {
        $cwd = $working_directory ?? getcwd();

        // Run the command using Laravel's Process facade
        $result = Process::path($cwd)->run($command);

        $output = $result->output();
        $errorOutput = $result->errorOutput();
        $combinedOutput = $output . ($errorOutput !== '' ? "\n" . $errorOutput : '');

        $status = $result->successful() ? 'success' : 'error';
        $message = $result->successful()
            ? 'Command executed successfully.'
            : "Command exited with code {$result->exitCode()}.";

        return [
            'status' => $status,
            'operation' => 'bash',
            'command' => $command,
            'output' => $combinedOutput,
            'exit_code' => $result->exitCode(),
            'working_directory' => $cwd,
            'message' => $message,
        ];
    }
}
