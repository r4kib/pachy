<?php

namespace App\Concerns\Ai\Tools;

trait RequiresHumanApproval
{
    public function requiresHumanApproval(array $arguments): bool
    {
        return true;
    }
}
