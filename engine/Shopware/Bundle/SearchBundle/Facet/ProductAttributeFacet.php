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

/**
 * @category  Shopware
 * @package   Shopware\Bundle\SearchBundle\Facet
 * @copyright Copyright (c) shopware AG (http://www.shopware.de)
 */
class ProductAttributeFacet implements FacetInterface
{
    const MODE_NOT_EMPTY = 'not_null';

    const MODE_EMPTY = 'null';

    const MODE_VALUES = 'values';

    /**
     * @var string
     */
    private $field;

    /**
     * @var string
     */
    private $mode;

    /**
     * @param $field
     * @param string $mode
     */
    public function __construct($field, $mode = self::MODE_VALUES)
    {
        $this->field = $field;
        $this->mode = $mode;
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
     * @param string $field
     */
    public function setField($field)
    {
        $this->field = $field;
    }

    /**
     * @return string
     */
    public function getMode()
    {
        return $this->mode;
    }

    /**
     * @param string $mode
     */
    public function setMode($mode)
    {
        $this->mode = $mode;
    }

    /**
     * @inheritdoc
     */
    public function jsonSerialize()
    {
        return get_object_vars($this);
    }
}
