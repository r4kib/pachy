<?php

namespace App\Commands;

use App\Ai\Agent\CoderAgent;
use App\Observers\CliToolObserver;
use App\Support\CliMarkdownRenderer;
use Illuminate\Console\Scheduling\Schedule;
use LaravelZero\Framework\Commands\Command;
use NeuronAI\Chat\Messages\Stream\Chunks\TextChunk;
use NeuronAI\Chat\Messages\UserMessage;

class Coder extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'coder
                            {prompt? : The coding task or prompt}
                            {--file= : Output result to a file}
                            {--interactive : Enable interactive mode}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'AI Coder Agent - Write and modify code with AI assistance';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        if (! $this->option('interactive')) {
            // Single prompt mode
            if (! $this->argument('prompt')) {
                return $this->error('Please provide a coding prompt or use the --interactive flag.');
            }

            return $this->runCoder($this->argument('prompt'));
        } else {
            // Interactive mode
            return $this->runInteractiveCoder();
        }
    }

    /**
     * Run the Coder agent with a single prompt.
     */
    private function runCoder(string $prompt): int
    {
        $this->info('🚀 Starting Coder Agent...');
        $this->line("💭 Prompt: {$prompt}");

        try {
            $agent = CoderAgent::make();
            $agent->observe(new CliToolObserver);

            $message = UserMessage::make($prompt);

            $this->info('🤖 Thinking...');

            $stream = $agent->stream($message);
            $fullContent = '';
            foreach ($stream->events() as $chunk) {
                if ($chunk instanceof TextChunk) {
                    $fullContent .= $chunk->content;
                }
            }
            $this->newLine();
            $this->line(CliMarkdownRenderer::render($fullContent));

            return Command::SUCCESS;
        } catch (\Exception $e) {
            print_r($e->getMessage());
            $this->error('❌ Error: '.$e->getMessage());

            return Command::FAILURE;
        }
    }

    /**
     * Run the Coder agent in interactive mode.
     */
    private function runInteractiveCoder(): int
    {
        $this->info('🤖 Coder Agent - Interactive Mode');
        $this->info('Type your coding prompt and press Enter. Type "!exit" to quit.');

        try {
            $agent = CoderAgent::make();
            $agent->observe(new CliToolObserver);

            while (true) {
                $prompt = $this->ask('What would you like me to code?');

                if (trim($prompt) === '!exit') {
                    $this->info('👋 Goodbye!');
                    break;
                }

                if (empty($prompt)) {
                    continue;
                }

                $this->line("💭 Processing: {$prompt}");
                $this->newLine();

                $message = UserMessage::make($prompt);

                $this->info('🤖 Thinking...');
                $stream = $agent->stream($message);
                $fullContent = '';
                foreach ($stream->events() as $chunk) {
                    if ($chunk instanceof TextChunk) {
                        $fullContent .= $chunk->content;
                    }
                }

                $this->newLine();
                $this->line(CliMarkdownRenderer::render($fullContent));

                $this->newLine();
            }

            return Command::SUCCESS;
        } catch (\Exception $e) {
            $this->error('❌ Error: '.$e->getMessage());

            return Command::FAILURE;
        }
    }

    /**
     * Define the command's schedule.
     */
    public function schedule(Schedule $schedule): void
    {
        // $schedule->command(static::class)->everyMinute();
    }
}
