<?php

declare(strict_types=1);

namespace Macopedia\OpenAiTranslator\Client\OpenAiClient;

class Choice
{
    public function __construct(
        private Message $message,
        private string $finishReason,
        private int $index
    ) {
    }

    public function getMessage(): Message
    {
        return $this->message;
    }
}
