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
        if ($data instanceof ToolCalling) {
            $tool = $data->tool;
            $inputs = json_encode($tool->getInputs());
            render(<<<HTML
                <div class="px-1 bg-blue-600 text-white">🚀 TOOL STARTING: {$tool->getName()}</div>
            HTML);
            render(<<<HTML
                <div class="text-gray-500 italic">Inputs: {$inputs}</div>
            HTML);
        }

        if ($data instanceof ToolCalled) {
            $tool = $data->tool;
            $result = $tool->getResult();
            $dataArray = json_decode($result, true);

            if (isset($dataArray['status']) && $dataArray['status'] === 'error') {
                $error = $dataArray['message'] ?? 'Unknown error';
                render(<<<HTML
                    <div class="px-1 bg-red-600 text-white">✘ TOOL FAILED</div>
                HTML);
                render(<<<HTML
                    <div class="text-red-500 font-bold">Error: {$error}</div>
                HTML);
            } else {
                render('<div class="text-green-500">✔ Tool execution completed successfully.</div>');
                render(<<<HTML
                <div class="text-gray-500 italic">Inputs: {$result}</div>
            HTML);
            }
        }
    }
}
