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

namespace Shopware\Bundle\SearchBundle;

use Shopware\Bundle\StoreFrontBundle\Struct\BaseProduct;

class BatchProductNumberSearchResult
{
    /**
     * Internal storage which contains all struct data.
     *
     * @var array
     */
    protected $storage = [];

    public function __construct(array $storage)
    {
        $this->storage = $storage;
    }

    /**
     * Returns a single storage value.
     *
     * @param string $key
     *
     * @throws \OutOfBoundsException
     *
     * @return BaseProduct[]
     */
    public function get($key)
    {
        if (array_key_exists($key, $this->storage)) {
            return $this->storage[$key];
        }

        throw new \OutOfBoundsException(sprintf('Key "%s" was not found.', $key));
    }

    /**
     * @return array
     */
    public function getAll()
    {
        return $this->storage;
    }

    /**
     * @return array
     */
    public function getProductNumbers()
    {
        if (!count($this->storage)) {
            return $this->storage;
        }

        $productNumbers = [];

        /** @var BaseProduct[] $products */
        foreach ($this->storage as $products) {
            foreach ($products as $product) {
                $productNumbers[] = $product->getNumber();
            }
        }

        return $productNumbers;
    }
}
