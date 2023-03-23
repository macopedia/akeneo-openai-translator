<?php

namespace Macopedia\Translator\Client;

use Macopedia\Translator\Client\OpenAiClient\Response;
use Macopedia\Translator\Exception\InvalidOpenAiResponseException;
use Orhanerday\OpenAi\OpenAi;
use Psr\Log\LoggerInterface;

class OpenAiClient
{
    private OpenAi $client;

    public function __construct(
        private LoggerInterface $logger,
        private string $model,
        string $apiKey,
        string $organization = null,
        private bool $logging = false
    )
    {
        $this->client = new OpenAi($apiKey);
        $this->client->listModels();

        if ($organization) {
            $this->client->setORG($organization);
        }
    }

    public function ask(string $role, string $content): ?Response
    {
        $message = $this->generateMessage($role, $content);

        if ($this->logging) {
            $this->logger->notice('OpenAI Message prepared', $message);
        }

        $response = $this->client->chat($message);

        if ($response === false) {
            throw new InvalidOpenAiResponseException();
        }

        $response = Response::fromArray(json_decode($response, true));

        if ($this->logging) {
            $this->logger->notice('OpenAI answer received', ['message' => $response->getFirstChoiceMessage() ?? 'EMPTY']);
        }

        return $response;
    }

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