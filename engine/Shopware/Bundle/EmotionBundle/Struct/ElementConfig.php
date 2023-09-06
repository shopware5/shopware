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

namespace Shopware\Bundle\EmotionBundle\Struct;

use JsonSerializable;
use ReturnTypeWillChange;
use Shopware\Models\Emotion\Library\Field;

class ElementConfig implements JsonSerializable
{
    /**
     * Internal storage which contains all struct data.
     *
     * @var array
     */
    protected $storage = [];

    public function __construct(array $data = [])
    {
        $storage = [];
        foreach ($data as $configElement) {
            if ($configElement['__emotionLibraryComponentField_value_type'] === Field::VALUE_TYPE_JSON) {
                $configElement['__emotionElementValue_value'] = json_decode($configElement['__emotionElementValue_value'], true);
            }

            $storage[$configElement['__emotionLibraryComponentField_name']] = $configElement['__emotionElementValue_value'];
        }

        $this->storage = $storage;
    }

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
     */
    public function get($name, $default = null)
    {
        return \array_key_exists($name, $this->storage) ? $this->storage[$name] : $default;
    }

    /**
     * @param string $name
     */
    public function set($name, $value)
    {
        $this->storage[$name] = $value;
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
}
