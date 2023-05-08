<?php

declare(strict_types=1);

namespace Macopedia\OpenAiTranslator;

use Symfony\Component\HttpKernel\Bundle\Bundle;

final class MacopediaTranslatorBundle extends Bundle
{
    public function getParent()
    {
        return null;
    }
}
