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

    protected function filterToolsRequiringApproval(array $tools): array
    {
        return array_filter(
            $tools,
            $this->toolRequiresApproval(...)
        );
    }

    protected function toolRequiresApproval(ToolInterface $tool): bool
    {
        if (method_exists($tool, 'requiresHumanApproval')) {
            return $tool->requiresHumanApproval($tool->getInputs());
        }

        return false;
    }
}
