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

namespace Shopware\Bundle\StoreFrontBundle\Listing;

use Shopware\Bundle\StoreFrontBundle\Context\ShopContextInterface;

class ListingFacetService implements ListingFacetServiceInterface
{
    /**
     * @var \Shopware\Bundle\StoreFrontBundle\Listing\ListingFacetGateway
     */
    private $gateway;

    /**
     * @param ListingFacetGateway $gateway
     */
    public function __construct(ListingFacetGateway $gateway)
    {
        $this->gateway = $gateway;
    }

    /**
     * {@inheritdoc}
     */
    public function getList(array $ids, ShopContextInterface $context)
    {
        return $this->gateway->getList($ids, $context->getTranslationContext());
    }

    /**
     * {@inheritdoc}
     */
    public function getFacetsOfCategories(array $categoryIds, ShopContextInterface $context)
    {
        return $this->gateway->getFacetsOfCategories($categoryIds, $context->getTranslationContext());
    }

    /**
     * {@inheritdoc}
     */
    public function getAllCategoryFacets(ShopContextInterface $context)
    {
        return $this->gateway->getAllCategoryFacets($context->getTranslationContext());
    }
}
