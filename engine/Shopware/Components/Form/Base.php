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

namespace Shopware\Components\Form;

use Shopware\Components\Form\Interfaces\Element;

class Base implements Element
{
    /**
     * @var string
     */
    protected $name;

    /**
     * @param string $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Outputs the object data as array.
     * Used to convert a form structure to
     * a uniform array structure which can be used
     * from the Shopware\Components\Form\Persister and
     * Shopware\Components\Form\Hydrator
     *
     * @return array
     */
    public function toArray()
    {
        $properties = get_class_vars(get_class($this));

        $data = [
            'type' => get_class($this),
        ];

        foreach ($properties as $property => $value) {
            $method = 'get' . ucfirst($property);

            if (!method_exists($this, $method)) {
                continue;
            }

            $value = $this->$method();

            if ($value instanceof \Traversable) {
                $converted = [];
                foreach ($value as $item) {
                    if ($item instanceof self) {
                        $converted[] = $item->toArray();
                    } else {
                        $converted[] = $item;
                    }
                }
                $value = $converted;
            }
            $data[$property] = $value;
        }

        return $data;
    }

    /**
     * Helper function to set the object
     * data by array data.
     *
     * @return $this
     */
    public function fromArray(array $array)
    {
        foreach ($array as $key => $value) {
            $method = 'set' . ucfirst($key);
            if (method_exists($this, $method)) {
                $this->$method($value);
            }
        }

        return $this;
    }
}
