<?php

namespace App\Support;

use App\Ai\Agent\CoderAgent;
use App\Observers\CliToolObserver;
use Illuminate\Support\Facades\File;
use NeuronAI\Chat\History\FileChatHistory;
use NeuronAI\Workflow\Persistence\FilePersistence;
use Symfony\Component\Finder\Finder;

class AgentHelper
{
    public static function getSessionDirectory(): string
    {
        return getcwd().DIRECTORY_SEPARATOR.'.pachy'.DIRECTORY_SEPARATOR.'storage'.DIRECTORY_SEPARATOR.'sessions';
    }

    public static function getChatDirectory(): string
    {
        return getcwd().DIRECTORY_SEPARATOR.'.pachy'.DIRECTORY_SEPARATOR.'storage'.DIRECTORY_SEPARATOR.'chats';
    }

    public static function initCoderAgent(bool $continue = false): CoderAgent
    {
        $dir = self::getSessionDirectory();
        $chatDir = self::getChatDirectory();
        $storageDir = dirname($dir);

        if (! File::isDirectory($dir)) {
            File::makeDirectory($dir, 0755, true);
        }

        if (! File::isDirectory($chatDir)) {
            File::makeDirectory($chatDir, 0755, true);
        }

        self::cleanupOldSessions($dir);

        $ignoreFile = $storageDir.DIRECTORY_SEPARATOR.'.gitignore';
        if (! File::exists($ignoreFile)) {
            File::put($ignoreFile, "*\n!.gitignore");
        }

        $sessionId = null;
        if ($continue) {
            $sessionId = self::getLatestSessionId($dir);
        }

        $store = new FilePersistence($dir, 'coder_session_');
        $agent = CoderAgent::make();

        $agent->observe(new CliToolObserver)
            ->setPersistence($store, $sessionId);

        // Also set persistent chat history using the same session ID
        $agent->setChatHistory(new FileChatHistory(
            $chatDir,
            $agent->getWorkflowId(),
            50000,
            'coder_chat_'
        ));

        return $agent;
    }

    public static function getLatestSessionId(string $dir): ?string
    {
        $chatDir = self::getChatDirectory();
        
        $files = Finder::create()
            ->files()
            ->in([$dir, $chatDir])
            ->name(['coder_session_*.store', 'coder_chat_*.chat'])
            ->sortByModifiedTime()
            ->reverseSorting();

        foreach ($files as $file) {
            $filename = $file->getFilename();
            // coder_session_{id}.store or coder_chat_{id}.chat
            if (preg_match('/(?:coder_session_|coder_chat_)(.*)\.(?:store|chat)/', $filename, $matches)) {
                return $matches[1];
            }
        }

        return null;
    }

    /**
     * Cleanup old persistence files, keeping only the most recent ones.
     */
    public static function cleanupOldSessions(?string $dir = null, int $keep = 100): void
    {
        $directories = array_unique(array_filter([
            $dir ?? self::getSessionDirectory(),
            self::getChatDirectory(),
            storage_path('app'),
        ]));

        foreach ($directories as $directory) {
            if (! File::isDirectory($directory)) {
                continue;
            }

            $files = Finder::create()
                ->files()
                ->in($directory)
                ->name(['*.store', '*.chat'])
                ->sortByModifiedTime()
                ->reverseSorting();

            $count = 0;
            foreach ($files as $file) {
                $count++;
                if ($count > $keep) {
                    File::delete($file->getRealPath());
                }
            }
        }
    }
}
