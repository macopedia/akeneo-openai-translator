<?php

declare(strict_types=1);

namespace Macopedia\OpenAiTranslator\Client\OpenAiClient;

use Macopedia\OpenAiTranslator\Client\OpenAiClient\Response\ChoicesResponse;
use Macopedia\OpenAiTranslator\Client\OpenAiClient\Response\ErrorResponse;
use Webmozart\Assert\Assert;
use DateTime;
use RuntimeException;

class Response
{
    public static function fromArray(array $data): self
    {
        if (array_key_exists('id', $data)) {
            Assert::keyExists($data, 'object');
            Assert::keyExists($data, 'created');
            Assert::keyExists($data, 'model');
            Assert::keyExists($data, 'choices');
            Assert::keyExists($data, 'usage');

            return new ChoicesResponse(
                $data['id'],
                $data['object'],
                (new DateTime())->setTimestamp($data['created']),
                $data['model'],
                new Choices($data['choices']),
                $data['usage']
            );
        }

        if (array_key_exists('error', $data)) {
            return new ErrorResponse($data['error']);
        }

        throw new RuntimeException('Can not create Response');
    }
}
