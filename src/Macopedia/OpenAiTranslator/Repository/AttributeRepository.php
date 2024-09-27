<?php

declare(strict_types=1);

namespace Macopedia\OpenAiTranslator\Repository;

use Akeneo\Pim\Structure\Bundle\Doctrine\ORM\Repository\AttributeRepository as BaseAttributeRepository;
use Akeneo\Pim\Structure\Component\Model\Attribute;

class AttributeRepository extends BaseAttributeRepository
{
    /** @return Attribute[] */
    public function getAttributesByCodes(array $codes): array
    {
        return $this->_em->createQueryBuilder()
            ->select('att')
            ->from($this->_entityName, 'att', 'att.code')
            ->where('att.code IN (:codes)')->setParameter('codes', $codes)
            ->getQuery()
            ->getResult();
    }
}
