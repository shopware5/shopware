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

use Shopware\Bundle\SearchBundle\Condition\PriceCondition;
use Shopware\Bundle\SearchBundle\Condition\VariantCondition;
use Shopware\Bundle\SearchBundle\Criteria;
use Shopware\Bundle\SearchBundle\Facet;
use Shopware\Bundle\SearchBundle\FacetInterface;
use Shopware\Bundle\SearchBundle\FacetResult\RangeFacetResult;
use Shopware\Bundle\SearchBundle\FacetResultInterface;
use Shopware\Bundle\SearchBundleDBAL\ListingPriceSwitcher;
use Shopware\Bundle\SearchBundleDBAL\PartialFacetHandlerInterface;
use Shopware\Bundle\SearchBundleDBAL\QueryBuilderFactoryInterface;
use Shopware\Bundle\StoreFrontBundle\Struct\ShopContextInterface;
use Shopware\Components\QueryAliasMapper;

class PriceFacetHandler implements PartialFacetHandlerInterface
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
     * @var string
     */
    private $minFieldName;

    /**
     * @var string
     */
    private $maxFieldName;

    /**
     * @var ListingPriceSwitcher
     */
    private $listingPriceSwitcher;

    public function __construct(
        QueryBuilderFactoryInterface $queryBuilderFactory,
        \Shopware_Components_Snippet_Manager $snippetManager,
        QueryAliasMapper $queryAliasMapper,
        ListingPriceSwitcher $listingPriceSwitcher
    ) {
        $this->queryBuilderFactory = $queryBuilderFactory;
        $this->snippetNamespace = $snippetManager->getNamespace('frontend/listing/facet_labels');

        if (!$this->minFieldName = $queryAliasMapper->getShortAlias('priceMin')) {
            $this->minFieldName = 'priceMin';
        }

        if (!$this->maxFieldName = $queryAliasMapper->getShortAlias('priceMax')) {
            $this->maxFieldName = 'priceMax';
        }
        $this->listingPriceSwitcher = $listingPriceSwitcher;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsFacet(FacetInterface $facet)
    {
        return $facet instanceof Facet\PriceFacet;
    }

    /**
     * @return FacetResultInterface|null
     */
    public function generatePartialFacet(
        FacetInterface $facet,
        Criteria $reverted,
        Criteria $criteria,
        ShopContextInterface $context
    ) {
        $query = $this->buildQuery($reverted, $criteria, $context);

        $query->orderBy('listing_price.cheapest_price', 'ASC');

        /** @var \Doctrine\DBAL\Driver\ResultStatement $statement */
        $statement = $query->execute();

        $min = $statement->fetch(\PDO::FETCH_COLUMN);

        $query = $this->buildQuery($reverted, $criteria, $context);

        $query->orderBy('listing_price.cheapest_price', 'DESC');

        /** @var \Doctrine\DBAL\Driver\ResultStatement $statement */
        $statement = $query->execute();

        $max = $statement->fetch(\PDO::FETCH_COLUMN);

        $activeMin = $min;
        $activeMax = $max;

        /** @var PriceCondition $condition */
        if ($condition = $criteria->getCondition($facet->getName())) {
            $activeMin = $condition->getMinPrice();
            $activeMax = $condition->getMaxPrice();
        }

        if ($min == $max) {
            return null;
        }

        /** @var Facet\PriceFacet $facet */
        if (!empty($facet->getLabel())) {
            $label = $facet->getLabel();
        } else {
            $label = $this->snippetNamespace->get($facet->getName(), 'Price');
        }

        return new RangeFacetResult(
            $facet->getName(),
            $criteria->hasCondition($facet->getName()),
            $label,
            (float) $min,
            (float) $max,
            (float) $activeMin,
            (float) $activeMax,
            $this->minFieldName,
            $this->maxFieldName,
            [],
            null,
            2,
            'frontend/listing/filter/facet-currency-range.tpl'
        );
    }

    /**
     * @return \Shopware\Bundle\SearchBundleDBAL\QueryBuilder
     */
    private function buildQuery(Criteria $reverted, Criteria $criteria, ShopContextInterface $context)
    {
        $tmp = clone $reverted;
        $conditions = $criteria->getConditionsByClass(VariantCondition::class);
        foreach ($conditions as $condition) {
            $tmp->addBaseCondition($condition);
        }

        $query = $this->queryBuilderFactory->createQuery($tmp, $context);

        $this->listingPriceSwitcher->joinPrice($query, $criteria, $context);
        $query->select('listing_price.cheapest_price');
        $query->setFirstResult(0);
        $query->setMaxResults(1);
        $query->addGroupBy('product.id');

        return $query;
    }
}
