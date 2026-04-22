<?php

namespace App\Observers;

use App\Support\RenderHelper;
use NeuronAI\Observability\Events\ToolCalled;
use NeuronAI\Observability\Events\ToolCalling;
use NeuronAI\Observability\ObserverInterface;

class CliToolObserver implements ObserverInterface
{
    public function onEvent(string $event, object $source, mixed $data = null): void
    {
        if ($data instanceof ToolCalling) {
            $tool = $data->tool;
            $inputs = json_encode($tool->getInputs());
            RenderHelper::renderToolStarting($tool->getName(),$inputs);
        }

        if ($data instanceof ToolCalled) {
            $tool = $data->tool;

            try {
                $result = $tool->getResult();
                $dataArray = json_decode($result, true);
                if (isset($dataArray['status']) && $dataArray['status'] === 'error') {
                    $error = $dataArray['message'] ?? 'Unknown error';
                    RenderHelper::renderToolFailure($error);
                } else {
                    RenderHelper::renderToolSuccess($result);
                }
            } catch (\Exception $e) {
                    RenderHelper::renderToolFailure($e->getMessage());
            }
        }
    }
}
