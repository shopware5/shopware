<?php
/**
 * Enlight
 *
 * LICENSE
 *
 * This source file is subject to the new BSD license that is bundled
 * with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://enlight.de/license
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@shopware.de so we can send you a copy immediately.
 *
 * @category   Enlight
 * @copyright  Copyright (c) 2011, shopware AG (http://www.shopware.de)
 * @license    http://enlight.de/license     New BSD License
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
