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

class ProductAttributeFacet implements FacetInterface
{
    const MODE_VALUE_LIST_RESULT = 'value_list';
    const MODE_RADIO_LIST_RESULT = 'radio';
    const MODE_BOOLEAN_RESULT = 'boolean';
    const MODE_RANGE_RESULT = 'range';

    /**
     * @var string
     */
    protected $field;

    /**
     * @var string
     */
    protected $mode;

    /**
     * @var string
     */
    protected $formFieldName;

    /**
     * @var string
     */
    protected $label;

    /**
     * @var string|null
     */
    protected $template;

    /**
     * @var string
     */
    protected $suffix;

    /**
     * @var int
     */
    protected $digits;

    /**
     * @param string      $field
     * @param string      $mode
     * @param string      $formFieldName
     * @param string      $label
     * @param string|null $template
     * @param string      $suffix
     * @param int         $digits
     */
    public function __construct(
        $field,
        $mode,
        $formFieldName,
        $label,
        $template = null,
        $suffix = '',
        $digits = 2
    ) {
        $this->field = $field;
        $this->mode = $mode;
        $this->formFieldName = $formFieldName;
        $this->label = $label;
        $this->template = $template;
        $this->suffix = $suffix;
        $this->digits = $digits;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'product_attribute_' . $this->field;
    }

    /**
     * @return string
     */
    public function getField()
    {
        return $this->field;
    }

    /**
     * @return string
     */
    public function getMode()
    {
        return $this->mode;
    }

    /**
     * @return string
     */
    public function getLabel()
    {
        return $this->label;
    }

    /**
     * @return string
     */
    public function getFormFieldName()
    {
        return $this->formFieldName;
    }

    /**
     * @return string|null
     */
    public function getTemplate()
    {
        return $this->template;
    }

    /**
     * @return int
     */
    public function getDigits()
    {
        return $this->digits;
    }

    /**
     * @return string
     */
    public function getSuffix()
    {
        return $this->suffix;
    }
}
