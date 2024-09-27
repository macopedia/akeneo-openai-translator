<?php

declare(strict_types=1);

namespace Macopedia\OpenAiTranslator\Exception;

use Exception;

use function json_encode;
use function sprintf;

use const CURLINFO_RESPONSE_CODE;

final class InvalidOpenAiResponseException extends Exception
{
    public function __construct(array $requestMessage, array $requestCurlInfo)
    {
        parent::__construct(sprintf(
            'Response from OpenAi is invalid. HTTP code: %d. Request: %s',
            (int) $requestCurlInfo[CURLINFO_RESPONSE_CODE],
            json_encode($requestMessage)
        ));
    }
}
