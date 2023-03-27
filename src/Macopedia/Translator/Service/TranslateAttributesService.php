<?php

declare(strict_types=1);

namespace Macopedia\Translator\Service;

use Akeneo\Pim\Enrichment\Component\Product\EntityWithFamilyVariant\CheckAttributeEditable;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModelInterface;
use Akeneo\Pim\Enrichment\Component\Product\Value\ScalarValue;
use Akeneo\Pim\Structure\Component\Repository\AttributeRepositoryInterface;
use Akeneo\Tool\Component\StorageUtils\Updater\PropertySetterInterface;
use Macopedia\Translator\Translator\Language;
use Macopedia\Translator\Translator\TranslatorInterface;
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

            if (!($attribute instanceof ScalarValue)) {
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
            'sourceScope' => $action['sourceChannel'],
            'targetScope' => $action['targetChannel'],
            'sourceLocaleAkeneo' => $action['sourceLocale'],
            'targetLocaleAkeneo' => $action['targetLocale'],
            'targetLocale' => Language::fromCode(explode('_', $action['targetLocale'])[0]),
            'attributesToTranslate' => $action['attributesToTranslate']
        ];
    }
}
