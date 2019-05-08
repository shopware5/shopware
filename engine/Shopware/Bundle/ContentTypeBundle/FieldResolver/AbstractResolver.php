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

namespace Shopware\Bundle\ContentTypeBundle\FieldResolver;

use Shopware\Bundle\ContentTypeBundle\Structs\Field;

abstract class AbstractResolver
{
    /**
     * @var array
     */
    protected $storage = [];

    /**
     * @var array
     */
    protected $resolveIds = [];

    /**
     * @param string|array $item
     */
    public function add($item, Field $field): void
    {
        $values = array_filter(explode('|', $item));

        foreach ($values as $value) {
            if (isset($this->storage[$value]) || isset($this->resolveIds[$value])) {
                return;
            }

            $this->resolveIds[] = $value;
        }
    }

    abstract public function resolve(): void;

    /**
     * @param int|string $item
     */
    public function get($item, Field $field)
    {
        $values = array_filter(explode('|', $item));

        if (!$field->getType()::isMultiple()) {
            if (isset($this->storage[$values[0]])) {
                return $this->storage[$values[0]];
            }

            return null;
        }

        $result = [];

        foreach ($values as $value) {
            if (isset($this->storage[$value])) {
                $result[] = $this->storage[$value];
            }
        }

        return $result;
    }
}
