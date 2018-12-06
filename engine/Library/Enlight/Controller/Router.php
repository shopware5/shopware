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

/**
 * Basic class for each Enlight router controller.
 *
 * The Enlight_Controller is the basic class for the routing.
 *
 * @category   Enlight
 *
 * @copyright  Copyright (c) 2011, shopware AG (http://www.shopware.de)
 * @license    http://enlight.de/license     New BSD License
 */
abstract class Enlight_Controller_Router extends Enlight_Class
{
    /**
     * @var Enlight_Controller_Front contains an instance of the Enlight_Controller_Front
     */
    protected $front;

    /**
     * @var array contains all global parameters for the routing process
     */
    protected $globalParams = [];

    /**
     * Setter method for the front controller.
     *
     * @param Enlight_Controller_Front $front
     *
     * @return self
     */
    public function setFront(Enlight_Controller_Front $front)
    {
        $this->front = $front;

        return $this;
    }

    /**
     * Returns the front controller
     *
     * @return Enlight_Controller_Front
     */
    public function Front()
    {
        return $this->front;
    }

    /**
     * Starts the routing-process.
     *
     * @param Enlight_Controller_Request_Request $request
     *
     * @throws Enlight_Controller_Exception
     *
     * @return Enlight_Controller_Request_Request
     */
    abstract public function route(Enlight_Controller_Request_Request $request);

    /**
     * Assembles the given parameters.
     *
     * @param array $userParams
     *
     * @return mixed|string
     */
    abstract public function assemble($userParams = []);

    /**
     * Sets a global parameter.
     *
     * @param string $name
     * @param mixed  $value
     *
     * @return self
     */
    public function setGlobalParam($name, $value)
    {
        $this->globalParams[$name] = $value;

        return $this;
    }
}
