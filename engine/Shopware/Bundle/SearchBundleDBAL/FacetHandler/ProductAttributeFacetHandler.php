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
use Shopware\Bundle\SearchBundle\FacetResult\RadioFacetResult;
use Shopware\Bundle\SearchBundle\FacetResult\RangeFacetResult;
use Shopware\Bundle\SearchBundle\FacetResult\ValueListFacetResult;
use Shopware\Bundle\SearchBundle\FacetResult\ValueListItem;
use Shopware\Bundle\SearchBundleDBAL\FacetHandlerInterface;
use Shopware\Bundle\SearchBundleDBAL\QueryBuilder;
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

        $sqlField = 'productAttribute.' . $facet->getField();
        $query->andWhere($sqlField . ' IS NOT NULL')
            ->andWhere($sqlField . " != ''");

        switch ($facet->getMode()) {
            case (ProductAttributeFacet::MODE_VALUE_LIST_RESULT):
            case (ProductAttributeFacet::MODE_RADIO_LIST_RESULT):
                $result = $this->createValueListFacetResult($query, $facet, $criteria);
                break;

            case (ProductAttributeFacet::MODE_BOOLEAN_RESULT):
                $result = $this->createBooleanFacetResult($query, $facet, $criteria);
                break;

            case (ProductAttributeFacet::MODE_RANGE_RESULT):
                $result = $this->createRangeFacetResult($query, $facet, $criteria);
                break;

            default:
                $result = null;
                break;
        }

        if ($result !== null && $facet->getTemplate()) {
            $result->setTemplate($facet->getTemplate());
        }

        return $result;
    }

    private function createValueListFacetResult(
        QueryBuilder $query,
        ProductAttributeFacet $facet,
        Criteria $criteria
    ) {
        $sqlField = 'productAttribute.' . $facet->getField();

        $query->addSelect($sqlField)
            ->orderBy($sqlField)
            ->groupBy($sqlField);

        /**@var $statement \Doctrine\DBAL\Driver\ResultStatement */
        $statement = $query->execute();
        $result = $statement->fetchAll(\PDO::FETCH_COLUMN);

        if (empty($result)) {
            return null;
        }

        $actives = [];
        /**@var $condition ProductAttributeCondition*/
        if ($condition = $criteria->getCondition($facet->getName())) {
            $actives = $condition->getValue();
        }

        $items = array_map(function($row) use ($actives) {
            return new ValueListItem($row, $row, ($row == $actives));
        }, $result);

        if ($facet->getMode() == ProductAttributeFacet::MODE_RADIO_LIST_RESULT) {
            return new RadioFacetResult(
                $facet->getName(),
                $criteria->hasCondition($facet->getName()),
                $facet->getLabel(),
                $items,
                $facet->getFormFieldName()
            );
        } else {
            return new ValueListFacetResult(
                $facet->getName(),
                $criteria->hasCondition($facet->getName()),
                $facet->getLabel(),
                $items,
                $facet->getFormFieldName()
            );
        }

    }


    private function createRangeFacetResult(
        QueryBuilder $query,
        ProductAttributeFacet $facet,
        Criteria $criteria
    ) {
        $sqlField = 'productAttribute.' . $facet->getField();

        $query->select([
            'MIN('.$sqlField.') as minValues',
            'MAX('.$sqlField.') as maxValues'
        ]);

        /**@var $statement \Doctrine\DBAL\Driver\ResultStatement */
        $statement = $query->execute();
        $result = $statement->fetch(\PDO::FETCH_ASSOC);

        if (empty($result)) {
            return null;
        }

        $activeMin = $result['minValues'];
        $activeMax = $result['maxValues'];

        /**@var $condition ProductAttributeCondition*/
        if ($condition = $criteria->getCondition($facet->getName())) {
            $data = $condition->getValue();
            $activeMin = $data['min'];
            $activeMax = $data['max'];
        }

        return new RangeFacetResult(
            $facet->getName(),
            $criteria->hasCondition($facet->getName()),
            $facet->getLabel(),
            $result['minValues'],
            $result['maxValues'],
            $activeMin,
            $activeMax,
            'min' . $facet->getFormFieldName(),
            'max' . $facet->getFormFieldName()
        );

    }


    private function createBooleanFacetResult(
        QueryBuilder $query,
        ProductAttributeFacet $facet,
        Criteria $criteria
    ) {
        $sqlField = 'productAttribute.' . $facet->getField();

        $query->select('COUNT('.$sqlField.')');

        /**@var $statement \Doctrine\DBAL\Driver\ResultStatement */
        $statement = $query->execute();
        $result = $statement->fetch(\PDO::FETCH_COLUMN);

        if (empty($result)) {
            return null;
        }

        return new BooleanFacetResult(
            $facet->getName(),
            $facet->getFormFieldName(),
            $criteria->hasCondition($facet->getName()),
            $facet->getLabel()
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
