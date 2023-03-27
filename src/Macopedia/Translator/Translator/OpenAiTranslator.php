<?php

declare(strict_types=1);

namespace Macopedia\Translator\Translator;

use Macopedia\Translator\Client\OpenAiClient;

class OpenAiTranslator implements TranslatorInterface
{
    private const NO_TRANSLATION = 'NO_TRANSLATION';
    //todo improve message to translate multiple attributes
    private const MESSAGE = 'Translate the text after a semicolon (keeping the HTML unchanged) to %s, if possible. If not possible, write \'' . self::NO_TRANSLATION . '\';';

    public function __construct(
        private OpenAiClient $openAiClient
    ) {
    }

    public function translate(string $text, Language $targetLanguageCode): string
    {
        $answer = $this
            ->openAiClient
            ->ask('user', sprintf(self::MESSAGE, $targetLanguageCode->asText()) . $text)
            ->getFirstChoiceMessage();

        if ($answer === null || str_contains($answer, self::NO_TRANSLATION)) {
            return '';
        }

        return $answer;
    }
}
