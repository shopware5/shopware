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

namespace Shopware\Bundle\StoreFrontBundle\Common;

use Shopware\Bundle\StoreFrontBundle\Struct\ListProduct;

/**
 * @deprecated in 5.6, will be removed in 5.7 without replacement
 */
class StructHelper
{
    public function assignProductData(ListProduct $listProduct, ListProduct $product)
    {
        trigger_error(sprintf('%s:%s is deprecated since Shopware 5.6 and will be removed with 5.7. Will be removed without replacement.', __CLASS__, __METHOD__), E_USER_DEPRECATED);

        $product->setShippingFree($listProduct->isShippingFree());
        $product->setMainVariantId($listProduct->getMainVariantId());
        $product->setAllowsNotification($listProduct->allowsNotification());
        $product->setHighlight($listProduct->highlight());
        $product->setUnit($listProduct->getUnit());
        $product->setTax($listProduct->getTax());
        $product->setPrices($listProduct->getPrices());
        $product->setManufacturer($listProduct->getManufacturer());
        $product->setCover($listProduct->getCover());
        $product->setCheapestPrice($listProduct->getCheapestPrice());
        $product->setName($listProduct->getName());
        $product->setAdditional($listProduct->getAdditional());
        $product->setCloseouts($listProduct->isCloseouts());
        $product->setEan($listProduct->getEan());
        $product->setHeight($listProduct->getHeight());
        $product->setKeywords($listProduct->getKeywords());
        $product->setLength($listProduct->getLength());
        $product->setLongDescription($listProduct->getLongDescription());
        $product->setMinStock($listProduct->getMinStock());
        $product->setReleaseDate($listProduct->getReleaseDate());
        $product->setShippingTime($listProduct->getShippingTime());
        $product->setShortDescription($listProduct->getShortDescription());
        $product->setStock($listProduct->getStock());
        $product->setWeight($listProduct->getWeight());
        $product->setWidth($listProduct->getWidth());
        $product->setPriceGroup($listProduct->getPriceGroup());
        $product->setCreatedAt($listProduct->getCreatedAt());
        $product->setUpdatedAt($listProduct->getUpdatedAt());
        $product->setPriceRules($listProduct->getPriceRules());
        $product->setCheapestPriceRule($listProduct->getCheapestPriceRule());
        $product->setManufacturerNumber($listProduct->getManufacturerNumber());
        $product->setMetaTitle($listProduct->getMetaTitle());
        $product->setTemplate($listProduct->getTemplate());
        $product->setHasConfigurator($listProduct->hasConfigurator());
        $product->setSales($listProduct->getSales());
        $product->setHasEsd($listProduct->hasEsd());
        $product->setEsd($listProduct->getEsd());
        $product->setIsPriceGroupActive($listProduct->isPriceGroupActive());
        $product->setBlockedCustomerGroupIds($listProduct->getBlockedCustomerGroupIds());
        $product->setVoteAverage($listProduct->getVoteAverage());
        $product->setHasAvailableVariant($listProduct->hasAvailableVariant());
        $product->setCheapestUnitPrice($listProduct->getCheapestUnitPrice());
        $product->setFallbackPriceCount($listProduct->getFallbackPriceCount());
        $product->setCustomerPriceCount($listProduct->getCustomerPriceCount());

        foreach ($listProduct->getAttributes() as $name => $attribute) {
            $product->addAttribute($name, $attribute);
        }

        foreach ($listProduct->getStates() as $state) {
            $product->addState($state);
        }
    }
}
