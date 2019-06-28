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

use Shopware\Bundle\SearchBundle\Condition\HeightCondition;
use Shopware\Bundle\SearchBundle\Condition\LengthCondition;
use Shopware\Bundle\SearchBundle\Condition\WeightCondition;
use Shopware\Bundle\SearchBundle\Condition\WidthCondition;
use Shopware\Bundle\SearchBundle\Criteria;
use Shopware\Bundle\SearchBundle\Facet\HeightFacet;
use Shopware\Bundle\SearchBundle\Facet\LengthFacet;
use Shopware\Bundle\SearchBundle\Facet\WeightFacet;
use Shopware\Bundle\SearchBundle\Facet\WidthFacet;
use Shopware\Bundle\SearchBundle\FacetInterface;
use Shopware\Bundle\SearchBundle\FacetResult\RangeFacetResult;
use Shopware\Bundle\SearchBundleDBAL\PartialFacetHandlerInterface;
use Shopware\Bundle\SearchBundleDBAL\QueryBuilderFactoryInterface;
use Shopware\Bundle\SearchBundleDBAL\VariantHelperInterface;
use Shopware\Bundle\StoreFrontBundle\Struct\Attribute;
use Shopware\Bundle\StoreFrontBundle\Struct\ShopContextInterface;

class ProductDimensionsFacetHandler implements PartialFacetHandlerInterface
{
    /**
     * @var QueryBuilderFactoryInterface
     */
    private $queryBuilderFactory;

    /**
     * @var \Enlight_Components_Snippet_Namespace
     */
    private $snippetNamespace;

    /**
     * @var VariantHelperInterface
     */
    private $variantHelper;

    public function __construct(
        QueryBuilderFactoryInterface $queryBuilderFactory,
        \Shopware_Components_Snippet_Manager $snippetManager,
        VariantHelperInterface $variantHelper
    ) {
        $this->queryBuilderFactory = $queryBuilderFactory;
        $this->snippetNamespace = $snippetManager->getNamespace('frontend/listing/facet_labels');
        $this->variantHelper = $variantHelper;
    }

    public function generatePartialFacet(
        FacetInterface $facet,
        Criteria $reverted,
        Criteria $criteria,
        ShopContextInterface $context
    ) {
        if ($criteria->hasAttribute('product_dimensions_handled')) {
            return null;
        }

        $query = $this->queryBuilderFactory->createQuery($reverted, $context);
        $query->resetQueryPart('orderBy');
        $query->resetQueryPart('groupBy');

        $this->variantHelper->joinVariants($query);

        $query->select([
            'MIN(allVariants.height) as minHeight',
            'MAX(allVariants.height) as maxHeight',
            'MIN(allVariants.weight) as minWeight',
            'MAX(allVariants.weight) as maxWeight',
            'MIN(allVariants.width) as minWidth',
            'MAX(allVariants.width) as maxWidth',
            'MIN(allVariants.length) as minLength',
            'MAX(allVariants.length) as maxLength',
        ]);

        $query->setMaxResults(1);

        $stats = $query->execute()->fetch(\PDO::FETCH_ASSOC);

        $facets = ['width', 'height', 'length', 'weight'];
        $results = [];
        foreach ($criteria->getFacets() as $criteriaFacet) {
            if (!in_array($criteriaFacet->getName(), $facets, true)) {
                continue;
            }
            $facetResult = $this->createRangeFacet($criteriaFacet, $stats, $criteria);
            if (!$facetResult) {
                continue;
            }

            $results[] = $facetResult;
        }
        $criteria->addAttribute('product_dimensions_handled', new Attribute());

        return $results;
    }

    public function supportsFacet(FacetInterface $facet)
    {
        return $facet instanceof WeightFacet
            || $facet instanceof WidthFacet
            || $facet instanceof LengthFacet
            || $facet instanceof HeightFacet
        ;
    }

    /**
     * @param WeightFacet|WidthFacet|LengthFacet|HeightFacet|FacetInterface $facet
     *
     * @return RangeFacetResult|null
     */
    private function createRangeFacet(FacetInterface $facet, array $stats, Criteria $criteria)
    {
        $name = $facet->getName();

        $minField = 'min' . ucfirst($name);
        $maxField = 'max' . ucfirst($name);

        $min = (float) $stats[$minField];
        $max = (float) $stats[$maxField];

        $min = round($min, $facet->getDigits());
        $max = round($max, $facet->getDigits());

        $activeMin = $min;
        $activeMax = $max;

        /** @var WeightCondition|WidthCondition|LengthCondition|HeightCondition $condition */
        if ($condition = $criteria->getCondition($name)) {
            $method = 'get' . ucfirst($minField);
            $activeMin = $condition->$method();

            $method = 'get' . ucfirst($maxField);
            $activeMax = $condition->$method();
        }

        if ($min == $max) {
            return null;
        }

        $activeMin = round($activeMin, $facet->getDigits());
        $activeMax = round($activeMax, $facet->getDigits());

        $label = $facet->getLabel();

        return new RangeFacetResult(
            $name,
            $criteria->hasCondition($name),
            $label,
            (float) $min,
            (float) $max,
            (float) $activeMin,
            (float) $activeMax,
            $minField,
            $maxField,
            [],
            $facet->getSuffix(),
            $facet->getDigits(),
            'frontend/listing/filter/facet-range.tpl'
        );
    }
}
