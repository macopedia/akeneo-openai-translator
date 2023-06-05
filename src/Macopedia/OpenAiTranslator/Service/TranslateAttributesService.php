<?php

declare(strict_types=1);

namespace Macopedia\OpenAiTranslator\Service;

use Akeneo\Pim\Enrichment\Component\Product\EntityWithFamilyVariant\CheckAttributeEditable;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModelInterface;
use Akeneo\Pim\Structure\Component\AttributeTypes;
use Akeneo\Pim\Structure\Component\Repository\AttributeRepositoryInterface;
use Akeneo\Tool\Component\Batch\Item\DataInvalidItem;
use Akeneo\Tool\Component\Batch\Job\BatchStatus;
use Akeneo\Tool\Component\Batch\Job\ExitStatus;
use Akeneo\Tool\Component\Batch\Model\StepExecution;
use Akeneo\Tool\Component\StorageUtils\Updater\PropertySetterInterface;
use Macopedia\OpenAiTranslator\Translator\Language;
use Macopedia\OpenAiTranslator\Translator\TranslatorInterface;
use Macopedia\OpenAiTranslator\Exception\InvalidOpenAiResponseException;
use Webmozart\Assert\Assert;

class TranslateAttributesService
{
    private StepExecution $stepExecution;

    public function __construct(
        private TranslatorInterface $translator,
        private AttributeRepositoryInterface $attributeRepository,
        private CheckAttributeEditable $checkAttributeEditable,
        private PropertySetterInterface $propertySetter
    ) {
    }

    public function setStepExecution(StepExecution $stepExecution): self
    {
        $this->stepExecution = $stepExecution;

        return $this;
    }

    /**
     * @param ProductInterface|ProductModelInterface $product
     * @param array<string, string|array<int, string>> $action
     */
    public function translateAttributes(mixed $product, array $action): ProductInterface|ProductModelInterface
    {
        [$sourceScope, $targetScope, $sourceLocaleAkeneo, $targetLocaleAkeneo, $targetLocale, $attributesToTranslate] = $this->extractVariables($action);
        $translations = [];

        $attributes = $this->attributeRepository->getAttributesByCodes($attributesToTranslate);
        $summary = [];
        $scopes = [];

        foreach ($attributes as $attribute) {
            if (!$this->checkAttributeEditable->isEditable($product, $attribute)) {
                $this->stepExecution->addWarning('Attribute is not editable', [], new DataInvalidItem($attribute));
                $this->stepExecution->incrementSummaryInfo('skip');
                $this->stepExecution->incrementProcessedItems();
                continue;
            }

            if (!($attribute->getType() === AttributeTypes::TEXT || $attribute->getType() === AttributeTypes::TEXTAREA)) {
                $this->stepExecution->addWarning('Attribute is not text', [], new DataInvalidItem($attribute));
                $this->stepExecution->incrementSummaryInfo('skip');
                $this->stepExecution->incrementProcessedItems();
                continue;
            }

            if (!$attribute->isScopable()) {
                $sourceScope = null;
                $targetScope = null;
            }

            $attributeCode = $attribute->getCode();
            $attributeValue = $product->getValue($attributeCode, $sourceLocaleAkeneo, $sourceScope);
            if ($attributeValue === null) {
                $this->stepExecution->addWarning('Attribute value is empty', [], new DataInvalidItem($attribute));
                $this->stepExecution->incrementSummaryInfo('skip');
                $this->stepExecution->incrementProcessedItems();
                continue;
            }

            $translations[$attributeCode] = $attributeValue->getData();
            $scopes[$attributeCode] = $targetScope;
        }

        $translatedText = $this->translator->translate(
            json_encode($translations),
            $targetLocale
        );

        if ($translatedText === null) {
            throw new InvalidOpenAiResponseException($translations);
        }

        foreach (json_decode($translatedText) as $key => $translation) {
            $summary[$key] = [[$translations[$key] => $translation]];
            $this->propertySetter->setData($product, $key, $translation, [
                'locale' => $targetLocaleAkeneo,
                'scope' => $scopes[$key],
            ]);
        }

        $this->stepExecution->setExitStatus(new ExitStatus(ExitStatus::COMPLETED, json_encode($summary)));

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
