<?php
/**
 * Shopware 4
 * Copyright © shopware AG
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

namespace Shopware\Struct\Product;

use Shopware\Struct\Attribute;

/**
 * @package Shopware\Struct\Product
 */
class MarketingAttribute implements Attribute
{
    /**
     * @var bool
     */
    private $isNew = false;

    /**
     * @var bool
     */
    private $isTopSeller = false;

    /**
     * @var bool
     */
    private $comingSoon = false;

    /**
     * @param boolean $comingSoon
     */
    public function setComingSoon($comingSoon)
    {
        $this->comingSoon = $comingSoon;
    }

    /**
     * @param boolean $isNew
     */
    public function setIsNew($isNew)
    {
        $this->isNew = $isNew;
    }

    /**
     * @param boolean $isTopSeller
     */
    public function setIsTopSeller($isTopSeller)
    {
        $this->isTopSeller = $isTopSeller;
    }

    /**
     * @return boolean
     */
    public function comingSoon()
    {
        return $this->comingSoon;
    }

    /**
     * @return boolean
     */
    public function isNew()
    {
        return $this->isNew;
    }

    /**
     * @return boolean
     */
    public function isTopSeller()
    {
        return $this->isTopSeller;
    }


}
