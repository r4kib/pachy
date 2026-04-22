<?php

namespace App\Support;

use Illuminate\Support\Str;
use function Termwind\render;
use function Termwind\terminal;

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

    public static function renderMarkDown($fullContent): void
    {
        try {
            render(Str::markdown($fullContent));
        } catch (\Exception $e) {
            render($fullContent);
        }

    }

}
