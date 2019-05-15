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
 * Class for an Controller exception.
 *
 * An Enlight_Controller_Exception is thrown if an error occurs in a controller.
 *
 * @category   Enlight
 * @package    Enlight_Controller
 * @copyright  Copyright (c) 2011, shopware AG (http://www.shopware.de)
 * @license    http://enlight.de/license     New BSD License
 */
class Enlight_Controller_EventArgs extends Enlight_Event_EventArgs
{
    /**
     * @return Enlight_Controller_Front
     */
    public function getSubject()
    {
        return $this->get('subject');
    }

    /**
     * @return Enlight_Controller_Request_Request
     */
    public function getRequest()
    {
        return $this->get('request');
    }

    /**
     * @return Enlight_Controller_Response_ResponseHttp
     */
    public function getResponse()
    {
        return $this->get('response');
    }
}
