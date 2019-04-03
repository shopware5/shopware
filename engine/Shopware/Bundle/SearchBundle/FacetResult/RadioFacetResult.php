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

class RadioFacetResult extends Extendable implements FacetResultInterface, TemplateSwitchable
{
    private const TEMPLATE = 'frontend/listing/filter/facet-radio.tpl';

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
     * @var string
     */
    protected $fieldName;

    /**
     * @var ValueListItem[]
     */
    protected $values;

    /**
     * @var string|null
     */
    protected $template;

    /**
     * @param string          $facetName
     * @param bool            $active
     * @param string          $label
     * @param ValueListItem[] $values
     * @param string          $fieldName
     * @param string|null     $template
     * @param Attribute[]     $attributes
     */
    public function __construct(
        $facetName,
        $active,
        $label,
        $values,
        $fieldName,
        $attributes = [],
        $template = self::TEMPLATE
    ) {
        $this->facetName = $facetName;
        $this->active = $active;
        $this->label = $label;
        $this->values = $values;
        $this->fieldName = $fieldName;
        $this->attributes = $attributes;
        $this->template = $template;
    }

    /**
     * @return string
     */
    public function getFacetName()
    {
        return $this->facetName;
    }

    /**
     * @return string
     */
    public function getFieldName()
    {
        return $this->fieldName;
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
     * @return ValueListItem[]
     */
    public function getValues()
    {
        return $this->values;
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
}
