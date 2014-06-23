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

namespace Shopware\Gateway\Search\Facet;

use Shopware\Gateway\Search\Facet;

/**
 * @package Shopware\Gateway\Search\Facet
 */
class Manufacturer implements Facet
{
    /**
     * Flag if the facet is filtered with a condition
     * @var bool
     */
    private $filtered = false;

    /**
     * @var \Shopware\Struct\Product\Manufacturer[]
     */
    private $manufacturers;

    /**
     * @return string
     */
    public function getName()
    {
        return 'manufacturer';
    }

    /**
     * @return \Shopware\Struct\Product\Manufacturer[]
     */
    public function getManufacturers()
    {
        return $this->manufacturers;
    }

    /**
     * @param \Shopware\Struct\Product\Manufacturer[] $manufacturers
     */
    public function setManufacturers(array $manufacturers)
    {
        $this->manufacturers = $manufacturers;
    }

    /**
     * @param bool $filtered
     */
    public function setFiltered($filtered)
    {
        $this->filtered = $filtered;
    }

    /**
     * @return bool
     */
    public function isFiltered()
    {
        return $this->filtered;
    }
}
