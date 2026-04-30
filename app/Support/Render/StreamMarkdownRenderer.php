<?php

namespace App\Support\Render;

class StreamMarkdownRenderer
{
    private string $buffer = '';

    private bool $inCodeBlock = false;

    public function push(string $chunk): string
    {
        $this->buffer .= $chunk;
        $output = '';

        while ($block = $this->extractCompleteBlock()) {
            $output .= $this->render($block);
        }

        return $output;
    }

    private function extractCompleteBlock(): ?string
    {
        if ($this->inCodeBlock) {
            if (($pos = strpos($this->buffer, '```')) !== false) {
                $end = $pos + 3;
                $block = substr($this->buffer, 0, $end);
                $this->buffer = substr($this->buffer, $end);
                $this->inCodeBlock = false;

                return $block;
            }

            return null;
        }

        if (preg_match('/^```/', $this->buffer)) {
            $this->inCodeBlock = true;

            return null;
        }

        if (($pos = strpos($this->buffer, "\n\n")) !== false) {
            $block = substr($this->buffer, 0, $pos);
            $this->buffer = substr($this->buffer, $pos + 2);

            return trim($block);
        }

        if (preg_match('/^(#{1,6} .+)\n/', $this->buffer, $m)) {
            $this->buffer = substr($this->buffer, strlen($m[0]));

            return trim($m[1]);
        }

        if (preg_match('/^([-*] .+\n)+/', $this->buffer, $m)) {
            $this->buffer = substr($this->buffer, strlen($m[0]));

            return trim($m[0]);
        }

        return null;
    }

    private function render(string $block): string
    {
        if (str_starts_with($block, '```')) {
            return $this->renderCode($block);
        }

        if (preg_match('/^#{1,6} /', $block)) {
            return $this->renderHeading($block);
        }

        if (preg_match('/^[-*] /m', $block)) {
            return $this->renderList($block);
        }

        return $this->renderParagraph($block);
    }

    private function renderHeading(string $text): string
    {
        $level = strspn($text, '#');
        $content = trim(substr($text, $level));

        return "\033[1;".(30 + $level).'m'.strtoupper($content)."\033[0m\n";
    }

    private function renderParagraph(string $text): string
    {
        return $this->inline($text)."\n\n";
    }

    private function renderList(string $text): string
    {
        $out = '';
        foreach (explode("\n", trim($text)) as $line) {
            $out .= ' • '.$this->inline(substr($line, 2))."\n";
        }

        return $out."\n";
    }

    private function renderCode(string $block): string
    {
        $code = trim(trim($block, '`'));

        return "\033[40;37m\n$code\n\033[0m\n\n";
    }

    private function inline(string $text): string
    {
        $text = preg_replace('/\*\*(.*?)\*\*/', "\033[1m$1\033[0m", $text);
        $text = preg_replace('/\*(.*?)\*/', "\033[3m$1\033[0m", $text);
        $text = preg_replace('/`(.*?)`/', "\033[40;37m$1\033[0m", $text);

        return $text;
    }
}
