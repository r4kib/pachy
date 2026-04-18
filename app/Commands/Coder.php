<?php

namespace App\Commands;

use Illuminate\Console\Scheduling\Schedule;
use LaravelZero\Framework\Commands\Command;
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
        if (!$this->option('interactive')) {
            // Single prompt mode
            if (!$this->argument('prompt')) {
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
            $agent = \App\Ai\Agent\Coder::make();
            $message = UserMessage::make($prompt);
            $response = $agent->chat($message)->getMessage();

            $content = $response->getContent();

            if ($this->option('file')) {
                file_put_contents($this->option('file'), $content);
                $this->info("✓ Response saved to {$this->option('file')}");
                return Command::SUCCESS;
            }

            $this->info("\n🤖 Response:\n");
            $this->line($content);

            return Command::SUCCESS;
        } catch (\Exception $e) {
            $this->error("❌ Error: " . $e->getMessage());
            return Command::FAILURE;
        }
    }

    /**
     * Run the Coder agent in interactive mode.
     */
    private function runInteractiveCoder(): int
    {
        $this->info('🤖 Coder Agent - Interactive Mode');
        $this->info('Type your coding prompt and press Enter. Type "!exit" to quit.\n');

        try {
            $agent = \App\Ai\Agent\Coder::make();

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
                $response = $agent->chat($message)->getMessage();

                $content = $response->getContent();

                $this->line($content);
                $this->newLine();

                $save = $this->confirm('Save this response to a file?', false);

                if ($save) {
                    $filename = $this->ask('Enter filename (e.g., response.md)', 'response.md');
                    file_put_contents($filename, $content);
                    $this->info("✓ Saved to {$filename}");
                }

                $this->newLine();
            }

            return Command::SUCCESS;
        } catch (\Exception $e) {
            $this->error("❌ Error: " . $e->getMessage());
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
