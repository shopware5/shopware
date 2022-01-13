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

namespace Shopware\Bundle\StoreFrontBundle\Service\Core;

use DateTime;
use DateTimeInterface;
use Shopware\Bundle\StoreFrontBundle\Service;
use Shopware\Bundle\StoreFrontBundle\Struct\ListProduct;
use Shopware\Bundle\StoreFrontBundle\Struct\Product\MarketingAttribute;
use Shopware_Components_Config;

class MarketingService implements Service\MarketingServiceInterface
{
    private Shopware_Components_Config $config;

    public function __construct(Shopware_Components_Config $config)
    {
        $this->config = $config;
    }

    /**
     * {@inheritdoc}
     */
    public function getProductAttribute(ListProduct $product)
    {
        $attribute = new MarketingAttribute();

        $today = new DateTime();

        $attribute->setIsNew(false);

        if ($product->getCreatedAt() instanceof DateTimeInterface) {
            $diff = $today->diff($product->getCreatedAt());
            $marker = (int) $this->config->get('markAsNew');

            $attribute->setIsNew(
                $diff->days <= $marker || $product->getCreatedAt() > $today
            );
        }

        $attribute->setComingSoon(
            $product->getReleaseDate() && $product->getReleaseDate() > $today
        );

        $attribute->setIsTopSeller(
            $product->getSales() >= $this->config->get('markAsTopSeller')
        );

        return $attribute;
    }
}
