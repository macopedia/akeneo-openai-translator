<?php

declare(strict_types=1);

namespace Macopedia\OpenAiTranslator\Translator;

use Macopedia\OpenAiTranslator\Client\OpenAiClient;

class OpenAiTranslator implements TranslatorInterface
{
    private const MESSAGE = 'Translate all values in the given JSON between <<<START>>> and <<<STOP>>> to %s. Do not translate any part of the HTML markup, such as tag names, attributes, or classes. Translate only the text content inside the HTML tags. Ensure the output is valid JSON with same keys as input (translate values and do not change the keys). <<<START>>>%s<<<STOP>>>';

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
            $answer = preg_replace('/(\(Note.*)/', '', $answer);
        }

        return $answer;
    }
}
