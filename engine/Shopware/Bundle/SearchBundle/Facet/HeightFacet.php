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

namespace Shopware\Bundle\SearchBundle\Facet;

use Shopware\Bundle\SearchBundle\FacetInterface;

class HeightFacet implements FacetInterface
{
    private const NAME = 'height';

    /**
     * @var string
     */
    protected $label;

    /**
     * @var string|null
     */
    protected $suffix;

    /**
     * @var int
     */
    protected $digits;

    /**
     * @param string|null $label
     * @param string|null $suffix
     * @param int         $digits
     */
    public function __construct($label = null, $suffix = null, $digits = 3)
    {
        $this->label = $label;
        $this->suffix = $suffix;
        $this->digits = $digits;
    }

    /**
     * @return string|null
     */
    public function getLabel()
    {
        return $this->label;
    }

    /**
     * @return string|null
     */
    public function getSuffix()
    {
        return $this->suffix;
    }

    /**
     * @return int
     */
    public function getDigits()
    {
        return $this->digits;
    }

    public function getName()
    {
        return self::NAME;
    }
}
