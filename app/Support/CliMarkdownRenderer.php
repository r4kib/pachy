<?php

namespace App\Support;

use League\CommonMark\Environment\Environment;
use League\CommonMark\Extension\CommonMark\CommonMarkCoreExtension;
use League\CommonMark\Extension\Table\TableExtension;
use League\CommonMark\Extension\HeadingPermalink\HeadingPermalinkExtension;
use League\CommonMark\MarkdownConverter;

class CliMarkdownRenderer
{
    public static function render(string $markdown): string
    {
        $config = [
            'renderer' => [
                'block_separator' => "\n",
                'inner_separator' => "\n",
            ],
        ];

        $environment = new Environment($config);
        $environment->addExtension(new CommonMarkCoreExtension());
        $environment->addExtension(new TableExtension());
        $environment->addExtension(new HeadingPermalinkExtension());

        $converter = new MarkdownConverter($environment);

        $html = $converter->convert($markdown)->getContent();
        
        // Remove class attributes from code tags
        $html = preg_replace('/<code class=".*?">/', '<code>', $html);

        // Very basic conversion of HTML to CLI-friendly text
        $text = strip_tags($html, ['code', 'strong', 'em', 'h1', 'h2', 'h3']);
        $text = preg_replace('/<h[1-3]>(.*?)<\/h[1-3]>/', "\n\033[1;34m$1\033[0m\n", $text);
        $text = preg_replace('/<strong>(.*?)<\/strong>/', "\033[1m$1\033[0m", $text);
        $text = preg_replace('/<em>(.*?)<\/em>/', "\033[3m$1\033[0m", $text);
        $text = preg_replace('/<code>(.*?)<\/code>/', "\033[32m$1\033[0m", $text);

        return $text;
    }
}
