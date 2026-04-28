<?php

namespace App\Ai\Tools;

use NeuronAI\Tools\Tool;

abstract class BaseTool extends Tool
{
    protected ?int $maxRuns = 20;
}
