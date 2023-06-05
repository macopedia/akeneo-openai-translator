<?php

declare(strict_types=1);

namespace Macopedia\OpenAiTranslator\Connector\Processor\MassEdit;

use Akeneo\Pim\Enrichment\Component\Product\Connector\Processor\MassEdit\AbstractProcessor;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModelInterface;
use Exception;
use InvalidArgumentException;
use Macopedia\OpenAiTranslator\Service\TranslateAttributesService;

final class TranslateAttributesProcessor extends AbstractProcessor
{
    public function __construct(
        private TranslateAttributesService $translateAttributesService
    ) {
    }

    public function process(mixed $item): ProductInterface|ProductModelInterface
    {
        if (!$item instanceof ProductInterface && !$item instanceof ProductModelInterface) {
            throw new InvalidArgumentException("Invalid $item type to this processor");
        }
        $actions = $this->getConfiguredActions();

        return $this->translateAttributes($item, $actions[0]);
    }

    /**
     * @param ProductInterface|ProductModelInterface $product
     * @param array<string, string|array<int, string>> $action
     *  $actions = [
     *      'sourceChannel' => 'ecommerce',
     *      'targetChannel' => 'ecommerce',
     *      'sourceLocale' => 'pl_PL',
     *      'targetLocale' => 'en_US',
     *      'attributesToTranslate' => [
     *          'name',
     *          'description',
     *     ]
     *  ];
     * @throws Exception
     */
    private function translateAttributes(mixed $product, array $action): ProductInterface|ProductModelInterface
    {
        return $this->translateAttributesService
            ->setStepExecution($this->stepExecution)
            ->translateAttributes($product, $action);
    }
}
