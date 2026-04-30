<?php

declare(strict_types=1);

namespace App\Ai\Tools\Interaction;

use App\Ai\Tools\BaseTool;
use NeuronAI\Tools\PropertyType;
use NeuronAI\Tools\ToolProperty;

use function Laravel\Prompts\select;

class ChoiceTool extends BaseTool
{
    public function __construct()
    {
        parent::__construct(
            name: 'ask_choice',
            description: 'Ask the user to choose one option from a list.',
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
                name: 'options',
                type: PropertyType::ARRAY,
                description: 'The list of options to choose from. Can be a flat array or associative (value => label).',
            ),
            ToolProperty::make(
                name: 'default',
                type: PropertyType::STRING,
                description: 'The default option.',
            ),
            ToolProperty::make(
                name: 'hint',
                type: PropertyType::STRING,
                description: 'An optional hint to display.',
            ),
        ];
    }

    public function __invoke(string $label, array $options, ?string $default = null, string $hint = ''): string|int
    {
        return select($label, $options, $default, hint: $hint);
    }
}
