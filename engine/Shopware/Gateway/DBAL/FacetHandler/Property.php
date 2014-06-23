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

namespace Shopware\Gateway\DBAL\FacetHandler;

use Shopware\Components\Model\DBAL\QueryBuilder;
use Shopware\Components\Model\ModelManager;
use Shopware\Gateway\Search\Criteria;
use Shopware\Gateway\Search\Facet;
use Shopware\Struct\Context;
use Shopware\Struct\CoreAttribute;

/**
 * @package Shopware\Gateway\DBAL\FacetHandler
 */
class Property implements DBAL
{
    /**
     * @var \Shopware\Service\Property
     */
    private $propertyGateway;

    /**
     * @param \Shopware\Gateway\Property $propertyGateway
     */
    function __construct(
        \Shopware\Gateway\Property $propertyGateway
    ) {
        $this->propertyGateway = $propertyGateway;
    }

    /**
     * @param Facet $facet
     * @param QueryBuilder $query
     * @param \Shopware\Gateway\Search\Criteria $criteria
     * @param Context $context
     * @return \Shopware\Gateway\Search\Facet\Category
     */
    public function generateFacet(
        Facet $facet,
        QueryBuilder $query,
        Criteria $criteria,
        Context $context
    ) {
        $this->rebuildQuery($query);

        /**@var $statement \Doctrine\DBAL\Driver\ResultStatement */
        $statement = $query->execute();

        /**@var $facet Facet\Property*/
        $valueIds = $statement->fetchAll(\PDO::FETCH_KEY_PAIR);

        $properties = $this->propertyGateway->getList(
            array_keys($valueIds),
            $context
        );

        $activeValues = array();
        /**@var $condition \Shopware\Gateway\Search\Condition\Property*/
        if ($condition = $criteria->getCondition('property')) {
            $activeValues = $condition->getValueIds();
        }

        $this->addAttributes($properties, $valueIds, $activeValues);

        $facet->setProperties($properties);
        $facet->setFiltered(!empty($activeValues));

        return $facet;
    }

    /**
     * @param \Shopware\Struct\Property\Set[] $properties
     * @param array $valueIds
     * @param array $activeValues
     */
    private function addAttributes(array $properties, array $valueIds, array $activeValues)
    {
        $baseAttribute = new CoreAttribute();
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

    public function supportsFacet(Facet $facet)
    {
        return ($facet instanceof Facet\Property);
    }
}
