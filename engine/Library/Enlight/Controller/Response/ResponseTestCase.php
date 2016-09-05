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
 * @package    Enlight_Controller
 * @copyright  Copyright (c) 2011, shopware AG (http://www.shopware.de)
 * @license    http://enlight.de/license     New BSD License
 * @version    $Id$
 * @author     Heiner Lohaus
 * @author     $Author$
 */

/**
 * Controller of the Enlight response test cases.
 *
 * The Enlight_Controller_Response_ResponseTestCase is used to test the response in the controller test cases.
 *
 * @category   Enlight
 * @package    Enlight_Controller
 * @copyright  Copyright (c) 2011, shopware AG (http://www.shopware.de)
 * @license    http://enlight.de/license     New BSD License
 */
class Enlight_Controller_Response_ResponseTestCase
    extends Zend_Controller_Response_HttpTestCase
    implements Enlight_Controller_Response_Response
{
    /**
     * @var array Contains all cookies, which have been set by the "setCookie" function.
     */
    protected $_cookies = array();

    /**
     * Sets a cookie method
     *
     * @param string $name
     * @param string $value
     * @param int    $expire
     * @param string $path
     * @param string $domain
     * @param bool   $secure
     * @param bool   $httpOnly
     * @return unknown
     */
    public function setCookie($name,
                               $value = null,
                               $expire = 0,
                               $path = null,
                               $domain = null,
                               $secure = false,
                               $httpOnly = false
    ) {
        $this->_cookies[$name] = array(
            'value' => $value,
            'expire' => $expire,
            'path' => $path,
            'domain' => $domain,
            'secure' => $secure,
            'httpOnly' => $httpOnly
        );
        return $this;
    }

    /**
     * @return array
     */
    public function getCookies()
    {
        return $this->_cookies;
    }

    /**
     * Sends all cookies
     *
     * @return Enlight_Controller_Response_ResponseTestCase
     */
    public function sendCookies()
    {
        if (!empty($this->_cookies)) {
            $this->canSendHeaders(true);
        }
        return $this;
    }

    /**
     * Gets a cookie value
     *
     * @param string $name
     * @param string $default
     * @return Zend_Controller_Request_HttpTestCase
     */
    public function getCookie($name, $default = null)
    {
        return isset($this->_cookies[$name]['value']) ? $this->_cookies[$name]['value'] : $default;
    }

    /**
     * Gets all the information for a cookie
     *
     * @param string $name
     * @return Zend_Controller_Request_HttpTestCase
     */
    public function getFullCookie($name)
    {
        return isset($this->_cookies[$name]) ? $this->_cookies[$name] : null;
    }

    /**
     * Gets a header value
     *
     * @param string $name
     * @param string $default
     * @return Zend_Controller_Request_HttpTestCase
     */
    public function getHeader($name, $default = null)
    {
        foreach ($this->_headers as $header) {
            if (isset($header['name']) && $header['name'] === $name) {
                return $header['value'];
            }
        }
        return $default;
    }

    /**
     * @return null
     */
    public function unsetExceptions()
    {
        $this->_exceptions = array();
    }
}
