<?php

namespace App\Ai\Agent\Middleware;

use NeuronAI\Agent\Middleware\ToolApproval;
use NeuronAI\Tools\ToolInterface;


class HumanApproval extends ToolApproval
{
    public function __construct(
    ) {
        parent::__construct();
    }

    protected function toolRequiresApproval(ToolInterface $tool): bool
    {
        if (method_exists($tool, 'shouldConfirm')) {
            return $tool->shouldConfirm($tool->getInputs());
        }

        return false;
    }
}
