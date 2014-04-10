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
 * Controller for Enlight request test cases.
 *
 * The Enlight_Controller_Request_RequestTestCase extends the zend controller request
 * with some helper functions.
 *
 * @category   Enlight
 * @package    Enlight_Controller
 * @copyright  Copyright (c) 2011, shopware AG (http://www.shopware.de)
 * @license    http://enlight.de/license     New BSD License
 */
class Enlight_Controller_Request_RequestTestCase
    extends Zend_Controller_Request_HttpTestCase
    implements Enlight_Controller_Request_Request
{
    /**
     * Server params
     * @var array
     */
    protected $_serverParams = array();

    /**
     * Set GET values method
     *
     * @param  string|array $spec
     * @param  null|mixed   $value
     * @return Zend_Controller_Request_Http
     */
    public function setQuery($spec, $value = null)
    {
        if (!is_array($spec) && $value === null) {
            unset($_GET[$spec]);
            return $this;
        } elseif (is_array($spec) && empty($spec)) {
            $_GET = array();
            return $this;
        }
        return parent::setQuery($spec, $value);
    }

    /**
     * Set POST values method
     *
     * @param  string|array $spec
     * @param  null|mixed   $value
     * @return Zend_Controller_Request_Http
     */
    public function setPost($spec, $value = null)
    {
        if (!is_array($spec) && $value === null) {
            unset($_POST[$spec]);
            return $this;
        } elseif (is_array($spec) && empty($spec)) {
            $_POST = array();
            return $this;
        }

        return parent::setPost($spec, $value);
    }

    /**
     * Set SERVER remote address
     *
     * @param string $address
     * @return Enlight_Controller_Request_RequestHttp
     */
    public function setRemoteAddress($address)
    {
        $this->setServer('REMOTE_ADDR', $address);
        return $this;
    }

    /**
     * Sets HTTP host method
     *
     * @param string $host
     * @return Zend_Controller_Request_HttpTestCase
     */
    public function setHttpHost($host)
    {
        $this->setHeader('HOST', $host);
        return $this;
    }

    /**
     * Sets HTTP client method
     * @param      $ip
     * @param bool $setProxy
     * @return Enlight_Controller_Request_RequestTestCase
     */
    public function setClientIp($ip, $setProxy = true)
    {
        if ($setProxy) {
            $this->setHeader('CLIENT_IP', $ip);
        } else {
            $this->setServer('REMOTE_ADDR', $ip);
        }

        return $this;
    }

    /**
     * Sets a server param
     *
     * @param  string $key
     * @param  string $value
     * @return Zend_Controller_Request_HttpTestCase
     */
    public function setServer($key, $value = null)
    {
        $this->_serverParams[$key] = $value === null ? null : (string) $value;
        return $this;
    }

    /**
     * Gets a server param
     *
     * @param string $key
     * @param string $default
     * @return Zend_Controller_Request_HttpTestCase
     */
    public function getServer($key = null, $default = null)
    {
        if (null === $key) {
            return array_merge($_SERVER, $this->_serverParams);
        } elseif (isset($this->_serverParams[$key])) {
            return $this->_serverParams[$key] !== null ? $this->_serverParams[$key] : $default;
        } elseif (isset($_SERVER[$key])) {
            return $_SERVER[$key];
        } else {
            return $default;
        }
    }

    /**
     * Sets a request header.
     * The key will normalized for setting and retrieval.
     * @param  string $key
     * @param  string $value
     * @return Zend_Controller_Request_HttpTestCase
     */
    public function setHeader($key, $value = null)
    {
        if ($value !== null) {
            $key = $this->_normalizeHeaderName($key);
            $this->_headers[$key] = (string) $value;
        } else {
            unset($this->_headers[$key]);
        }
        $this->setServer('HTTP_' . $key, $value);
        return $this;
    }
}
