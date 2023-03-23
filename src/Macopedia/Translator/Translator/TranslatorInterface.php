<?php


namespace Macopedia\Translator\Translator;

interface TranslatorInterface
{
    public function translate(string $text, Language $targetLanguageCode): string;
}
