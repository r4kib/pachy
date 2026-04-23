<?php

namespace App\Support;

use League\CommonMark\GithubFlavoredMarkdownConverter;
use function Termwind\render;

final class TermwindMarkdownConverter
{
    private static ?GithubFlavoredMarkdownConverter $converter = null;

    public static function render(string $markdown): void
    {
        if (self::$converter === null) {
            self::$converter = new GithubFlavoredMarkdownConverter([
                'html_input' => 'strip',
                'allow_unsafe_links' => false,
                'renderer' => [
                    'block_separator' => "\n",
                    'inner_separator' => "\n",
                    'soft_break'      => "\n",
                ],
            ]);
        }

        $html = self::$converter->convert($markdown)->getContent();

        $styledHtml = self::applyTermwindStyles($html);
        render($styledHtml);
    }

    private static function applyTermwindStyles(string $html): string
    {
        $replacements = [
            '<h1>' => '<div class="font-bold text-blue-400 uppercase">',
            '</h1>' => '</div>',
            '<h2>' => '<div class="font-bold text-blue-200 mt-1">',
            '</h2>' => '</div>',
            '<strong>' => '<b class="text-white font-bold">',
            '</strong>' => '</b>',
            '<code>' => '<span class="bg-gray-800 text-yellow-300 px-1 font-mono">',
            '</code>' => '</span>',
            '<pre>' => '<div class="bg-gray-900 p-2 my-1 border-l-2 border-gray-600 text-green-400">',
            '</pre>' => '</div>',
            '<blockquote>' => '<div class="border-l-4 border-gray-500 pl-2 italic text-gray-400">',
            '</blockquote>' => '</div>',
        ];

        return str_replace(array_keys($replacements), array_values($replacements), $html);
    }
}
