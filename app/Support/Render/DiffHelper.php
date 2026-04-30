<?php

namespace App\Support\Render;

use SebastianBergmann\Diff\Differ;
use SebastianBergmann\Diff\Output\UnifiedDiffOutputBuilder;
use function Termwind\render;

class DiffHelper
{
    public static function renderDiff(string $old, string $new, string $filePath): void
    {
        $diffs = new Differ(new UnifiedDiffOutputBuilder)->diff($old, $new);
        foreach (explode("\n",$diffs) as $diff) {
            $class = "";
            if (str_starts_with($diff, '+')) {
                $class = 'text-green';
            } else if (str_starts_with($diff, '-')) {
                $class = 'text-red';
            } else if (str_starts_with($diff, '@@')) {
                $class = 'text-yellow';
            }

            render("<span class='{$class}'>{$diff}</span>");
        }

        render("<span class='text-white'>==============================</span>");
        render("<span class='text-blue-600'>File: {$filePath}</span>");
    }
}
