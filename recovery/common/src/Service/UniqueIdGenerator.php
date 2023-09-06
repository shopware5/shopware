<?php

declare(strict_types=1);
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

namespace Shopware\Recovery\Common\Service;

/**
 * Generates a random unique ID and caches it in a local file.
 */
class UniqueIdGenerator
{
    private string $cacheFilePath;

    public function __construct(string $cacheFilePath)
    {
        $this->cacheFilePath = $cacheFilePath;
    }

    /**
     * @return string
     */
    public function getUniqueId()
    {
        if (file_exists($this->cacheFilePath)) {
            return file_get_contents($this->cacheFilePath);
        }

        $uniqueId = $this->generateUniqueId();

        $this->saveUniqueId($uniqueId);

        return $uniqueId;
    }

    /**
     * @param int    $length
     * @param string $keyspace
     *
     * @return string
     */
    public function generateUniqueId($length = 32, $keyspace = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ')
    {
        $str = '';
        $max = mb_strlen($keyspace, '8bit') - 1;
        for ($i = 0; $i < $length; ++$i) {
            $str .= $keyspace[random_int(0, $max)];
        }

        return $str;
    }

    private function saveUniqueId(string $uniqueId): void
    {
        file_put_contents($this->cacheFilePath, $uniqueId);
    }
}
