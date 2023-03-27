<?php

declare(strict_types=1);

namespace Macopedia\Translator\Client\OpenAiClient;

class Message
{
    public function __construct(
        private string $role,
        private string $content
    ) {
    }

    public function getContent(): string
    {
        return $this->content;
    }
}
