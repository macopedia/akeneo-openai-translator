<?php

declare(strict_types=1);

namespace Macopedia\Translator\Translator;

interface TranslatorInterface
{
    public function translate(string $text, Language $targetLanguageCode): string;
}
