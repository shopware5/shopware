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

namespace Shopware\Bundle\SearchBundle\FacetResult;

use Shopware\Bundle\SearchBundle\FacetResultInterface;
use Shopware\Bundle\StoreFrontBundle\Struct\Attribute;
use Shopware\Bundle\StoreFrontBundle\Struct\Extendable;

/**
 * @category  Shopware
 * @package   Shopware\Bundle\SearchBundle\FacetResult
 * @copyright Copyright (c) shopware AG (http://www.shopware.de)
 */
class RangeFacetResult
    extends Extendable
    implements FacetResultInterface
{
    /**
     * @var string
     */
    private $facetName;

    /**
     * @var bool
     */
    private $active;

    /**
     * @var string
     */
    private $label;

    /**
     * @var int
     */
    private $min;

    /**
     * @var int
     */
    private $max;

    /**
     * @var string
     */
    private $minFieldName;

    /**
     * @var string
     */
    private $maxFieldName;

    /**
     * @var float
     */
    private $activeMax;

    /**
     * @var float
     */
    private $activeMin;

    /**
     * @var null|string
     */
    private $template = null;

    /**
     * @param string $facetName
     * @param boolean $active
     * @param string $label
     * @param float $min
     * @param float $max
     * @param float $activeMin
     * @param float $activeMax
     * @param string $minFieldName
     * @param string $maxFieldName
     * @param string|null $template
     * @param Attribute[] $attributes
     */
    public function __construct(
        $facetName,
        $active,
        $label,
        $min,
        $max,
        $activeMin,
        $activeMax,
        $minFieldName,
        $maxFieldName,
        $attributes = [],
        $template = 'frontend/listing/filter/facet-range.tpl'
    ) {
        $this->facetName = $facetName;
        $this->active = $active;
        $this->label = $label;
        $this->min = $min;
        $this->activeMin = $activeMin;
        $this->minFieldName = $minFieldName;
        $this->max = $max;
        $this->activeMax = $activeMax;
        $this->maxFieldName = $maxFieldName;
        $this->attributes = $attributes;
        $this->template = $template;
    }

    /**
     * @return float
     */
    public function getActiveMax()
    {
        return $this->activeMax;
    }

    /**
     * @return float
     */
    public function getActiveMin()
    {
        return $this->activeMin;
    }

    /**
     * @return string
     */
    public function getFacetName()
    {
        return $this->facetName;
    }

    /**
     * @return boolean
     */
    public function isActive()
    {
        return $this->active;
    }

    /**
     * @return string
     */
    public function getLabel()
    {
        return $this->label;
    }

    /**
     * @return float
     */
    public function getMax()
    {
        return $this->max;
    }

    /**
     * @return string
     */
    public function getMaxFieldName()
    {
        return $this->maxFieldName;
    }

    /**
     * @return float
     */
    public function getMin()
    {
        return $this->min;
    }

    /**
     * @return string
     */
    public function getMinFieldName()
    {
        return $this->minFieldName;
    }

    /**
     * @inheritdoc
     */
    public function getTemplate()
    {
        return $this->template;
    }

    /**
     * @param null|string $template
     */
    public function setTemplate($template)
    {
        $this->template = $template;
    }
}
