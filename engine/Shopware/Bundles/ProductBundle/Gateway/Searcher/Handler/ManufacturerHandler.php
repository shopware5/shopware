<?php

namespace ProductBundle\Gateway\Searcher\Handler;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Query\QueryBuilder;
use ProductBundle\Struct\Manufacturer;
use SearchBundle\AggregatorInterface;
use SearchBundle\HandlerInterface;
use Shopware\Bundle\SearchBundle\Condition\ManufacturerCondition;
use Shopware\Bundle\SearchBundle\Criteria;
use Shopware\Bundle\SearchBundle\CriteriaPartInterface;
use Shopware\Bundle\SearchBundle\Facet\ManufacturerFacet;
use Shopware\Bundle\SearchBundle\FacetResult\ValueListFacetResult;
use Shopware\Bundle\SearchBundle\FacetResult\ValueListItem;
use Shopware\Bundle\StoreFrontBundle\Context\TranslationContext;

class ManufacturerHandler implements HandlerInterface, AggregatorInterface
{
    const REQUEST_PARAMETER = 's';

    /**
     * @var ManufacturerReader
     */
    private $manufacturerReader;

    public function supports(CriteriaPartInterface $criteriaPart): bool
    {
        return (
            $criteriaPart instanceof ManufacturerCondition
            ||
            $criteriaPart instanceof ManufacturerFacet
        );
    }

    public function handle(
        CriteriaPartInterface $criteriaPart,
        QueryBuilder $builder,
        Criteria $criteria,
        TranslationContext $context
    ) {
        $key = ':' . self::class;

        $builder->andWhere('product.supplierID IN (' . $key . ')');

        /** @var ManufacturerCondition $criteriaPart */
        $builder->setParameter($key, $criteriaPart, Connection::PARAM_INT_ARRAY);
    }

    public function aggregate(CriteriaPartInterface $criteriaPart, QueryBuilder $builder, Criteria $criteria, TranslationContext $context): ?ValueListFacetResult
    {
        $builder->resetQueryPart('groupBy');
        $builder->resetQueryPart('orderBy');

        $builder->groupBy('product.id');
        $builder->select('DISTINCT product.supplierID as id');

        /** @var $statement \Doctrine\DBAL\Driver\ResultStatement */
        $statement = $builder->execute();

        $ids = $statement->fetchAll(\PDO::FETCH_COLUMN);
        $ids = array_filter($ids);

        if (empty($ids)) {
            return null;
        }

//        $manufacturers = $this->manufacturerReader->getList($ids, $context);
//
//        $activeManufacturers = $this->getActiveIds($criteria);
//
//        return $this->createFacetResult($criteriaPart, $manufacturers, $activeManufacturers);
    }

    /**
     * @param ManufacturerFacet $facet
     * @param Manufacturer[] $manufacturers
     * @param int[] $activeIds
     *
     * @return ValueListFacetResult
     */
    private function createFacetResult(ManufacturerFacet $facet, $manufacturers, $activeIds): ValueListFacetResult
    {
        $listItems = [];

        /** @var Manufacturer $manufacturer */
        foreach ($manufacturers as $manufacturer) {
            $listItem = new ValueListItem(
                $manufacturer->getId(),
                $manufacturer->getName(),
                in_array($manufacturer->getId(), $activeIds, true),
                $manufacturer->getAttributes()
            );

            $listItems[] = $listItem;
        }

        usort($listItems, function (ValueListItem $a, ValueListItem $b) {
            return strcasecmp($a->getLabel(), $b->getLabel());
        });

        return new ValueListFacetResult(
            'manufacturer',
            !empty($activeIds),
            $facet->getLabel(),
            $listItems,
            self::REQUEST_PARAMETER
        );
    }

    /**
     * @param Criteria $criteria
     * @return int[]
     */
    private function getActiveIds(Criteria $criteria): array
    {
        if (!$criteria->hasCondition(ManufacturerCondition::class)) {
            return [];
        }

        /** @var $condition ManufacturerCondition */
        $condition = $criteria->getCondition(ManufacturerCondition::class);

        return $condition->getManufacturerIds();
    }
}