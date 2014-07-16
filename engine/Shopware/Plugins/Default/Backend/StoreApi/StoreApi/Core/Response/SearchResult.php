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

class Shopware_StoreApi_Core_Response_SearchResult implements IteratorAggregate, ArrayAccess
{
    protected $rawData;
    protected $collection;
    protected $total = 0;

    public function __construct($rawData)
    {
        $this->rawData = $rawData;

        if (!empty($this->rawData['total'])) {
            $this->total = $this->rawData['total'];
        }

        $preparedResponse = new Shopware_StoreApi_Core_Response_Response($rawData);
        $this->collection = $preparedResponse->getCollection();
    }

    public function getIterator()
    {
        return new ArrayIterator( $this->collection );
    }

    public function offsetSet($offset, $value)
    {
        if (is_null($offset)) {
            $this->collection[] = $value;
        } else {
            $this->collection[$offset] = $value;
        }
    }
    public function offsetExists($offset)
    {
        return isset($this->collection[$offset]);
    }

    public function offsetUnset($offset)
    {
        unset($this->collection[$offset]);
    }

    public function offsetGet($offset)
    {
        return isset($this->collection[$offset]) ? $this->collection[$offset] : null;
    }

    public function getTotal()
    {
        return $this->total;
    }

    public function getCollection()
    {
        return $this->collection;
    }
}
