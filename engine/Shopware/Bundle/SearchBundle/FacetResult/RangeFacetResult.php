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
use Shopware\Bundle\SearchBundle\TemplateSwitchable;
use Shopware\Bundle\StoreFrontBundle\Struct\Attribute;
use Shopware\Bundle\StoreFrontBundle\Struct\Extendable;

class RangeFacetResult extends Extendable implements FacetResultInterface, TemplateSwitchable
{
    private const TEMPLATE = 'frontend/listing/filter/facet-range.tpl';

    /**
     * @var string
     */
    protected $facetName;

    /**
     * @var bool
     */
    protected $active;

    /**
     * @var string
     */
    protected $label;

    /**
     * @var float
     */
    protected $min;

    /**
     * @var float
     */
    protected $max;

    /**
     * @var string
     */
    protected $minFieldName;

    /**
     * @var string
     */
    protected $maxFieldName;

    /**
     * @var float
     */
    protected $activeMax;

    /**
     * @var float
     */
    protected $activeMin;

    /**
     * @var string|null
     */
    protected $suffix;

    /**
     * @var string|null
     */
    protected $template;

    /**
     * @var int
     */
    protected $digits;

    /**
     * @param string      $facetName
     * @param bool        $active
     * @param string      $label
     * @param float       $min
     * @param float       $max
     * @param float       $activeMin
     * @param float       $activeMax
     * @param string      $minFieldName
     * @param string      $maxFieldName
     * @param Attribute[] $attributes
     * @param string|null $suffix
     * @param int         $digits
     * @param string|null $template
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
        $suffix = null,
        $digits = 2,
        $template = self::TEMPLATE
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
        $this->suffix = $suffix;
        $this->digits = $digits;
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
     * @return bool
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
     * {@inheritdoc}
     */
    public function getTemplate()
    {
        return $this->template;
    }

    /**
     * @param string|null $template
     */
    public function setTemplate($template)
    {
        $this->template = $template;
    }

    /**
     * @param float $min
     */
    public function setMin($min)
    {
        $this->min = $min;
    }

    /**
     * @param float $max
     */
    public function setMax($max)
    {
        $this->max = $max;
    }

    /**
     * @param float $activeMax
     */
    public function setActiveMax($activeMax)
    {
        $this->activeMax = $activeMax;
    }

    /**
     * @param float $activeMin
     */
    public function setActiveMin($activeMin)
    {
        $this->activeMin = $activeMin;
    }

    /**
     * @param bool $active
     */
    public function setActive($active)
    {
        $this->active = $active;
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
}
