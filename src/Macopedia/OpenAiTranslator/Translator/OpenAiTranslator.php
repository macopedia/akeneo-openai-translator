<?php

declare(strict_types=1);

namespace Macopedia\OpenAiTranslator\Translator;

use Macopedia\OpenAiTranslator\Client\OpenAiClient;

class OpenAiTranslator implements TranslatorInterface
{
    private const MESSAGE = 'Translate text betweeen <START> and <STOP> to %s. Keep HTMl unchanged. <START>%s<STOP>';

    public function __construct(
        private OpenAiClient $openAiClient
    ) {
    }

    public function translate(string $text, Language $targetLanguageCode): ?string
    {
        $answer = $this
            ->openAiClient
            ->ask('user', sprintf(self::MESSAGE, $targetLanguageCode->asText(), $text));

        if ($answer !== null) {
            $answer = preg_replace('/(\(Note.*)/','',$answer);
        }

        return $answer;
    }
}
