<?php

namespace App\Support;

use App\Ai\Agent\CoderAgent;
use App\Observers\CliToolObserver;
use Illuminate\Support\Facades\File;
use NeuronAI\Workflow\Persistence\FilePersistence;
use Symfony\Component\Finder\Finder;

class AgentHelper
{
    public static function getSessionDirectory(): string
    {
        return getcwd().DIRECTORY_SEPARATOR.'.pachy'.DIRECTORY_SEPARATOR.'storage'.DIRECTORY_SEPARATOR.'sessions';
    }

    public static function initCoderAgent(): CoderAgent
    {
        $dir = self::getSessionDirectory();
        $storageDir = dirname($dir);

        if (! File::isDirectory($dir)) {
            File::makeDirectory($dir, 0755, true);
        }

        self::cleanupOldSessions($dir);

        $ignoreFile = $storageDir.DIRECTORY_SEPARATOR.'.gitignore';
        if (! File::exists($ignoreFile)) {
            File::put($ignoreFile, "*\n!.gitignore");
        }

        $store = new FilePersistence($dir, 'coder_session_');
        $agent = CoderAgent::make();
        $agent->observe(new CliToolObserver)
            ->setPersistence($store);

        return $agent;
    }

    /**
     * Cleanup old persistence files, keeping only the most recent ones.
     */
    public static function cleanupOldSessions(?string $dir = null, int $keep = 100): void
    {
        $directories = array_unique(array_filter([
            $dir ?? self::getSessionDirectory(),
            storage_path('app'),
        ]));

        foreach ($directories as $directory) {
            if (! File::isDirectory($directory)) {
                continue;
            }

            $files = Finder::create()
                ->files()
                ->in($directory)
                ->name('*.store')
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
