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
 *
 * @copyright Copyright (c) shopware AG (http://www.shopware.de)
 */
class TreeFacetResult extends Extendable implements FacetResultInterface
{
    /**
     * @var string
     */
    private $facetName;

    /**
     * @var string
     */
    private $fieldName;

    /**
     * @var bool
     */
    private $active;

    /**
     * @var string
     */
    private $label;

    /**
     * @var TreeItem[]
     */
    private $values;

    /**
     * @var string|null
     */
    private $template = null;

    /**
     * @param string      $facetName
     * @param string      $fieldName
     * @param bool        $active
     * @param string      $label
     * @param TreeItem[]  $values
     * @param null|string $template
     * @param Attribute[] $attributes
     */
    public function __construct(
        $facetName,
        $fieldName,
        $active,
        $label,
        $values,
        $attributes = [],
        $template = 'frontend/listing/filter/facet-value-tree.tpl'
    ) {
        $this->facetName = $facetName;
        $this->fieldName = $fieldName;
        $this->active = $active;
        $this->label = $label;
        $this->values = $values;
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
     * @return TreeItem[]
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
}
