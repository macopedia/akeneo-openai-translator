<?php

declare(strict_types=1);

namespace Macopedia\OpenAiTranslator\Client\OpenAiClient\Response;

use Macopedia\OpenAiTranslator\Client\OpenAiClient\Choices;
use Macopedia\OpenAiTranslator\Client\OpenAiClient\Response;
use DateTime;

class ChoicesResponse extends Response
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
