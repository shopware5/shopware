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
use Shopware\Bundle\SearchBundle\FacetResult\FacetResultGroup;
use Shopware\Bundle\SearchBundle\FacetResult\MediaListFacetResult;
use Shopware\Bundle\SearchBundle\FacetResult\MediaListItem;
use Shopware\Bundle\SearchBundle\FacetResult\ValueListFacetResult;
use Shopware\Bundle\SearchBundle\FacetResultInterface;
use Shopware\Bundle\SearchBundleDBAL\PartialFacetHandlerInterface;
use Shopware\Bundle\SearchBundleDBAL\QueryBuilderFactory;
use Shopware\Bundle\SearchBundle\FacetInterface;
use Shopware\Bundle\SearchBundleDBAL\QueryBuilder;
use Shopware\Bundle\SearchBundle\Criteria;
use Shopware\Bundle\SearchBundle\Facet;
use Shopware\Bundle\StoreFrontBundle\Struct;
use Shopware\Bundle\StoreFrontBundle\Gateway\PropertyGatewayInterface;
use Shopware\Bundle\StoreFrontBundle\Struct\ShopContextInterface;
use Shopware\Components\QueryAliasMapper;

/**
 * @category  Shopware
 * @package   Shopware\Bundle\SearchBundleDBAL\FacetHandler
 * @copyright Copyright (c) shopware AG (http://www.shopware.de)
 */
class PropertyFacetHandler implements PartialFacetHandlerInterface
{
    /**
     * @var PropertyGatewayInterface
     */
    private $propertyGateway;

    /**
     * @var QueryBuilderFactory
     */
    private $queryBuilderFactory;

    /**
     * @var string
     */
    private $fieldName;

    /**
     * @param PropertyGatewayInterface $propertyGateway
     * @param QueryBuilderFactory $queryBuilderFactory
     * @param QueryAliasMapper $queryAliasMapper
     */
    public function __construct(
        PropertyGatewayInterface $propertyGateway,
        QueryBuilderFactory $queryBuilderFactory,
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
        return ($facet instanceof Facet\PropertyFacet);
    }

    /**
     * @param FacetInterface|Facet\PropertyFacet $facet
     * @param Criteria $reverted
     * @param Criteria $criteria
     * @param ShopContextInterface $context
     * @return FacetResultInterface|null
     */
    public function generatePartialFacet(
        FacetInterface $facet,
        Criteria $reverted,
        Criteria $criteria,
        ShopContextInterface $context
    ) {
        $properties = $this->getProperties($context, $reverted);

        if (null === $properties) {
            return null;
        }
        $actives = $this->getFilteredValues($criteria);
        return $this->createCollectionResult($facet, $properties, $actives);
    }

    /**
     * @param QueryBuilder $query
     */
    private function rebuildQuery(QueryBuilder $query)
    {
        $query->resetQueryPart('orderBy');
        $query->resetQueryPart('groupBy');
        $query->innerJoin('product', 's_filter_articles', 'productProperty', 'productProperty.articleID = product.id');
        $query->groupBy('productProperty.valueID');
        $query->select('productProperty.valueID as id');
    }

    /**
     * @param Criteria $criteria
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
     * @param Facet\PropertyFacet $facet
     * @param Struct\Property\Set[] $sets
     * @param int[] $actives
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

    /**
     * @param Struct\ShopContextInterface $context
     * @param Criteria $queryCriteria
     * @return Struct\Property\Set[]
     */
    protected function getProperties(Struct\ShopContextInterface $context, Criteria $queryCriteria)
    {
        $query = $this->queryBuilderFactory->createQuery($queryCriteria, $context);
        $this->rebuildQuery($query);

        /**@var $statement \Doctrine\DBAL\Driver\ResultStatement */
        $statement = $query->execute();

        /**@var $facet Facet\PropertyFacet */
        $valueIds = $statement->fetchAll(\PDO::FETCH_COLUMN);

        if (empty($valueIds)) {
            return null;
        }

        $properties = $this->propertyGateway->getList(
            $valueIds,
            $context
        );

        return $properties;
    }
}
