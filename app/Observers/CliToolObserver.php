<?php

namespace App\Observers;

use NeuronAI\Observability\Events\ToolCalled;
use NeuronAI\Observability\Events\ToolCalling;
use NeuronAI\Observability\ObserverInterface;

use function Termwind\render;

class CliToolObserver implements ObserverInterface
{
    public function onEvent(string $event, object $source, mixed $data = null): void
    {
        // 📥 Tool Start
        if ($data instanceof ToolCalling) {
            $tool = $data->tool;
            $inputs = json_encode($tool->getInputs());
            render(<<<HTML
                <div class="px-1 bg-blue-600 text-white">🚀 TOOL STARTING: {$tool->getName()}</div>
                <div class="text-gray-500 italic">Inputs: {$inputs}</div>
            HTML);
        }

        // ✅ Tool Success/Failure
        if ($data instanceof ToolCalled) {
            $tool = $data->tool;
            if ($tool->hasFailed()) {
                $error = $tool->getException()?->getMessage() ?? 'Unknown error';
                render(<<<HTML
                    <div class="px-1 bg-red-600 text-white">✘ TOOL FAILED</div>
                    <div class="text-red-500 font-bold">Error: {$error}</div>
                HTML);
            } else {
                render('<div class="text-green-500">✔ Tool execution completed successfully.</div>');
            }
        }
    }
}
