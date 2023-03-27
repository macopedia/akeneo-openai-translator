<?php

declare(strict_types=1);

namespace Macopedia\Translator\Exception;

use Exception;

class InvalidOpenAiResponseException extends Exception
{
    public function __construct(array $request)
    {
        parent::__construct(sprintf('Response from OpenAi is empty. Request: %s', json_encode($request)));
    }
}
