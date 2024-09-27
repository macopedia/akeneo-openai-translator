<?php

declare(strict_types=1);

namespace Macopedia\OpenAiTranslator\Client;

use JetBrains\PhpStorm\ArrayShape;
use Macopedia\OpenAiTranslator\Client\OpenAiClient\Response;
use Macopedia\OpenAiTranslator\Exception\ErrorOpenAiResponseException;
use Macopedia\OpenAiTranslator\Exception\InvalidOpenAiResponseException;
use Orhanerday\OpenAi\OpenAi;
use Psr\Log\LoggerInterface;

class OpenAiClient
{
    private OpenAi $client;

    public function __construct(
        private string $model,
        ?string $apiKey = '',
        ?string $organization = null
    ) {
        $this->client = new OpenAi($apiKey);
        $this->client->listModels();

        if ($organization) {
            $this->client->setORG($organization);
        }
    }

    public function ask(string $role, string $content): ?string
    {
        $message = $this->generateMessage($role, $content);

        $response = $this->client->chat($message);
        if ($response === false) {
            throw new InvalidOpenAiResponseException($message, $this->client->getCURLInfo());
        }

        $response = Response::fromArray(json_decode($response, true));
        if ($response instanceof Response\ErrorResponse) {
            throw new ErrorOpenAiResponseException($message, $response);
        }

        return $response->getFirstChoiceMessage();
    }


    #[ArrayShape(['model' => 'string', 'messages' => "\string[][]"])]
    private function generateMessage(string $role, string $content): array
    {
        return [
            'model' => $this->model,
            'messages' => [[
                'role' => $role,
                'content' => $content
            ]
            ]
        ];
    }
}
