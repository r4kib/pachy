<?php

declare(strict_types=1);

namespace App\Ai\Tools\FileSystem;

use Greph\Ast\AstMatch;
use Greph\Ast\AstSearchOptions;
use Greph\Greph;
use Greph\Text\TextFileResult;
use Greph\Text\TextSearchOptions;
use NeuronAI\Tools\PropertyType;
use NeuronAI\Tools\Tool;
use NeuronAI\Tools\ToolProperty;
use PhpParser\Node;

use function is_dir;
use function is_file;

class CodebaseSearchTool extends Tool
{
    protected ?int $maxRuns = 20;

    public function __construct()
    {
        parent::__construct(
            name: 'codebase_search',
            description: 'Powerful codebase search using the Greph engine. Supports standard regex (text mode) and structural PHP patterns (AST mode) to find logic across the repository.',
        );
    }

    protected function properties(): array
    {
        return [
            ToolProperty::make(
                name: 'pattern',
                type: PropertyType::STRING,
                description: 'The search pattern. For mode=\'text\', use regex or strings. For mode=\'ast\', use PHP-like snippets (e.g., \'new $CLASS()\').',
                required: true,
            ),
            ToolProperty::make(
                name: 'mode',
                type: PropertyType::STRING,
                description: 'The search engine to use. \'text\' for grep/ripgrep style; \'ast\' for structural PHP matching.',
                required: true,
            ),
            ToolProperty::make(
                name: 'path',
                type: PropertyType::STRING,
                description: 'Target directory or file path (default: .).',
            ),
            ToolProperty::make(
                name: 'case_insensitive',
                type: PropertyType::BOOLEAN,
                description: 'Ignore case for text searches.',
            ),
            ToolProperty::make(
                name: 'context_lines',
                type: PropertyType::INTEGER,
                description: 'Number of lines of code to show before/after matches (text mode).',
            ),
            ToolProperty::make(
                name: 'programming_language',
                type: PropertyType::STRING,
                description: 'Programming language (AST mode) (default: php).',
            ),
        ];
    }

    public function __invoke(
        string $pattern,
        string $mode,
        ?string $path = '.',
        ?bool $case_insensitive = true,
        ?int $context_lines = 0,
        ?string $programming_language = 'php'
    ): string {
        $path = $path ?? '.';
        $case_insensitive = $case_insensitive ?? true;
        $context_lines = $context_lines ?? 0;
        $programming_language = $programming_language ?? 'php';
        if (! is_dir($path) && ! is_file($path)) {
            return "Error: Path '{$path}' does not exist.";
        }

        if (! in_array($mode, ['text', 'ast'], true)) {
            return "Error: Invalid mode '{$mode}'. Must be 'text' or 'ast'.";
        }

        try {
            if ($mode === 'text') {
                $results = Greph::searchText($pattern, $path, new TextSearchOptions(caseInsensitive: $case_insensitive, beforeContext: $context_lines, afterContext: $context_lines));
            } elseif ($mode === 'ast') {
                $results = Greph::searchAst($pattern, $path, new AstSearchOptions(language: $programming_language));
            }
        } catch (\Exception $e) {
            return "Error during search: {$e->getMessage()}";
        }

        $matchCount = count($results);

        if ($matchCount === 0) {
            return "No matches found for pattern '{$pattern}' in mode '{$mode}' at path '{$path}'.";
        }

        if ($matchCount > 50) {
            $summary = "Found {$matchCount} matches across multiple files. Showing the first 5 matches.\n\n";
            $displayResults = array_slice($results, 0, 5);

            foreach ($displayResults as $result) {
                $summary .= $this->formatResult($result)."\n";
            }

            $summary .= "\nPlease refine your search pattern or path to see more results.";

            return $summary;
        }

        $output = '';
        foreach ($results as $result) {
            $formatted = $this->formatResult($result);
            if ($formatted) {
                $output .= $formatted."\n";
            }
        }

        return $output ?: "No results found for pattern '{$pattern}'.";
    }

    private function formatResult(mixed $result): string
    {
        $cwd = getcwd();
        if ($result instanceof TextFileResult) {
            $output = '';
            $file = str_replace($cwd, '', $result->file);
            foreach ($result->matches as $match) {
                $output .= sprintf('%s:%d:%d:%s', $file,
                    $match->line,
                    $match->column,
                    trim($match->content)
                );
            }

            return $output;
        }

        if ($result instanceof AstMatch) {
            return $this->formatAstMatch($result);
        }

        return '';
    }

    public function formatAstMatch(AstMatch $match): string
    {
        // 1. Format captures into a compact string: key=val,key2=val2
        $caps = [];
        foreach ($match->captures as $key => $val) {
            $value = ($val instanceof Node) ? $val->getType() : (string) $val;
            $caps[] = "$key=$value";
        }
        $capString = implode(',', $caps);

        $cleanCode = str_replace(["\n", "\r"], ' ', substr($match->code, 0, 100));
        $cleanCode = preg_replace('/\s+/', ' ', $cleanCode);

        return sprintf(
            '%s:%d:%d: [%s] %s',
            $match->file,
            $match->startLine,
            $match->startFilePos,
            $capString,
            trim($cleanCode)
        );
    }
}
