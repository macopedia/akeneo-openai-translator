<?php

declare(strict_types=1);

namespace Macopedia\OpenAiTranslator\Translator;

use JsonException;
use Macopedia\OpenAiTranslator\Client\OpenAiClient;
use Macopedia\OpenAiTranslator\Exception\EmptyTranslationResponseException;
use Macopedia\OpenAiTranslator\Exception\InvalidTranslationResponseException;

use function json_decode;
use function preg_replace;
use function sprintf;
use function str_replace;

use const JSON_THROW_ON_ERROR;

class OpenAiTranslator implements TranslatorInterface
{
    private const MARKET_START = '__AI_TRANSLATE_START__';
    private const MARKET_STOP = '__AI_TRANSLATE_STOP__';
    private const MESSAGE = 'Translate all values in the given JSON between "' . self::MARKET_START . '" and "' . self::MARKET_STOP . ' " markers to "%s" locale. If HTML tags are present, do not translate any part of the HTML markup, such as tag names, attributes, or classes (translate only the text inside HTML tags). Translate only the values of the JSON data between markers and ensure that no changes are made to the JSON keys. Do not include the markers "' . self::MARKET_START . '" and "' . self::MARKET_STOP . '" in the final output. The output have to be a valid JSON identical to the input JSON structure, except for translated values. "' . self::MARKET_START . '"%s"' . self::MARKET_STOP . '"';

    public function __construct(
        private OpenAiClient $openAiClient,
    ) {
    }

    public function translate(string $text, Language $targetLanguageCode): ?array
    {
        $prompt = sprintf(self::MESSAGE, $targetLanguageCode->asText(), $text);
        $answer = $this
            ->openAiClient
            ->ask('user', $prompt);

        if ($answer === null) {
            throw new EmptyTranslationResponseException($prompt);
        }
        $answer = preg_replace('/(\(Note.*)/', '', $answer);
        $answer = str_replace([self::MARKET_START, self::MARKET_STOP], '', $answer);
        try {
            return json_decode($answer, true, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException $exception) {
            throw new InvalidTranslationResponseException($prompt, $exception);
        }
    }
}
