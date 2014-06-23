<?php
/**
 * Shopware 4
 * Copyright © shopware AG
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

namespace Shopware\Gateway\DBAL\Hydrator;

use Shopware\Struct;

/**
 * @package Shopware\Gateway\DBAL\Hydrator
 */
class Hydrator
{
    public function extractFields($prefix, $data)
    {
        $result = array();
        foreach ($data as $field => $value) {
            if (strpos($field, $prefix) === 0) {
                $key = str_replace($prefix, '', $field);
                $result[$key] = $value;
            }
        }
        return $result;
    }

    protected function getFields($prefix, $data)
    {
        $result = array();
        foreach ($data as $field => $value) {
            if (strpos($field, $prefix) === 0) {
                $result[$field] = $value;
            }
        }
        return $result;
    }

    protected function convertArrayKeys($data, $keys)
    {
        foreach ($keys as $old => $new) {
            if (!isset($data[$old])) {
                continue;
            }

            $data[$new] = $data[$old];
            unset($data[$old]);
        }

        return $data;
    }
}
