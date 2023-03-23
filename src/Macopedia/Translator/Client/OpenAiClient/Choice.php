<?php

namespace Macopedia\Translator\Client\OpenAiClient;

class Choice
{
    public function __construct(
        private Message $message,
        private string $finishReason,
        private int $index
    ) {}

    public function getMessage(): Message
    {
        return $this->message;
    }
}