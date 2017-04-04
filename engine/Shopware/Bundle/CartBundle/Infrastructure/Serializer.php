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

namespace Shopware\Bundle\CartBundle\Infrastructure;

class Serializer
{
    const FORMAT_JSON = 'json';

    const FORMAT_ARRAY = 'array';

    public function serialize($data, string $format)
    {
        switch ($format) {
            case self::FORMAT_ARRAY:
                return json_decode(json_encode($data), true);

            case self::FORMAT_JSON:
                return json_encode($data);

            default:
                throw new \Exception(sprintf('Unsupported serializer format %s', $format));
        }
    }
}
