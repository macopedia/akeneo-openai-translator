<?php

declare(strict_types=1);

namespace Macopedia\OpenAiTranslator\Client\OpenAiClient\Response;

use Macopedia\OpenAiTranslator\Client\OpenAiClient\Response;

class ErrorResponse extends Response
{
    public function __construct(
        private string $error
    ) {
    }

    public function getError(): string
    {
        return $this->error;
    }
}
