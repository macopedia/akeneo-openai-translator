<?php

declare(strict_types=1);

namespace Macopedia\OpenAiTranslator\Translator;

interface TranslatorInterface
{
    public function translate(string $text, Language $targetLanguageCode): ?array;
}
