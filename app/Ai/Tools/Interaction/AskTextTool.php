<?php

declare(strict_types=1);

namespace App\Ai\Tools\Interaction;

use App\Ai\Tools\BaseTool;
use NeuronAI\Tools\PropertyType;
use NeuronAI\Tools\ToolProperty;

use function Laravel\Prompts\text;

class AskTextTool extends BaseTool
{
    public function __construct()
    {
        parent::__construct(
            name: 'ask_text',
            description: 'Ask the user for a text answer.',
        );
    }

    protected function properties(): array
    {
        return [
            ToolProperty::make(
                name: 'label',
                type: PropertyType::STRING,
                description: 'The question to display.',
            ),
            ToolProperty::make(
                name: 'default',
                type: PropertyType::STRING,
                description: 'The default answer.',
            ),
            ToolProperty::make(
                name: 'placeholder',
                type: PropertyType::STRING,
                description: 'The placeholder text.',
            ),
            ToolProperty::make(
                name: 'hint',
                type: PropertyType::STRING,
                description: 'An optional hint to display.',
            ),
        ];
    }

    public function __invoke(string $label, ?string $default = null, ?string $placeholder = null, string $hint = ''): string
    {
        return text($label, $placeholder ?? '', $default ?? '', hint: $hint);
    }
}
