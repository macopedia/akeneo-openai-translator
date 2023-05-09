<?php

declare(strict_types=1);

namespace Macopedia\OpenAiTranslator\Client\OpenAiClient\Response;

use Macopedia\OpenAiTranslator\Client\OpenAiClient\Response;

class ErrorResponse extends Response
{
    public function __construct(
        private string|array $error
    ) {
    }

    public function getError(): string
    {
        $error = $this->error;

        if (is_array($error)) {
            $error = json_encode($error);
        }
        return $error;
    }
}
