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

namespace Shopware\Bundle\SearchBundle;

use OutOfBoundsException;
use Shopware\Bundle\StoreFrontBundle\Struct\ListProduct;

class BatchProductSearchResult
{
    /**
     * Internal storage which contains all struct data.
     *
     * @var array<string, array<ListProduct>>
     */
    protected $storage = [];

    /**
     * @param array<string, array<ListProduct>> $storage
     */
    public function __construct(array $storage)
    {
        $this->storage = $storage;
    }

    /**
     * Returns a single storage value.
     *
     * @param string $key
     *
     * @throws OutOfBoundsException
     *
     * @return array<ListProduct>
     */
    public function get($key)
    {
        if (\array_key_exists($key, $this->storage)) {
            return $this->storage[$key];
        }

        throw new OutOfBoundsException(sprintf('Key "%s" was not found.', $key));
    }

    /**
     * @return array<string, array<ListProduct>>
     */
    public function getAll()
    {
        return $this->storage;
    }

    /**
     * @return array<string>
     */
    public function getProductNumbers()
    {
        if (\count($this->storage) === 0) {
            return [];
        }

        $productNumbers = array_merge(...array_values($this->storage));

        return array_keys($productNumbers);
    }
}
