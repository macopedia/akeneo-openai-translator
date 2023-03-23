<?php

namespace Macopedia\Translator\Exception;

class InvalidOpenAiResponseException extends \Exception
{
    public function __construct()
    {
        parent::__construct('Response from OpenAi is empty');
    }
}
