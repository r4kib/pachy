<?php

use App\Ai\Agent\Coder;
use NeuronAI\Chat\Messages\UserMessage;

require __DIR__ . '/vendor/autoload.php';

$message = UserMessage::make('Explain what tools I have available to me as a coder');

$agent = Coder::make()->chat($message);
$response = $agent->getMessage();

echo $response->getContent();