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

namespace Shopware\Bundle\StoreFrontBundle\Struct;

use ArrayAccess;
use InvalidArgumentException;
use JsonSerializable;
use ReturnTypeWillChange;

class Attribute extends Struct implements JsonSerializable, ArrayAccess
{
    /**
     * Internal storage which contains all struct data.
     *
     * @var array
     */
    protected $storage = [];

    /**
     * @throws InvalidArgumentException
     */
    public function __construct(array $data = [])
    {
        if (!$this->isValid($data)) {
            throw new InvalidArgumentException('Class values should be serializable');
        }
        $this->storage = $data;
    }

    /**
     * Checks if a storage key exists
     *
     * @param string $key
     *
     * @return bool
     */
    public function exists($key)
    {
        return \array_key_exists($key, $this->storage);
    }

    /**
     * Sets a single store value.
     * The attribute storage allows only serializable
     * values which allows shopware to serialize the struct elements.
     *
     * @param string $name
     *
     * @throws InvalidArgumentException
     */
    public function set($name, $value)
    {
        if (!$this->isValid($value)) {
            throw new InvalidArgumentException('Class values should be serializable');
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
     * @param string $name
     */
    public function get($name)
    {
        return $this->storage[$name];
    }

    /**
     * @return array<string, mixed>
     *
     * @deprecated - Native return type will be added with Shopware 5.8
     */
    #[ReturnTypeWillChange]
    public function jsonSerialize()
    {
        return $this->storage;
    }

    /**
     * @param mixed $offset attribute name
     *
     * @deprecated - Native return and parameter type will be added with Shopware 5.8
     */
    #[ReturnTypeWillChange]
    public function offsetExists($offset)
    {
        return $this->exists($offset);
    }

    /**
     * @param mixed $offset attribute name
     *
     * @return mixed attribute value
     *
     * @deprecated - Native return and parameter type will be added with Shopware 5.8
     */
    #[ReturnTypeWillChange]
    public function offsetGet($offset)
    {
        return $this->get($offset);
    }

    /**
     * @param mixed $offset attribute name
     * @param mixed $value  attribute value
     *
     * @return void
     *
     * @deprecated - Native return and parameter type will be added with Shopware 5.8
     */
    #[ReturnTypeWillChange]
    public function offsetSet($offset, $value)
    {
        $this->set($offset, $value);
    }

    /**
     * @param mixed $offset attribute name
     *
     * @return void
     *
     * @deprecated - Native return and parameter type will be added with Shopware 5.8
     */
    #[ReturnTypeWillChange]
    public function offsetUnset($offset)
    {
        if ($this->exists($offset)) {
            unset($this->storage[$offset]);
        }
    }

    private function isValid($value): bool
    {
        if ($value instanceof JsonSerializable || \is_scalar($value) || $value === null) {
            return true;
        }

        if (\is_array($value)) {
            foreach ($value as $val) {
                if (!$this->isValid($val)) {
                    return false;
                }
            }

            return true;
        }

        return false;
    }
}
