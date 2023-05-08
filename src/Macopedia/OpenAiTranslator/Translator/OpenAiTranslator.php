<?php

declare(strict_types=1);

namespace Macopedia\OpenAiTranslator\Translator;

use Macopedia\OpenAiTranslator\Client\OpenAiClient;

class OpenAiTranslator implements TranslatorInterface
{
    private const MESSAGE = 'Translate the text after a semicolon to %s;';

    public function __construct(
        private OpenAiClient $openAiClient
    ) {
    }

    public function translate(string $text, Language $targetLanguageCode): ?string
    {
        return $this
            ->openAiClient
            ->ask('user', sprintf(self::MESSAGE, $targetLanguageCode->asText()) . $text);
    }
}
