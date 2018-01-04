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

use Shopware\Bundle\SearchBundle\Criteria;
use Shopware\Bundle\SearchBundle\Facet;
use Shopware\Bundle\SearchBundle\FacetInterface;
use Shopware\Bundle\SearchBundle\FacetResultInterface;
use Shopware\Bundle\SearchBundleDBAL\ListingPriceTable;
use Shopware\Bundle\SearchBundleDBAL\PartialFacetHandlerInterface;
use Shopware\Bundle\SearchBundleDBAL\QueryBuilderFactoryInterface;
use Shopware\Bundle\StoreFrontBundle\Struct\ShopContextInterface;
use Shopware\Components\QueryAliasMapper;

/**
 * @category  Shopware
 *
 * @copyright Copyright (c) shopware AG (http://www.shopware.de)
 */
class VariantPriceFacetHandler implements PartialFacetHandlerInterface
{
    /**
     * @var PartialFacetHandlerInterface
     */
    private $priceFacetHandler;

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
     * @var ListingPriceTable
     */
    private $listingPriceTable;

    /**
     * @param PartialFacetHandlerInterface         $priceFacetHandler
     * @param ListingPriceTable                    $listingPriceTable
     * @param QueryBuilderFactoryInterface         $queryBuilderFactory
     * @param \Shopware_Components_Snippet_Manager $snippetManager
     * @param QueryAliasMapper                     $queryAliasMapper
     *
     * @internal param PriceHelperInterface $priceHelper
     */
    public function __construct(
        PartialFacetHandlerInterface $priceFacetHandler,
        ListingPriceTable $listingPriceTable,
        QueryBuilderFactoryInterface $queryBuilderFactory,
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
        $this->priceFacetHandler = $priceFacetHandler;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsFacet(FacetInterface $facet)
    {
        return $facet instanceof Facet\PriceFacet;
    }

    /**
     * @param FacetInterface       $facet
     * @param Criteria             $reverted
     * @param Criteria             $criteria
     * @param ShopContextInterface $context
     *
     * @return FacetResultInterface
     */
    public function generatePartialFacet(
        FacetInterface $facet,
        Criteria $reverted,
        Criteria $criteria,
        ShopContextInterface $context
    ) {
        // TODO: Checken ob Varianten angezeigt werden und dann die Preise anders ermitteln
        return $this->priceFacetHandler->generatePartialFacet($facet, $reverted, $criteria, $context);
    }
}
