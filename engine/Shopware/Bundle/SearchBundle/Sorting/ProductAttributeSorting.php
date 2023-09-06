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

namespace Shopware\Bundle\SearchBundle\Sorting;

use RuntimeException;
use Shopware\Bundle\SearchBundle\SortingInterface;

class ProductAttributeSorting extends Sorting
{
    private string $field;

    public function __construct(string $field, string $direction = SortingInterface::SORT_ASC)
    {
        $this->setField($field);
        parent::__construct($direction);
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'product_attribute_' . $this->field;
    }

    /**
     * @param string $field
     */
    public function setField($field)
    {
        if (!\is_string($field) || $field === '') {
            throw new RuntimeException('ProductAttributeSorting class requires a defined attribute field!');
        }
        $this->field = $field;
    }

    /**
     * @return string
     */
    public function getField()
    {
        return $this->field;
    }
}
