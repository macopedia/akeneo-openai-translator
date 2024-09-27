<?php

declare(strict_types=1);

namespace Macopedia\OpenAiTranslator\Exception;

use Exception;

use function sprintf;

final class EmptyTranslationResponseException extends Exception
{
    public function __construct(string $prompt)
    {
        parent::__construct(sprintf(
            'Translation from OpenAi is empty. Prompt: %s',
            $prompt
        ));
    }
}
