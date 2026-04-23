<?php

namespace App\Support;

final class StreamHealer
{
    /**
     * Heals unclosed Markdown syntax for intermediate rendering.
     */
    public static function heal(string $buffer): string
    {
        // 1. Handle Fenced Code Blocks (```)
        if (str_count($buffer, "```") % 2 !== 0) {
            $buffer .= "\n```"; // Close the code block
        }

        // 2. Handle Inline Code (`)
        if (str_count($buffer, "`") % 2 !== 0) {
            $buffer .= "`";
        }

        // 3. Handle Bold (**)
        // We look for patterns where ** is opened but not closed
        if (preg_match_all('/\*\*/', $buffer) % 2 !== 0) {
            $buffer .= "**";
        }

        // 4. Handle Italic (*)
        // Note: Logic must skip single '*' used in lists or bold
        if (preg_match_all('/(?<!\*)\*(?!\*)/', $buffer) % 2 !== 0) {
            $buffer .= "*";
        }

        return $buffer;
    }
}
