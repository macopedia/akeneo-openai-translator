<?php

declare(strict_types=1);

namespace Macopedia\OpenAiTranslator\Exception;

use Exception;
use Macopedia\OpenAiTranslator\Client\OpenAiClient\Response\ErrorResponse;

use function json_encode;
use function sprintf;

final class ErrorOpenAiResponseException extends Exception
{
    public function __construct(array $requestMessage, ErrorResponse $response)
    {
        parent::__construct(sprintf(
            'Response from OpenAi contains error: %s. Request: %s',
            $response->getError(),
            json_encode($requestMessage)
        ));
    }
}
