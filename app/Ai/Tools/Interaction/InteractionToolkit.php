<?php

declare(strict_types=1);

namespace App\Ai\Tools\Interaction;

use NeuronAI\Tools\Toolkits\AbstractToolkit;

/**
 * @method static static make()
 */
class InteractionToolkit extends AbstractToolkit
{
    public function guidelines(): ?string
    {
        return 'Use these tools to interact with the user, ask for confirmation, choice or additional information. Use ask_user for multiple questions at once.';
    }

    public function provide(): array
    {
        return [
            AskUserTool::make(),
            ConfirmTool::make(),
            ChoiceTool::make(),
            AskTextTool::make(),
        ];
    }
}
