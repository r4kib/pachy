<?php

namespace App\Commands;

use App\Ai\Agent\CoderAgent;
use App\Observers\CliToolObserver;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Support\Str;
use LaravelZero\Framework\Commands\Command;
use NeuronAI\Chat\Messages\Stream\Chunks\TextChunk;
use NeuronAI\Chat\Messages\UserMessage;
use function Termwind\render;

class Coder extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'coder';

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
        $this->info('🤖 Coder Agent - Interactive Mode');
        $this->info('Type your coding prompt and press Enter. Type "!exit" to quit.');

        try {
            $this->runAgent();
            return Command::SUCCESS;
        } catch (\Exception $e) {
            $this->error('❌ Error: '.$e->getMessage());
            return Command::FAILURE;
        }
    }

    public function runAgent(): void
    {
        $agent = CoderAgent::make();
        $agent->observe(new CliToolObserver);

        while (true) {
            $prompt = $this->ask('What would you like me to code?');

            if (empty($prompt)) continue;


            if (trim($prompt) === '!exit') {
                $this->info('👋 Goodbye!');
                break;
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
            render(Str::markdown($fullContent));
            $this->newLine();
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
