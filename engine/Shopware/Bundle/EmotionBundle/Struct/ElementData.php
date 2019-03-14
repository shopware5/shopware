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

namespace Shopware\Bundle\EmotionBundle\Struct;

class ElementData implements \JsonSerializable
{
    /**
     * Internal storage which contains all struct data.
     *
     * @var array
     */
    protected $storage = [];

    /**
     * Returns the whole storage data.
     *
     * @return array
     */
    public function getAll()
    {
        return $this->storage;
    }

    /**
     * Returns a single storage value.
     *
     * @param string $name
     *
     * @return mixed|null
     */
    public function get($name, $default = null)
    {
        return array_key_exists($name, $this->storage) ? $this->storage[$name] : $default;
    }

    /**
     * @param string $name
     */
    public function set($name, $value)
    {
        $this->storage[$name] = $value;
    }

    /**
     * {@inheritdoc}
     */
    public function jsonSerialize()
    {
        return $this->storage;
    }
}
