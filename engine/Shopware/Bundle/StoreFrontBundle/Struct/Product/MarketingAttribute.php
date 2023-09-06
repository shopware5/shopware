<?php
/**
 * Shopware 5
 * Copyright (c) shopware AG
 *
 * According to our licensing model, this program can be used
 * under the terms of the GNU Affero General Public License, version 3.
 *
 * The texts of the GNU Affero General Public License with an additional
 * permission can be found at and in the LICENSE file you have received
 * along with this program.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 * See the GNU Affero General Public License for more details.
 *
 * "Shopware" is a registered trademark of shopware AG.
 * The licensing of the program under the AGPLv3 does not imply a
 * trademark license. Therefore, any rights, title and interest in
 * our trademarks remain entirely with the shopware AG.
 */

namespace Shopware\Bundle\StoreFrontBundle\Struct\Product;

use Shopware\Bundle\StoreFrontBundle\Struct\Attribute;
use Shopware\Components\ObjectJsonSerializeTraitDeprecated;

class MarketingAttribute extends Attribute
{
    use ObjectJsonSerializeTraitDeprecated;

    /**
     * @var bool
     */
    protected $isNew = false;

    /**
     * @var bool
     */
    protected $isTopSeller = false;

    /**
     * @var bool
     */
    protected $comingSoon = false;

    /**
     * @param bool $comingSoon
     */
    public function setComingSoon($comingSoon)
    {
        $this->comingSoon = $comingSoon;
    }

    /**
     * @param bool $isNew
     */
    public function setIsNew($isNew)
    {
        $this->isNew = $isNew;
    }

    /**
     * @param bool $isTopSeller
     */
    public function setIsTopSeller($isTopSeller)
    {
        $this->isTopSeller = $isTopSeller;
    }

    /**
     * @return bool
     */
    public function comingSoon()
    {
        return $this->comingSoon;
    }

    /**
     * @return bool
     */
    public function isNew()
    {
        return $this->isNew;
    }

    /**
     * @return bool
     */
    public function isTopSeller()
    {
        return $this->isTopSeller;
    }
}
