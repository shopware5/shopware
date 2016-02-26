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
 * HTTP request controller for use with Enlight_Controller.
 *
 * The Enlight_Controller_Request_RequestHttp represents the request object (what is in the url, what was read out).
 *
 * @category   Enlight
 * @package    Enlight_Controller
 * @copyright  Copyright (c) 2011, shopware AG (http://www.shopware.de)
 * @license    http://enlight.de/license     New BSD License
 */
class Enlight_Controller_Request_RequestHttp
    extends Zend_Controller_Request_Http
    implements Enlight_Controller_Request_Request
{
    /**
     * @var string[]
     */
    private $validDeviceTypes = [
        'desktop',
        'tablet',
        'mobile',
    ];

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
     * Sets the request URI scheme
     *
     * @param $value
     * @return Enlight_Controller_Request_Request
     */
    public function setSecure($value = true)
    {
        $_SERVER['HTTPS'] = $value ? 'on' : null;
        return $this;
    }

    /**
     * Set SERVER remote address
     *
     * @param string $address
     * @return Enlight_Controller_Request_Request
     */
    public function setRemoteAddress($address)
    {
        $_SERVER['REMOTE_ADDR'] = $address;

        return $this;
    }

    /**
     * Sets HTTP host method
     *
     * @param string $host
     * @return Enlight_Controller_Request_Request
     */
    public function setHttpHost($host)
    {
        $_SERVER['HTTP_HOST'] = $host;
        return $this;
    }

    /**
     * Sets the REQUEST_URI on which the instance operates.
     *
     * If no request URI is passed, it uses the value in $_SERVER['REQUEST_URI'],
     * $_SERVER['HTTP_X_REWRITE_URL'], or $_SERVER['ORIG_PATH_INFO'] + $_SERVER['QUERY_STRING'].
     *
     * @param string $requestUri
     * @return Zend_Controller_Request_Http
     */
    public function setRequestUri($requestUri = null)
    {
        parent::setRequestUri($requestUri);
        if ($this->_requestUri === null
                && !empty($_SERVER['argc'])
                && $_SERVER['argc'] > 1) {
            $this->setRequestUri($_SERVER['argv'][1]);
        }
        return $this;
    }

    /**
     * Return the value of the given HTTP header. Pass the header name as the
     * plain, HTTP-specified header name. Ex.: Ask for 'Accept' to get the
     * Accept header, 'Accept-Encoding' to get the Accept-Encoding header.
     *
     * @param string $header HTTP header name
     * @return string|false HTTP header value, or false if not found
     * @throws Zend_Controller_Request_Exception
     */
    public function getHeader($header)
    {
        $temp = strtoupper(str_replace('-', '_', $header));
        if (isset($_SERVER['HTTP_' . $temp])) {
            return $_SERVER['HTTP_' . $temp];
        }

        if (strpos($temp, 'CONTENT_') === 0 && isset($_SERVER[$temp])) {
            return $_SERVER[$temp];
        }

        return parent::getHeader($header);
    }

    /**
     * Sets HTTP header method
     *
     * @param   string $header
     * @param   $value
     * @return  Enlight_Controller_Request_Request
     */
    public function setHeader($header, $value)
    {
        $temp = strtoupper(str_replace('-', '_', $header));
        $_SERVER['HTTP_' . $temp] = $value;
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getModuleName()
    {
        if (parent::getModuleName() === null) {
            return null;
        }

        return strtolower(trim(parent::getModuleName()));
    }

    /**
     * {@inheritdoc}
     */
    public function getClientIp($checkProxy = false)
    {
        return parent::getClientIp($checkProxy);
    }

    /**
     * {@inheritdoc}
     */
    public function getDeviceType()
    {
        $deviceType = strtolower($this->getCookie('x-ua-device', 'desktop'));

        return (in_array($deviceType, $this->validDeviceTypes)) ? $deviceType : 'desktop';
    }
}
