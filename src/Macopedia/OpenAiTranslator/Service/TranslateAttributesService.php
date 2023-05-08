<?php

declare(strict_types=1);

namespace Macopedia\OpenAiTranslator\Service;

use Akeneo\Pim\Enrichment\Component\Product\EntityWithFamilyVariant\CheckAttributeEditable;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModelInterface;
use Akeneo\Pim\Enrichment\Component\Product\Value\ScalarValue;
use Akeneo\Pim\Structure\Component\AttributeTypes;
use Akeneo\Pim\Structure\Component\Repository\AttributeRepositoryInterface;
use Akeneo\Tool\Component\StorageUtils\Updater\PropertySetterInterface;
use Macopedia\OpenAiTranslator\Translator\Language;
use Macopedia\OpenAiTranslator\Translator\TranslatorInterface;
use Webmozart\Assert\Assert;

class TranslateAttributesService
{
    public function __construct(
        private TranslatorInterface $translator,
        private AttributeRepositoryInterface $attributeRepository,
        private CheckAttributeEditable $checkAttributeEditable,
        private PropertySetterInterface $propertySetter
    ) {
    }

    /**
     * @param ProductInterface|ProductModelInterface $product
     * @param array<string, string|array<int, string>> $action
     */
    public function translateAttributes(mixed $product, array $action): ProductInterface|ProductModelInterface
    {
        [$sourceScope, $targetScope, $sourceLocaleAkeneo, $targetLocaleAkeneo, $targetLocale, $attributesToTranslate] = $this->extractVariables($action);

        foreach ($attributesToTranslate as $attributeCode) {
            $attribute = $this->attributeRepository->findOneByIdentifier($attributeCode);
            if (!$this->checkAttributeEditable->isEditable($product, $attribute)) {
                continue;
            }

            if (!($attribute->getType() === AttributeTypes::TEXT || $attribute->getType() === AttributeTypes::TEXTAREA)) {
                continue;
            }

            if (!$attribute->isScopable()) {
                $sourceScope = null;
                $targetScope = null;
            }

            $attributeValue = $product->getValue($attributeCode, $sourceLocaleAkeneo, $sourceScope);
            if ($attributeValue === null) {
                continue;
            }

            $translatedText = $this->translator->translate(
                $attributeValue->getData(),
                $targetLocale
            );

            if ($translatedText === null) {
                continue;
            }

            $this->propertySetter->setData($product, $attributeCode, $translatedText, [
                'locale' => $targetLocaleAkeneo,
                'scope' => $targetScope,
            ]);
        }

        return $product;
    }

    private function extractVariables(array $action): array
    {
        Assert::keyExists($action, 'sourceChannel');
        Assert::keyExists($action, 'targetChannel');
        Assert::keyExists($action, 'sourceLocale');
        Assert::keyExists($action, 'targetLocale');
        Assert::keyExists($action, 'attributesToTranslate');

        return [
            $action['sourceChannel'],
            $action['targetChannel'],
            $action['sourceLocale'],
            $action['targetLocale'],
            Language::fromCode(explode('_', $action['targetLocale'])[0]),
            $action['attributesToTranslate']
        ];
    }
}
