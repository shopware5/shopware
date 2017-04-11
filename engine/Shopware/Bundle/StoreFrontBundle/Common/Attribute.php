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

namespace Shopware\Bundle\StoreFrontBundle\Common;

/**
 * @category  Shopware
 *
 * @copyright Copyright (c) shopware AG (http://www.shopware.de)
 */
class Attribute extends Struct
{
    /**
     * Internal storage which contains all struct data.
     *
     * @var array
     */
    protected $storage = [];

    /**
     * @param array $data
     *
     * @throws \Exception
     */
    public function __construct($data = [])
    {
        if (!$this->isValid($data)) {
            throw new \Exception(sprintf(
                'Class values should be serializable',
                __CLASS__
            ));
        }
        $this->storage = $data;
    }

    /**
     * Checks if a storage key exists
     *
     * @param $key
     *
     * @return bool
     */
    public function exists($key)
    {
        return array_key_exists($key, $this->storage);
    }

    /**
     * Sets a single store value.
     * The attribute storage allows only serializable
     * values which allows shopware to serialize the struct elements.
     *
     * @param $name
     * @param $value
     *
     * @throws \Exception
     */
    public function set($name, $value)
    {
        if (!$this->isValid($value)) {
            throw new \Exception(sprintf(
                'Class values should be serializable',
                __CLASS__
            ));
        }

        $this->storage[$name] = $value;
    }

    /**
     * Returns the whole storage data.
     *
     * @return array
     */
    public function toArray()
    {
        return $this->storage;
    }

    /**
     * Returns a single storage value.
     *
     * @param $name
     *
     * @return mixed
     */
    public function get($name)
    {
        return $this->storage[$name];
    }

    /**
     * {@inheritdoc}
     */
    public function jsonSerialize()
    {
        $data = $this->storage;
        $data['_class'] = static::class;

        return $data;
    }

    /**
     * @param mixed $value
     *
     * @return bool
     */
    private function isValid($value)
    {
        if (is_scalar($value)) {
            return true;
        }
        if ($value instanceof \JsonSerializable) {
            return true;
        }
        if (!is_array($value)) {
            return true;
        }

        foreach ($value as $val) {
            if (!$this->isValid($val)) {
                return false;
            }
        }

        return true;
    }
}
