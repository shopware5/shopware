<?php
/**
 * Shopware 4
 * Copyright © shopware AG
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

namespace Shopware\Bundle\SearchBundle\DBAL\FacetHandler;

use Shopware\Bundle\SearchBundle\FacetInterface;
use Shopware\Components\Model\DBAL\QueryBuilder;
use Shopware\Bundle\SearchBundle\Criteria;
use Shopware\Bundle\SearchBundle\Facet;
use Shopware\Bundle\SearchBundle\DBAL\FacetHandlerInterface;
use Shopware\Bundle\StoreFrontBundle\Struct;
use Shopware\Bundle\StoreFrontBundle\Gateway\PropertyGatewayInterface;

/**
 * @package Shopware\Bundle\SearchBundle\Platform\DBAL\FacetHandler
 */
class PropertyFacetHandler implements FacetHandlerInterface
{
    /**
     * @var PropertyGatewayInterface
     */
    private $propertyGateway;

    /**
     * @param PropertyGatewayInterface $propertyGateway
     */
    function __construct(PropertyGatewayInterface $propertyGateway)
    {
        $this->propertyGateway = $propertyGateway;
    }

    public function generateFacet(
        FacetInterface $facet,
        QueryBuilder $query,
        Criteria $criteria,
        Struct\Context $context
    ) {
        $this->rebuildQuery($query);

        /**@var $statement \Doctrine\DBAL\Driver\ResultStatement */
        $statement = $query->execute();

        /**@var $facet Facet\PropertyFacet*/
        $valueIds = $statement->fetchAll(\PDO::FETCH_KEY_PAIR);

        $properties = $this->propertyGateway->getList(
            array_keys($valueIds),
            $context
        );

        $activeValues = array();
        /**@var $condition \Shopware\Bundle\SearchBundle\Condition\PropertyCondition*/
        if ($condition = $criteria->getCondition('property')) {
            $activeValues = $condition->getValueIds();
        }

        $this->addAttributes($properties, $valueIds, $activeValues);

        $facet->setProperties($properties);
        $facet->setFiltered(!empty($activeValues));

        return $facet;
    }

    /**
     * @param Struct\Property\Set[] $properties
     * @param array $valueIds
     * @param array $activeValues
     */
    private function addAttributes(array $properties, array $valueIds, array $activeValues)
    {
        $baseAttribute = new Struct\CoreAttribute();
        $baseAttribute->set('active', false);

        foreach ($properties as $set) {
            $setAttribute = clone $baseAttribute;

            foreach ($set->getGroups() as $group) {
                $groupAttribute = clone $baseAttribute;

                foreach ($group->getOptions() as $option) {
                    $count = $valueIds[$option->getId()];

                    $attribute = clone $baseAttribute;
                    $attribute->set('total', (int) $count);

                    $active = in_array($option->getId(), $activeValues);

                    if ($active) {
                        $attribute->set('active', true);
                        $setAttribute->set('active', true);
                        $groupAttribute->set('active', true);
                    }

                    $option->addAttribute('facet', $attribute);
                }
                $group->addAttribute('facet', $groupAttribute);
            }
            $set->addAttribute('facet', $setAttribute);
        }
    }

    private function rebuildQuery(QueryBuilder $query)
    {
        $query->resetQueryPart('orderBy');

        $query->resetQueryPart('groupBy');

        $query->select(
            array(
                'productProperties.valueID as id',
                'COUNT(DISTINCT products.id) as total'
            )
        );

        $query->innerJoin(
            'products',
            's_filter',
            'propertySet',
            'propertySet.id = products.filtergroupID'
        );

        $query->innerJoin(
            'products',
            's_filter_articles',
            'productProperties',
            'productProperties.articleID = products.id'
        );

        $query->innerJoin(
            'productProperties',
            's_filter_values',
            'propertyOptions',
            'propertyOptions.id = productProperties.valueID'
        );

        $query->innerJoin(
            'propertyOptions',
            's_filter_options',
            'propertyGroups',
            'propertyGroups.id = propertyOptions.optionID
             AND propertyGroups.filterable = 1'
        );

        $query->innerJoin(
            'propertyOptions',
            's_filter_relations',
            'propertyRelations',
            'propertyRelations.optionID = propertyGroups.id'
        );

        $query->groupBy('productProperties.valueID');
    }

    public function supportsFacet(FacetInterface $facet)
    {
        return ($facet instanceof Facet\PropertyFacet);
    }
}
