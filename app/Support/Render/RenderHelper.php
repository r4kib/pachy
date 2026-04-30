<?php

namespace App\Support\Render;

use function Termwind\render;

class RenderHelper
{
    public static function renderToolStarting(string $name, string $inputs): void
    {
        render('<div class="px-1 bg-blue-600 text-white">🚀 TOOL STARTING: '.$name.'</div>');
        render('<div class="text-gray-300  italic">Inputs: '.$inputs.'</div>');
    }

    public static function renderToolSuccess(string $result): void
    {
        render('<div class="text-green-500">✔ Tool execution successful.</div>');
        render('<div class="text-gray-300 italic">Result: '.$result.'</div>');
    }

    public static function renderToolFailure(string $error): void
    {
        render('<div class="px-1 bg-red-600 text-white">✘ TOOL FAILED</div>');
        render('<div class="text-red-500 font-bold">Error: '.$error.'</div>');
    }

    public static function renderNoToolResult(): void
    {
        render('<div class="px-1 bg-red-600 text-white">[!] Failed to get tool result</div>');

    }

    public static function renderApprovalPreview(mixed $action): void
    {

        $inputs = json_decode($action->description, true);

        if ($action->name === 'edit_file' && isset($inputs['file_path'], $inputs['search'], $inputs['replace'])) {
            DiffHelper::renderDiff($inputs['search'], $inputs['replace'], $inputs['file_path']);
        } elseif ($action->name === 'write_file' && isset($inputs['file_path'], $inputs['content'])) {
            $oldContent = file_exists($inputs['file_path']) ? file_get_contents($inputs['file_path']) : '';
            DiffHelper::renderDiff($oldContent, $inputs['content'], $inputs['file_path']);
        }

    }
}
