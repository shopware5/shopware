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

namespace Shopware\Bundle\StoreFrontBundle\Gateway\DBAL\Hydrator;

use Shopware\Bundle\StoreFrontBundle\Struct;
use Shopware\Models;

/**
 * @category  Shopware
 * @package   Shopware\Bundle\StoreFrontBundle\Gateway\DBAL\Hydrator
 * @copyright Copyright (c) shopware AG (http://www.shopware.de)
 */
class ProductStreamHydrator extends Hydrator
{
    /**
     * @var array
     */
    private $translationProductStreamFields = [
        'name' => '__stream_name',
        'description' => '__stream_description'
    ];

    /**
     * @param array $data
     * @return Struct\ProductStream
     */
    public function hydrate(array $data)
    {
        $productStream = new Struct\ProductStream();
        $translation = $this->getTranslation(
            $data,
            '__stream_translation',
            '__stream_translation_fallback',
            $this->translationProductStreamFields
        );

        $data = array_merge($data, $translation);

        if (isset($data['__stream_id'])) {
            $productStream->setId((int) $data['__stream_id']);
        }

        if (isset($data['__stream_name'])) {
            $productStream->setName($data['__stream_name']);
        }

        if (isset($data['__stream_description'])) {
            $productStream->setDescription($data['__stream_description']);
        }

        if (isset($data['__stream_type'])) {
            $productStream->setType((int) $data['__stream_type']);
        }

        return $productStream;
    }

    /**
     * @param $data
     * @param $arrayKey
     * @param $fallbackArrayKey
     * @param array $mapping
     * @return array|mixed
     */
    private function getTranslation($data, $arrayKey, $fallbackArrayKey, $mapping)
    {
        if (!isset($data[$arrayKey])
            || empty($data[$arrayKey])
        ) {
            $translation = [];
        } else {
            $translation = unserialize($data[$arrayKey]);
        }

        if (isset($data[$fallbackArrayKey])
            && !empty($data[$fallbackArrayKey])
        ) {
            $fallbackTranslation = unserialize($data[$fallbackArrayKey]);
            $translation += $fallbackTranslation;
        }

        return $this->convertArrayKeys($translation, $mapping);
    }
}
