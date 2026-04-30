<?php

declare(strict_types=1);

namespace App\Ai\Tools;

use NeuronAI\Exceptions\ArrayPropertyException;
use NeuronAI\Exceptions\ToolException;
use NeuronAI\Tools\ArrayProperty;
use NeuronAI\Tools\ObjectProperty;
use NeuronAI\Tools\PropertyType;
use NeuronAI\Tools\ToolProperty;
use Throwable;
use function PHPUnit\Framework\isCallable;

class MultiCallTool extends BaseTool
{
    protected array $tools = [];

    public function __construct(array $tools = [])
    {
        parent::__construct(
            name: 'multi_call',
            description: 'Execute multiple tool calls in a single operation. Useful for planning multiple tool call beforehand and run them in a single operation.',
        );

        foreach ($tools as $tool) {
            $this->tools[$tool->getName()] = $tool;
        }
    }

    /**
     * @throws \ReflectionException
     * @throws ArrayPropertyException
     * @throws ToolException
     */
    protected function properties(): array
    {
        return [
            new ArrayProperty(
                name: 'calls',
                description: 'A list of tools to calls ',
                required: true,
                items: new ObjectProperty(
                    name: 'call',
                    description: 'call with tool name and arguments',
                    required: true,
                    properties: [
                        new ToolProperty(
                            name: 'tool',
                            type: PropertyType::STRING,
                            description: 'Name of tool',
                            required: true
                        ),
                        new ObjectProperty(
                            name: 'arguments',
                            description: 'Key value arguments. Example ("argument1":"argument value 2")',
                            required: true,
                        )

                    ]
                ),
                minItems: 2
            )

        ];
    }


    public function __invoke(array $calls): array
    {

        $results = [];

        foreach ($calls as $call) {
            $toolName = $call['tool'] ?? '';
            $parameters = $call['parameters'] ?? [];

            if (! isset($this->tools[$toolName])) {
                $results[] = [
                    'tool' => $toolName,
                    'status' => 'error',
                    'message' => "Tool '{$toolName}' not found or not available for multi_call.",
                ];
                continue;
            }

            try {
                $tool = $this->tools[$toolName];

                // Invoke the tool with its parameters
                if (isCallable($tool)) {
                    $result = $tool(...$parameters);
                }else{
                    $result = "Tool is not callable";
                }

                $results[] = [
                    'tool' => $toolName,
                    'status' => 'success',
                    'result' => $result,
                ];
            } catch (Throwable $e) {
                $results[] = [
                    'tool' => $toolName,
                    'status' => 'error',
                    'message' => $e->getMessage(),
                ];
            }
        }

        return $results;
    }

    /**
     * Check if any of the calls require human approval.
     */
    public function requiresHumanApproval(array $arguments): bool
    {
        $calls = $arguments['calls'] ?? [];
        foreach ($calls as $call) {
            $toolName = $call['tool'] ?? '';
            if (isset($this->tools[$toolName])) {
                $tool = $this->tools[$toolName];
                if (method_exists($tool, 'requiresHumanApproval')) {
                    if ($tool->requiresHumanApproval($call['parameters'] ?? [])) {
                        return true;
                    }
                }
            }
        }

        return false;
    }
}
