<?php

namespace App\Commands;

use App\Ai\Agent\CoderAgent;
use App\Observers\CliToolObserver;
use App\Support\RenderHelper;
use App\Support\StreamHealer;
use App\Support\StreamMarkdownRenderer;
use App\Support\TermwindMarkdownConverter;
use Illuminate\Console\Scheduling\Schedule;
use LaravelZero\Framework\Commands\Command;
use NeuronAI\Agent\AgentHandler;
use NeuronAI\Chat\Messages\UserMessage;
use NeuronAI\Chat\Messages\Stream\Chunks\TextChunk;
use NeuronAI\Workflow\Interrupt\ApprovalRequest;
use NeuronAI\Workflow\Interrupt\WorkflowInterrupt;
use NeuronAI\Workflow\Persistence\FilePersistence;

class Coder extends Command
{
    protected CoderAgent $agent;
    protected $signature = 'coder';

    protected $description = 'AI Coder Agent - Write and modify code with AI assistance';

    public function handle()
    {
        $this->info('🤖 Coder Agent - Interactive Mode');
        $this->info('Type your coding prompt and press Enter. Type "exit" to quit.');
        $this->agent = $this->getAgent();

        try {
            $this->runAgent();
            return Command::SUCCESS;
        } catch (\Exception $e) {
            $this->error('❌ Error: ' . $e->getMessage());
            return Command::FAILURE;
        }
    }

    public function runAgent(): void
    {

        while (true) {
            $prompt = $this->ask('What would you like me to code?');

            if (empty($prompt)) continue;

            if (trim($prompt) === 'exit') {
                $this->info('👋 Goodbye!');
                break;
            }


            $this->line("💭 Processing: {$prompt}");
            $this->newLine();

            $message = UserMessage::make($prompt);

            $this->info('🤖 Thinking...');
            try {
                $this->handleResponse($this->agent->stream($message));
            } catch (WorkflowInterrupt $e) {
                $this->handleInterrupt($e);
            }

        }

    }

    private function handleInterrupt(WorkflowInterrupt $e): void
    {
        try {
            $approvalRequest = $e->getRequest();
            if (!($approvalRequest instanceof ApprovalRequest)) return;

            foreach ($approvalRequest->getPendingActions() as $action) {
                $this->handleApproval($action);
            }

            $this->handleResponse($this->agent->stream(interrupt: $approvalRequest));

        } catch (WorkflowInterrupt $nested) {
            $this->handleInterrupt($nested);
        }
    }


    private function getAgent(): CoderAgent
    {
        $store = new FilePersistence('storage/app');
        $agent = CoderAgent::make();
        $agent->observe(new CliToolObserver)
            ->setPersistence($store);
        return $agent;
    }

    /**
     * Define the command's schedule.
     */
    public function schedule(Schedule $schedule): void
    {
        // $schedule->command(static::class)->everyMinute();
    }

    public function handleResponse(AgentHandler $response): void
    {
        $this->newLine();
        $renderer = new StreamMarkdownRenderer();

        foreach ($response->events() as $event) {
            if ($event instanceof TextChunk) {
                $output = $renderer->push($event->content);

                if ($output !== '') {
                    $this->output->write($output);
                }

            }
        }

        $this->newLine();

        $this->printUsageStats($response);
    }

    private function printUsageStats(AgentHandler $response): void
    {
        $time = microtime(true) - LARAVEL_START;
        $memory = memory_get_peak_usage(true) / 1024 / 1024;
        $usage = $response->getMessage()->getUsage();

        $this->newLine();
        $this->components->info(sprintf(
            'Stats: Time: %.2fs | Memory: %.2f MB | Tokens (In, Out, Total): %d , %d , %d | CWD: %s',
            $time,
            $memory,
            $usage->inputTokens ?? 0,
            $usage->outputTokens ?? 0,
            $usage->getTotal() ?? 0,
            getcwd()
        ));
        $this->newLine();
    }

    public function handleApproval(mixed $action): void
    {
        $this->warn("[!] TOOL APPROVAL");
        $this->line("Tool: {$action->name} " .
            str_replace("\n", '  ', $action->description));

        if ($this->confirm('Allow this action?', true)) {
            $action->approve();
        } else {
            $action->reject('User declined.');
        }
    }

}
