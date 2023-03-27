<?php

declare(strict_types=1);

namespace Macopedia\Translator\Client\OpenAiClient;

use Macopedia\Translator\Client\OpenAiClient\Choices;
use Webmozart\Assert\Assert;
use DateTime;

class Response
{
    public function __construct(
        private string $id,
        private string $object,
        private DateTime $created,
        private string $model,
        private Choices $choices,
        array $usage
    ) {
    }

    public static function fromArray(array $data)
    {
        Assert::keyExists($data, 'id');
        Assert::keyExists($data, 'object');
        Assert::keyExists($data, 'created');
        Assert::keyExists($data, 'model');
        Assert::keyExists($data, 'choices');
        Assert::keyExists($data, 'usage');

        return new self(
            $data['id'],
            $data['object'],
            (new DateTime())->setTimestamp($data['created']),
            $data['model'],
            new Choices($data['choices']),
            $data['usage']
        );
    }

    /**
     * @return Choices
     */
    public function getChoices(): Choices
    {
        return $this->choices;
    }

    public function getFirstChoiceMessage(): ?string
    {
        $firstMessage = $this->choices->getAnswers()[0] ?? null;

        if ($firstMessage === null) {
            return null;
        }
        return $firstMessage->getMessage()->getContent();
    }
}
