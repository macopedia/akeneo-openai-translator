<?php

declare(strict_types=1);

namespace Macopedia\OpenAiTranslator\Service;

use Akeneo\Pim\Enrichment\Component\Product\EntityWithFamilyVariant\CheckAttributeEditable;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModelInterface;
use Akeneo\Pim\Structure\Component\AttributeTypes;
use Akeneo\Pim\Structure\Component\Model\Attribute;
use Akeneo\Pim\Structure\Component\Repository\AttributeRepositoryInterface;
use Akeneo\Tool\Component\Batch\Item\DataInvalidItem;
use Akeneo\Tool\Component\Batch\Job\ExitStatus;
use Akeneo\Tool\Component\Batch\Model\StepExecution;
use Akeneo\Tool\Component\StorageUtils\Updater\PropertySetterInterface;
use Macopedia\OpenAiTranslator\Translator\Language;
use Macopedia\OpenAiTranslator\Translator\TranslatorInterface;
use Psr\Log\LoggerInterface;
use Throwable;
use Webmozart\Assert\Assert;

use function array_merge;
use function in_array;
use function json_encode;
use function sprintf;
use function trim;

class TranslateAttributesService
{
    private StepExecution $stepExecution;

    public function __construct(
        private TranslatorInterface $translator,
        private AttributeRepositoryInterface $attributeRepository,
        private CheckAttributeEditable $checkAttributeEditable,
        private PropertySetterInterface $propertySetter,
        private LoggerInterface $logger
    ) {
    }

    public function setStepExecution(StepExecution $stepExecution): self
    {
        $this->stepExecution = $stepExecution;

        return $this;
    }

    /**
     * @param ProductInterface|ProductModelInterface   $product
     * @param array<string, string|array<int, string>> $action
     */
    public function translateAttributes(mixed $product, array $action): ProductInterface|ProductModelInterface
    {
        [$sourceScope, $targetScope, $sourceLocaleAkeneo, $targetLocaleAkeneo, $targetLocale, $attributesToTranslate] = $this->extractVariables($action);
        $translations = [];

        /** @var Attribute[] $attributes */
        $attributes = $this->attributeRepository->getAttributesByCodes($attributesToTranslate);
        $summary = [];
        $scopes = [];

        foreach ($attributes as $attribute) {
            if (!$this->checkAttributeEditable->isEditable($product, $attribute)) {
                $this->addWarning('The attribute is not editable. WARNING! This is completely normal if one of the selected products is a product model', $product, $attribute);
                $this->stepExecution->incrementSummaryInfo('skip');
                $this->stepExecution->incrementProcessedItems();
                continue;
            }

            if (!$attribute->isLocalizable()) {
                $this->addWarning('The attribute is not localizable', $product, $attribute);
                $this->stepExecution->incrementSummaryInfo('skip');
                $this->stepExecution->incrementProcessedItems();
                continue;
            }

            if (!in_array($attribute->getType(), [AttributeTypes::TEXT, AttributeTypes::TEXTAREA], true)) {
                $this->addWarning('The attribute is not text or textarea', $product, $attribute);
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
            if ($attributeValue === null || !$attributeValue->hasData()) {
                $this->addWarning('The attribute value is empty', $product, $attribute, $sourceLocaleAkeneo, $sourceScope);
                $this->stepExecution->incrementSummaryInfo('skip');
                $this->stepExecution->incrementProcessedItems();
                continue;
            }

            $translations[$attributeCode] = $attributeValue->getData();
            $scopes[$attributeCode] = $targetScope;
        }

        if (!$translations) {
            $this->stepExecution->addWarning(sprintf('No valid attributes to translate on product: %s', $product->getIdentifier()), [], new DataInvalidItem(['product_identifier' => $product->getIdentifier()]));
            return $product;
        }

        try {
            $aiResponse = $this->translator->translate(
                json_encode($translations),
                $targetLocale
            );
        } catch (Throwable $exception) {
            $this->logger->error($exception->getMessage(), ['product_identifier' => $product->getIdentifier()]);
            throw $exception;
        }

        $aiResponse = $this->translator->translate(
            json_encode($translations),
            $targetLocale
        );
        foreach ($aiResponse as $key => $translation) {
            $key = trim($key);
            if (!$key) {
                continue;
            }
            if (!in_array($key, $attributesToTranslate, true)) {
                $this->addWarning(sprintf('Unexpected key in response: %s', $key), $product);
                $this->logger->warning(sprintf('Unexpected key in response: %s', $key), ['respomse' => json_encode($aiResponse)]);
                continue;
            }
            $summary[$key] = [[$translations[$key] => $translation]];
            $this->propertySetter->setData($product, $key, $translation, [
                'locale' => $targetLocaleAkeneo,
                'scope' => $scopes[$key],
            ]);
        }

        $this->stepExecution->setExitStatus(new ExitStatus(ExitStatus::COMPLETED, json_encode($summary)));

        return $product;
    }

    private function addWarning(string $message, ProductInterface|ProductModelInterface $product, ?Attribute $attribute = null, ?string $sourceLocale = null, ?string $sourceScope = null): void
    {
        $context = array_merge(
            ['product_identifier' => $product->getIdentifier()],
            $attribute ? ['attribute_code' => $attribute->getCode()] : [],
            $sourceLocale ? ['source_locale' => $sourceLocale] : [],
            $sourceScope ? ['source_scope' => $sourceScope] : [],
        );
        $this->stepExecution->addWarning($message, [], new DataInvalidItem($context));
        $this->logger->warning($message, $context);
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
            $action['attributesToTranslate'],
        ];
    }
}
