<?php

namespace App\Support;

use App\Ai\Agent\CoderAgent;
use App\Observers\CliToolObserver;
use Illuminate\Support\Facades\File;
use NeuronAI\Workflow\Persistence\FilePersistence;

class AgentHelper
{
    public static function initCoderAgent(): CoderAgent
    {
        $storageDir = getcwd().DIRECTORY_SEPARATOR.'.pachy'.DIRECTORY_SEPARATOR.'storage';

        $dir = $storageDir.DIRECTORY_SEPARATOR.'sessions';

        if (! File::isDirectory($dir)) {
            File::makeDirectory($dir, 0755, true);
        }

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
}
