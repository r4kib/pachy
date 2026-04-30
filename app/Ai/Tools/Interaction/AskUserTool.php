<?php

declare(strict_types=1);

namespace App\Ai\Tools\Interaction;

use App\Ai\Tools\BaseTool;
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
            ToolProperty::make(
                name: 'questions',
                type: PropertyType::ARRAY,
                description: 'An array of question objects to ask the user. Each object should have id, type (text, confirm, select, multiselect), label, and optional options (for select/multiselect) or default value.',
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
