<?php

declare(strict_types=1);

namespace App\Ai\Tools\Interaction;

use App\Ai\Tools\BaseTool;
use NeuronAI\Tools\PropertyType;
use NeuronAI\Tools\ToolProperty;

use function Laravel\Prompts\confirm;

class ConfirmTool extends BaseTool
{
    public function __construct()
    {
        parent::__construct(
            name: 'confirm_action',
            description: 'Ask the user for a yes/no confirmation.',
        );
    }

    protected function properties(): array
    {
        return [
            ToolProperty::make(
                name: 'label',
                type: PropertyType::STRING,
                description: 'The question or confirmation message to display.',
            ),
            ToolProperty::make(
                name: 'default',
                type: PropertyType::BOOLEAN,
                description: 'The default value if the user presses enter.',
            ),
            ToolProperty::make(
                name: 'hint',
                type: PropertyType::STRING,
                description: 'An optional hint to display.',
            ),
        ];
    }

    public function __invoke(string $label, bool $default = true, string $hint = ''): bool
    {
        return confirm($label, $default, hint: $hint);
    }
}
