<?php

declare(strict_types=1);

namespace App\Ai\Tools\Interaction;

use App\Ai\Tools\BaseTool;
use NeuronAI\Tools\ArrayProperty;
use NeuronAI\Tools\ObjectProperty;
use NeuronAI\Tools\PropertyType;
use NeuronAI\Tools\ToolProperty;

use function Laravel\Prompts\confirm;
use function Laravel\Prompts\multiselect;
use function Laravel\Prompts\select;
use function Laravel\Prompts\text;

class AskUserTool extends BaseTool
{
    public function __construct()
    {
        parent::__construct(
            name: 'ask_user',
            description: 'Ask the user for information, confirmation, or choices. You can ask multiple questions at once.',
        );
    }

    protected function properties(): array
    {
        return [
            ArrayProperty::make(
                name: 'questions',
                description: 'An array of question objects to ask the user. Each object should have id, type (text, confirm, select, multiselect), label, and optional options (for select/multiselect) or default value.',
                items: new ObjectProperty(
                    name: 'question',
                    description: 'An array of question objects to ask the user.',
                    properties: [
                        ToolProperty::make(
                            name: 'id',
                            type: PropertyType::STRING,
                            description: 'question  id for keeping track of question',
                            required: true,
                        ),
                        ToolProperty::make(
                            name: 'type',
                            type: PropertyType::STRING,
                            description: 'question type (text, confirm, select, multiselect)',
                            required: true,
                        ),
                        ArrayProperty::make(
                            name: 'options',
                            description: 'question options for user to select from',
                            items: ToolProperty::make(
                                name: 'option',
                                type: PropertyType::STRING,
                                description: 'question options for user to select from',
                            )
                        ),
                        ToolProperty::make(
                            name: 'label',
                            type: PropertyType::STRING,
                            description: 'question label for user to select from',
                        ),
                        ToolProperty::make(
                            name: 'hint',
                            type: PropertyType::STRING,
                            description: 'question hint',
                        ),
                        ToolProperty::make(
                            name: 'default',
                            type: PropertyType::STRING,
                            description: 'default answer',
                        ),
                        ToolProperty::make(
                            name: 'placeholder',
                            type: PropertyType::STRING,
                            description: 'answer placeholder',
                        )
                    ]
                )
            ),
        ];
    }

    /**
     * @param array<int, array{id: string, type: string, label: string, options?: string[], default?: mixed, placeholder?: string, hint?: string}> $questions
     */
    public function __invoke(array $questions): array
    {
        $answers = [];

        foreach ($questions as $question) {
            $id = $question['id'] ?? 'unnamed';
            $type = $question['type'] ?? 'text';
            $label = $question['label'] ?? 'Question:';
            $options = $question['options'] ?? [];
            $default = $question['default'] ?? null;
            $placeholder = $question['placeholder'] ?? '';
            $hint = $question['hint'] ?? '';

            $answers[$id] = match ($type) {
                'confirm' => confirm(
                    label: $label,
                    default: (bool) ($default ?? true),
                    hint: $hint
                ),
                'select' => select(
                    label: $label,
                    options: $options,
                    default: $default,
                    hint: $hint
                ),
                'multiselect' => multiselect(
                    label: $label,
                    options: $options,
                    default: (array) ($default ?? []),
                    hint: $hint
                ),
                default => text(
                    label: $label,
                    placeholder: $placeholder,
                    default: (string) ($default ?? ''),
                    required: true,
                    hint: $hint
                ),
            };
        }

        return $answers;
    }
}
