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
use Shopware\Bundle\SearchBundle\FacetResult\RangeFacetResult;
use Shopware\Bundle\SearchBundle\FacetResultInterface;
use Shopware\Bundle\SearchBundleDBAL\ConditionHandler\PriceConditionHandler;
use Shopware\Bundle\SearchBundleDBAL\ListingPriceTable;
use Shopware\Bundle\SearchBundleDBAL\PartialFacetHandlerInterface;
use Shopware\Bundle\SearchBundleDBAL\QueryBuilderFactory;
use Shopware\Bundle\SearchBundle\FacetInterface;
use Shopware\Bundle\SearchBundle\Criteria;
use Shopware\Bundle\SearchBundle\Facet;
use Shopware\Bundle\StoreFrontBundle\Struct\ShopContextInterface;
use Shopware\Components\QueryAliasMapper;

/**
 * @category  Shopware
 * @package   Shopware\Bundle\SearchBundleDBAL\FacetHandler
 * @copyright Copyright (c) shopware AG (http://www.shopware.de)
 */
class PriceFacetHandler implements PartialFacetHandlerInterface
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
     * @var string
     */
    private $minFieldName;

    /**
     * @var string
     */
    private $maxFieldName;

    /**
     * @var ListingPriceTable
     */
    private $listingPriceTable;

    /**
     * @param ListingPriceTable $listingPriceTable
     * @param QueryBuilderFactory $queryBuilderFactory
     * @param \Shopware_Components_Snippet_Manager $snippetManager
     * @param QueryAliasMapper $queryAliasMapper
     * @internal param PriceHelperInterface $priceHelper
     */
    public function __construct(
        ListingPriceTable $listingPriceTable,
        QueryBuilderFactory $queryBuilderFactory,
        \Shopware_Components_Snippet_Manager $snippetManager,
        QueryAliasMapper $queryAliasMapper
    ) {
        $this->queryBuilderFactory = $queryBuilderFactory;
        $this->snippetNamespace = $snippetManager->getNamespace('frontend/listing/facet_labels');
        $this->listingPriceTable = $listingPriceTable;

        if (!$this->minFieldName = $queryAliasMapper->getShortAlias('priceMin')) {
            $this->minFieldName = 'priceMin';
        }

        if (!$this->maxFieldName = $queryAliasMapper->getShortAlias('priceMax')) {
            $this->maxFieldName = 'priceMax';
        }
    }

    /**
     * {@inheritdoc}
     */
    public function supportsFacet(FacetInterface $facet)
    {
        return ($facet instanceof Facet\PriceFacet);
    }

    /**
     * @param FacetInterface $facet
     * @param Criteria $reverted
     * @param Criteria $criteria
     * @param ShopContextInterface $context
     * @return FacetResultInterface
     */
    public function generatePartialFacet(
        FacetInterface $facet,
        Criteria $reverted,
        Criteria $criteria,
        ShopContextInterface $context
    ) {
        $query = $this->queryBuilderFactory->createQuery($reverted, $context);
        $query->resetQueryPart('orderBy');
        $query->resetQueryPart('groupBy');

        if (!$query->hasState(PriceConditionHandler::LISTING_PRICE_JOINED)) {
            $table = $this->listingPriceTable->get($context);
            $query->innerJoin('product', '(' . $table->getSQL() . ')', 'listing_price', 'listing_price.articleID = product.id');
            foreach ($table->getParameters() as $key => $value) {
                $query->setParameter($key, $value);
            }
            $query->addState(PriceConditionHandler::LISTING_PRICE_JOINED);
        }

        $query->select('MIN(listing_price.cheapest_price)');

        /**@var $statement \Doctrine\DBAL\Driver\ResultStatement */
        $statement = $query->execute();

        $min = $statement->fetch(\PDO::FETCH_COLUMN);

        $query->groupBy('product.id')
            ->orderBy('listing_price.cheapest_price', 'DESC')
            ->setFirstResult(0)
            ->setMaxResults(1);

        /**@var $statement \Doctrine\DBAL\Driver\ResultStatement */
        $statement = $query->execute();

        $max = $statement->fetch(\PDO::FETCH_COLUMN);

        $activeMin = $min;
        $activeMax = $max;

        /**@var $condition PriceCondition */
        if ($condition = $criteria->getCondition($facet->getName())) {
            $activeMin = $condition->getMinPrice();
            $activeMax = $condition->getMaxPrice();
        }

        if ($min == $max) {
            return null;
        }

        return new RangeFacetResult(
            $facet->getName(),
            $criteria->hasCondition($facet->getName()),
            $this->snippetNamespace->get($facet->getName(), 'Price'),
            (float) $min,
            (float) $max,
            (float) $activeMin,
            (float) $activeMax,
            $this->minFieldName,
            $this->maxFieldName,
            [],
            'frontend/listing/filter/facet-currency-range.tpl'
        );
    }
}
