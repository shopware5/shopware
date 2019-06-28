<?php
/**
 * Shopware 5
 * Copyright (c) shopware AG
 *
 * According to our dual licensing model, this program can be used either
 * under the terms of the GNU Affero General Public License, version 3,
 * or under a proprietary license.
 *
 * The texts of the GNU Affero General Public License with an additional
 * permission and of our proprietary license can be found at and
 * in the LICENSE file you have received along with this program.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Affero General Public License for more details.
 *
 * "Shopware" is a registered trademark of shopware AG.
 * The licensing of the program under the AGPLv3 does not imply a
 * trademark license. Therefore any rights, title and interest in
 * our trademarks remain entirely with us.
 */

namespace Shopware\Bundle\SearchBundleDBAL\FacetHandler;

use Shopware\Bundle\SearchBundle\Condition\PropertyCondition;
use Shopware\Bundle\SearchBundle\Criteria;
use Shopware\Bundle\SearchBundle\Facet;
use Shopware\Bundle\SearchBundle\FacetInterface;
use Shopware\Bundle\SearchBundle\FacetResult\FacetResultGroup;
use Shopware\Bundle\SearchBundle\FacetResult\MediaListFacetResult;
use Shopware\Bundle\SearchBundle\FacetResult\MediaListItem;
use Shopware\Bundle\SearchBundle\FacetResult\ValueListFacetResult;
use Shopware\Bundle\SearchBundle\FacetResultInterface;
use Shopware\Bundle\SearchBundleDBAL\PartialFacetHandlerInterface;
use Shopware\Bundle\SearchBundleDBAL\QueryBuilder;
use Shopware\Bundle\SearchBundleDBAL\QueryBuilderFactoryInterface;
use Shopware\Bundle\StoreFrontBundle\Gateway\PropertyGatewayInterface;
use Shopware\Bundle\StoreFrontBundle\Struct;
use Shopware\Bundle\StoreFrontBundle\Struct\ShopContextInterface;
use Shopware\Components\QueryAliasMapper;

class PropertyFacetHandler implements PartialFacetHandlerInterface
{
    /**
     * @var PropertyGatewayInterface
     */
    private $propertyGateway;

    /**
     * @var QueryBuilderFactoryInterface
     */
    private $queryBuilderFactory;

    /**
     * @var string
     */
    private $fieldName;

    public function __construct(
        PropertyGatewayInterface $propertyGateway,
        QueryBuilderFactoryInterface $queryBuilderFactory,
        QueryAliasMapper $queryAliasMapper
    ) {
        $this->propertyGateway = $propertyGateway;
        $this->queryBuilderFactory = $queryBuilderFactory;

        if (!$this->fieldName = $queryAliasMapper->getShortAlias('sFilterProperties')) {
            $this->fieldName = 'sFilterProperties';
        }
    }

    /**
     * {@inheritdoc}
     */
    public function supportsFacet(FacetInterface $facet)
    {
        return $facet instanceof Facet\PropertyFacet;
    }

    /**
     * @param FacetInterface|Facet\PropertyFacet $facet
     *
     * @return FacetResultInterface|null
     */
    public function generatePartialFacet(
        FacetInterface $facet,
        Criteria $reverted,
        Criteria $criteria,
        ShopContextInterface $context
    ) {
        $properties = $this->getProperties($context, $reverted);

        if ($properties === null) {
            return null;
        }
        $actives = $this->getFilteredValues($criteria);

        /* @var Facet\PropertyFacet $facet */
        return $this->createCollectionResult($facet, $properties, $actives);
    }

    /**
     * @return Struct\Property\Set[]|null
     */
    protected function getProperties(Struct\ShopContextInterface $context, Criteria $queryCriteria)
    {
        $query = $this->queryBuilderFactory->createQuery($queryCriteria, $context);
        $this->rebuildQuery($query);

        /** @var \Doctrine\DBAL\Driver\ResultStatement $statement */
        $statement = $query->execute();

        $propertyData = $statement->fetchAll();

        $valueIds = array_column($propertyData, 'id');
        $filterGroupIds = array_keys(array_flip(array_column($propertyData, 'filterGroupId')));

        if (empty($valueIds)) {
            return null;
        }

        $properties = $this->propertyGateway->getList(
            $valueIds,
            $context,
            $filterGroupIds
        );

        return $properties;
    }

    private function rebuildQuery(QueryBuilder $query)
    {
        $query->resetQueryPart('orderBy');
        $query->resetQueryPart('groupBy');
        $query->innerJoin('product', 's_filter_articles', 'productProperty', 'productProperty.articleID = product.id');
        $query->innerJoin('product', 's_filter', 'propertySet', 'propertySet.id = product.filtergroupID');
        $query->groupBy('productProperty.valueID');
        $query->select('productProperty.valueID as id');

        $query->addSelect('product.filtergroupID as filterGroupId');
        $query->orderBy('propertySet.position');
    }

    /**
     * @return array
     */
    private function getFilteredValues(Criteria $criteria)
    {
        $values = [];
        foreach ($criteria->getConditions() as $condition) {
            if ($condition instanceof PropertyCondition) {
                $values = array_merge($values, $condition->getValueIds());
            }
        }

        return $values;
    }

    /**
     * @param Struct\Property\Set[] $sets
     * @param int[]                 $actives
     *
     * @return FacetResultGroup
     */
    private function createCollectionResult(
        Facet\PropertyFacet $facet,
        array $sets,
        $actives
    ) {
        $results = [];

        foreach ($sets as $set) {
            foreach ($set->getGroups() as $group) {
                $items = [];
                $useMedia = false;
                $isActive = false;

                foreach ($group->getOptions() as $option) {
                    $listItem = new MediaListItem(
                        $option->getId(),
                        $option->getName(),
                        in_array(
                            $option->getId(),
                            $actives
                        ),
                        $option->getMedia(),
                        $option->getAttributes()
                    );

                    $isActive = ($isActive || $listItem->isActive());
                    $useMedia = ($useMedia || $listItem->getMedia() !== null);

                    $items[] = $listItem;
                }

                if ($useMedia) {
                    $results[] = new MediaListFacetResult(
                        $facet->getName(),
                        $isActive,
                        $group->getName(),
                        $items,
                        $this->fieldName,
                        $group->getAttributes()
                    );
                } else {
                    $results[] = new ValueListFacetResult(
                        $facet->getName(),
                        $isActive,
                        $group->getName(),
                        $items,
                        $this->fieldName,
                        $group->getAttributes()
                    );
                }
            }
        }

        return new FacetResultGroup(
            $results,
            null,
            $facet->getName()
        );
    }
}
