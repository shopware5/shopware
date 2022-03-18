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

namespace Shopware\Components\LegacyRequestWrapper;

use ArrayAccess;
use Enlight_Controller_Request_Request;
use ReturnTypeWillChange;

class GetWrapper implements ArrayAccess
{
    private Enlight_Controller_Request_Request $request;

    public function __construct(Enlight_Controller_Request_Request $request)
    {
        $this->request = $request;
    }

    /**
     * @param mixed $offset an offset to check for
     *
     * @return bool true on success or false on failure. The return value will be casted to boolean if non-boolean was returned
     *
     * @deprecated - Native return and parameter type will be added with Shopware 5.8
     */
    #[ReturnTypeWillChange]
    public function offsetExists($offset)
    {
        $getData = $this->request->getQuery();

        return \array_key_exists($offset, $getData);
    }

    /**
     * @param mixed $offset the offset to retrieve
     *
     * @return mixed can return all value types
     *
     * @deprecated - Native return and parameter type will be added with Shopware 5.8
     */
    #[ReturnTypeWillChange]
    public function offsetGet($offset)
    {
        return $this->request->getQuery($offset);
    }

    /**
     * @param mixed $offset the offset to assign the value to
     * @param mixed $value  the value to set
     *
     * @return void
     *
     * @deprecated - Native return and parameter type will be added with Shopware 5.8
     */
    #[ReturnTypeWillChange]
    public function offsetSet($offset, $value)
    {
        $this->request->setQuery($offset, $value);
    }

    /**
     * @param mixed $offset the offset to unset
     *
     * @return void
     *
     * @deprecated - Native return and parameter type will be added with Shopware 5.8
     */
    #[ReturnTypeWillChange]
    public function offsetUnset($offset)
    {
        $this->request->setQuery($offset, null);
    }

    /**
     * Replace all _GET values with provided values
     *
     * @param array $values
     */
    public function setAll($values)
    {
        $this->request->setQuery($values);
    }

    /**
     * Returns an array with all current values in _GET
     *
     * @return array
     */
    public function toArray()
    {
        return $this->request->getQuery();
    }
}
