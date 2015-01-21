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

use Shopware\Bundle\SearchBundle\Condition\ProductAttributeCondition;
use Shopware\Bundle\SearchBundle\Criteria;
use Shopware\Bundle\SearchBundle\FacetResult\BooleanFacetResult;
use Shopware\Bundle\SearchBundle\FacetResult\ValueListFacetResult;
use Shopware\Bundle\SearchBundle\FacetResult\ValueListItem;
use Shopware\Bundle\SearchBundleDBAL\FacetHandlerInterface;
use Shopware\Bundle\SearchBundleDBAL\QueryBuilderFactory;
use Shopware\Bundle\SearchBundle\Facet\ProductAttributeFacet;
use Shopware\Bundle\SearchBundle\FacetInterface;
use Shopware\Bundle\StoreFrontBundle\Struct;

/**
 * @category  Shopware
 * @package   Shopware\Bundle\SearchBundleDBAL\FacetHandler
 * @copyright Copyright (c) shopware AG (http://www.shopware.de)
 */
class ProductAttributeFacetHandler implements FacetHandlerInterface
{
    /**
     * @var QueryBuilderFactory
     */
    private $queryBuilderFactory;

    /**
     * @var \Enlight_Components_Snippet_Namespace
     */
    private $snippetNamespace;

    /**
     * @param QueryBuilderFactory $queryBuilderFactory
     * @param \Shopware_Components_Snippet_Manager $snippetManager
     */
    public function __construct(
        QueryBuilderFactory $queryBuilderFactory,
        \Shopware_Components_Snippet_Manager $snippetManager
    ) {
        $this->queryBuilderFactory = $queryBuilderFactory;
        $this->snippetNamespace = $snippetManager->getNamespace('frontend/listing/facet_labels');
    }

    /**
     * Generates the facet data for the passed query, criteria and context object.
     *
     * @param FacetInterface|ProductAttributeFacet $facet
     * @param Criteria $criteria
     * @param Struct\ShopContextInterface $context
     * @return BooleanFacetResult|ValueListFacetResult
     */
    public function generateFacet(
        FacetInterface $facet,
        Criteria $criteria,
        Struct\ShopContextInterface $context
    ) {
        $queryCriteria = clone $criteria;
        $queryCriteria->resetConditions();
        $queryCriteria->resetSorting();

        $query = $this->queryBuilderFactory->createQuery($queryCriteria, $context);

        $query->resetQueryPart('orderBy');
        $query->resetQueryPart('groupBy');

        $query->select(
            [
            'COUNT(DISTINCT product.id) as total'
            ]
        );

        switch ($facet->getMode()) {
            case (ProductAttributeFacet::MODE_EMPTY):
                $query->andWhere(
                    "(productAttribute." . $facet->getField() . " IS NULL
                     OR productAttribute." . $facet->getField() . " = '')"
                );
                break;

            case (ProductAttributeFacet::MODE_NOT_EMPTY):
                $query->andWhere(
                    "(productAttribute." . $facet->getField() . " IS NOT NULL
                     OR productAttribute." . $facet->getField() . " != '')"
                );
                break;

            default:
                $query->addSelect('productAttribute.' . $facet->getField())
                    ->orderBy('productAttribute.' . $facet->getField())
                    ->groupBy('productAttribute.' . $facet->getField());

                break;
        }

        /**@var $statement \Doctrine\DBAL\Driver\ResultStatement */
        $statement = $query->execute();
        $result = $statement->fetchAll(\PDO::FETCH_ASSOC);

        if (!$result) {
            return null;
        }

        if ($facet->getMode() == ProductAttributeFacet::MODE_VALUES) {
            return $this->createValueListResult($facet, $criteria, $result);
        } else {
            return new BooleanFacetResult(
                $facet->getName(),
                $facet->getName(),
                $criteria->hasCondition($facet->getName()),
                $this->snippetNamespace->get($facet->getName(), 'Attribute')
            );
        }
    }

    /**
     * @param ProductAttributeFacet $facet
     * @param Criteria $criteria
     * @param $result
     * @return ValueListFacetResult
     */
    private function createValueListResult(ProductAttributeFacet $facet, Criteria $criteria, $result)
    {
        $items = [];
        $actives = null;

        /**@var $condition ProductAttributeCondition*/
        if ($condition = $criteria->getCondition($facet->getName())) {
            $actives = $condition->getValue();
        }

        foreach ($result as $row) {
            $value = $row[$facet->getField()];

            if ($condition && $condition->getOperator() == ProductAttributeCondition::OPERATOR_IN && is_array($actives)) {
                $active = in_array($value, $actives);
            } else {
                $active = ($actives == $value);
            }

            $items[] = new ValueListItem($value, $value, $active);
        }

        return new ValueListFacetResult(
            $facet->getName(),
            $criteria->hasCondition($facet->getName()),
            'Attribute',
            $items,
            $facet->getName()
        );
    }

    /**
     * {@inheritdoc}
     */
    public function supportsFacet(FacetInterface $facet)
    {
        return ($facet instanceof ProductAttributeFacet);
    }
}
