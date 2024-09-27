<?php

declare(strict_types=1);

namespace Macopedia\OpenAiTranslator\Exception;

use Exception;
use Throwable;

use function sprintf;

final class InvalidTranslationResponseException extends Exception
{
    public function __construct(string $prompt, Throwable $previousException)
    {
        parent::__construct(sprintf(
            'Translation from OpenAi contains invalid JSON. Prompt: %s',
            $prompt
        ), 0, $previousException);
    }
}
