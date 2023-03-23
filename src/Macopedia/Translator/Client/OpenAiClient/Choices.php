<?php

namespace Macopedia\Translator\Client\OpenAiClient;

use Webmozart\Assert\Assert;

class Choices
{
    /** @var Choice[] */
    private array $answers;

    public function __construct(array $data)
    {
        foreach ($data as $choice) {
            $this->answers[] = new Choice(
                new Message(
                    $choice['message']['role'],
                    $choice['message']['content']
                ),
                $choice['finish_reason'],
                $choice['index']
            );
        }
    }

    /**
     * @return Choice[]
     */
    public function getAnswers(): array
    {
        return $this->answers;
    }
}