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
use Shopware\Bundle\SearchBundleDBAL\QueryBuilderFactory;
use Shopware\Bundle\SearchBundle\FacetInterface;
use Shopware\Bundle\SearchBundle\Criteria;
use Shopware\Bundle\SearchBundleDBAL\PriceHelper;
use Shopware\Bundle\SearchBundleDBAL\FacetHandlerInterface;
use Shopware\Bundle\SearchBundle\Facet;
use Shopware\Bundle\StoreFrontBundle\Struct\ShopContextInterface;
use Shopware\Components\QueryAliasMapper;

/**
 * @category  Shopware
 * @package   Shopware\Bundle\SearchBundleDBAL\FacetHandler
 * @copyright Copyright (c) shopware AG (http://www.shopware.de)
 */
class PriceFacetHandler implements FacetHandlerInterface
{
    /**
     * @var PriceHelper
     */
    private $priceHelper;

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
     * @param PriceHelper $priceHelper
     * @param QueryBuilderFactory $queryBuilderFactory
     * @param \Shopware_Components_Snippet_Manager $snippetManager
     * @param QueryAliasMapper $queryAliasMapper
     */
    public function __construct(
        PriceHelper $priceHelper,
        QueryBuilderFactory $queryBuilderFactory,
        \Shopware_Components_Snippet_Manager $snippetManager,
        QueryAliasMapper $queryAliasMapper
    ) {
        $this->priceHelper = $priceHelper;
        $this->queryBuilderFactory = $queryBuilderFactory;
        $this->snippetNamespace = $snippetManager->getNamespace('frontend/listing/facet_labels');

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
     * @param FacetInterface|Facet\PriceFacet $facet
     * @param Criteria $criteria
     * @param ShopContextInterface $context
     * @return RangeFacetResult
     */
    public function generateFacet(
        FacetInterface $facet,
        Criteria $criteria,
        ShopContextInterface $context
    ) {
        $queryCriteria = clone $criteria;
        $queryCriteria->resetConditions();
        $queryCriteria->resetSorting();

        $query = $this->queryBuilderFactory->createQuery(
            $queryCriteria,
            $context
        );

        $query->resetQueryPart('orderBy');
        $query->resetQueryPart('groupBy');

        $selection = $this->priceHelper->getSelection($context);
        $this->priceHelper->joinPrices($query, $context);

        $query->select('MIN('. $selection .') as cheapest_price');

        /**@var $statement \Doctrine\DBAL\Driver\ResultStatement */
        $statement = $query->execute();

        $min = $statement->fetch(\PDO::FETCH_COLUMN);

        $query->groupBy('product.id')
            ->orderBy('cheapest_price', 'DESC')
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
