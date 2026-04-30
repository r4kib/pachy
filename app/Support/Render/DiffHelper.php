<?php

namespace App\Support\Render;

use SebastianBergmann\Diff\Differ;
use SebastianBergmann\Diff\Output\UnifiedDiffOutputBuilder;
use function Termwind\render;

class DiffHelper
{
    public static function renderDiff(string $old, string $new, string $filePath): void
    {
        $differ = new Differ(new UnifiedDiffOutputBuilder);
        $diffArray = $differ->diffToArray($old, $new);

        render("<div class='bg-blue-600 text-white px-1 mt-1'>File: {$filePath}</div>");

        $html = '<table class="w-full mt-1">';

        $leftBuffer = [];
        $rightBuffer = [];
        $context = 3;

        $totalLines = count($diffArray);
        $linesToShow = [];

        // Identify which lines to show based on changes + context
        foreach ($diffArray as $i => $change) {
            if ($change[1] !== 0) { // Change (added or removed)
                for ($j = max(0, $i - $context); $j <= min($totalLines - 1, $i + $context); $j++) {
                    $linesToShow[$j] = true;
                }
            }
        }

        $flush = function() use (&$leftBuffer, &$rightBuffer, &$html) {
            $max = max(count($leftBuffer), count($rightBuffer));
            for ($i = 0; $i < $max; $i++) {
                $l = $leftBuffer[$i] ?? ['line' => '', 'type' => 'empty'];
                $r = $rightBuffer[$i] ?? ['line' => '', 'type' => 'empty'];

                $lText = htmlspecialchars($l['line']);
                $rText = htmlspecialchars($r['line']);

                $lClass = $l['type'] === 2 ? 'text-red-500 bg-red-900' : ($l['type'] === 'empty' ? '' : 'text-gray-400');
                $rClass = $r['type'] === 1 ? 'text-green-500 bg-green-900' : ($r['type'] === 'empty' ? '' : 'text-gray-400');

                if ($l['type'] === 0 && $r['type'] === 0) {
                    $lClass = 'text-gray-500';
                    $rClass = 'text-gray-500';
                }

                $html .= "<tr>
                    <td class='w-1/2 $lClass'>$lText</td>
                    <td class='w-1/2 $rClass'>$rText</td>
                </tr>";
            }
            $leftBuffer = [];
            $rightBuffer = [];
        };

        $lastShown = -1;
        foreach ($diffArray as $i => $change) {
            if (!isset($linesToShow[$i])) {
                continue;
            }

            if ($lastShown !== -1 && $i > $lastShown + 1) {
                $flush();
                $html .= "<tr><td colspan='2' class='text-center text-gray-600 italic font-bold'>...</td></tr>";
            }

            $line = $change[0];
            $type = $change[1];

            if ($type === 0) { // Unchanged
                $flush();
                $leftBuffer[] = ['line' => $line, 'type' => 0];
                $rightBuffer[] = ['line' => $line, 'type' => 0];
                $flush();
            } elseif ($type === 1) { // Added
                $rightBuffer[] = ['line' => $line, 'type' => 1];
            } elseif ($type === 2) { // Removed
                $leftBuffer[] = ['line' => $line, 'type' => 2];
            }
            $lastShown = $i;
        }
        $flush();

        $html .= '</table>';

        render($html);
    }
}
