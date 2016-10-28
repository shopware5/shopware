<?php
/**
 * Enlight
 *
 * LICENSE
 *
 * This source file is subject to the new BSD license that is bundled
 * with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/bsd-license.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@shopware.de so we can send you a copy immediately.
 *
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @copyright  Copyright (c) 2011, shopware AG (http://www.shopware.de)
 * @license    http://opensource.org/licenses/bsd-license.php New BSD License
 */

/**
 * This class is highly based on Zend_Controller_Request_HttpTestCasep
 *
 * @link https://github.com/zendframework/zf1/blob/release-1.12.20/library/Zend/Controller/Request/HttpTestCase.php
 */
class Enlight_Controller_Request_RequestTestCase extends Enlight_Controller_Request_RequestHttp
{
    /**
     * Request headers
     * @var array
     */
    protected $_headers = [];

    /**
     * Request method
     * @var string
     */
    protected $_method = 'GET';

    /**
     * Valid request method types
     * @var array
     */
    protected $_validMethodTypes = [
        'DELETE',
        'GET',
        'HEAD',
        'OPTIONS',
        'POST',
        'PUT',
    ];

    /**
     * Server params
     * @var array
     */
    protected $_serverParams = [];

    /**
     * See: getDeviceType()
     *
     * @var string
     */
    private $deviceType = 'desktop';

    /**
     * Clear the global state
     */
    public function clearAll()
    {
        $this->clearCookies();
        $this->clearHeaders();
        $this->clearParams();
        $this->clearPost();
        $this->clearQuery();
        $this->clearRawBody();
    }

    /**
     * Clear GET values
     *
     * @return Enlight_Controller_Request_RequestTestCase
     */
    public function clearQuery()
    {
        $_GET = [];
        return $this;
    }

    /**
     * Clear POST values
     *
     * @return Enlight_Controller_Request_RequestTestCase
     */
    public function clearPost()
    {
        $_POST = [];
        return $this;
    }

    /**
     * Set raw POST body
     *
     * @param  string $content
     * @return Enlight_Controller_Request_RequestTestCase
     */
    public function setRawBody($content)
    {
        $this->_rawBody = (string) $content;
        return $this;
    }

    /**
     * Get RAW POST body
     *
     * @return string|null
     */
    public function getRawBody()
    {
        return $this->_rawBody;
    }

    /**
     * Clear raw POST body
     *
     * @return Enlight_Controller_Request_RequestTestCase
     */
    public function clearRawBody()
    {
        $this->_rawBody = null;
        return $this;
    }

    /**
     * Set a cookie
     *
     * @param  string $key
     * @param  mixed $value
     * @return Enlight_Controller_Request_RequestTestCase
     */
    public function setCookie($key, $value)
    {
        $_COOKIE[(string) $key] = $value;
        return $this;
    }

    /**
     * Set multiple cookies at once
     *
     * @param array $cookies
     * @return self
     */
    public function setCookies(array $cookies)
    {
        foreach ($cookies as $key => $value) {
            $_COOKIE[$key] = $value;
        }
        return $this;
    }

    /**
     * Clear all cookies
     *
     * @return Enlight_Controller_Request_RequestTestCase
     */
    public function clearCookies()
    {
        $_COOKIE = [];
        return $this;
    }

    /**
     * Set request method
     *
     * @param  string $type
     * @return Enlight_Controller_Request_RequestTestCase
     * @throws Exception
     */
    public function setMethod($type)
    {
        $type = strtoupper(trim((string) $type));
        if (!in_array($type, $this->_validMethodTypes)) {
            throw new \Exception('Invalid request method specified');
        }
        $this->_method = $type;
        return $this;
    }

    /**
     * Get request method
     *
     * @return string|null
     */
    public function getMethod()
    {
        return $this->_method;
    }

    /**
     * Sets a request header.
     * The key will normalized for setting and retrieval.
     * @param  string $key
     * @param  string $value
     * @return Enlight_Controller_Request_RequestTestCase
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


    /**
     * Set request headers
     *
     * @param  array $headers
     * @return Enlight_Controller_Request_RequestTestCase
     */
    public function setHeaders(array $headers)
    {
        foreach ($headers as $key => $value) {
            $this->setHeader($key, $value);
        }
        return $this;
    }

    /**
     * Get request header
     *
     * @param  string $header
     * @param  mixed $default
     * @return string|null
     */
    public function getHeader($header, $default = null)
    {
        $header = $this->_normalizeHeaderName($header);
        if (array_key_exists($header, $this->_headers)) {
            return $this->_headers[$header];
        }
        return $default;
    }

    /**
     * Get all request headers
     *
     * @return array
     */
    public function getHeaders()
    {
        return $this->_headers;
    }

    /**
     * Clear request headers
     *
     * @return Enlight_Controller_Request_RequestTestCase
     */
    public function clearHeaders()
    {
        $this->_headers = [];
        return $this;
    }

    /**
     * Get REQUEST_URI
     *
     * @return null|string
     */
    public function getRequestUri()
    {
        return $this->_requestUri;
    }

    /**
     * Normalize a header name for setting and retrieval
     *
     * @param  string $name
     * @return string
     */
    protected function _normalizeHeaderName($name)
    {
        $name = strtoupper((string) $name);
        $name = str_replace('-', '_', $name);
        return $name;
    }


    /**
     * Set GET values method
     *
     * @param  string|array $spec
     * @param  null|mixed   $value
     * @return Enlight_Controller_Request_Request
     */
    public function setQuery($spec, $value = null)
    {
        if (!is_array($spec) && $value === null) {
            unset($_GET[$spec]);
            return $this;
        } elseif (is_array($spec) && empty($spec)) {
            $_GET = [];
            return $this;
        }
        return parent::setQuery($spec, $value);
    }

    /**
     * Set POST values method
     *
     * @param  string|array $spec
     * @param  null|mixed   $value
     * @return Enlight_Controller_Request_Request
     */
    public function setPost($spec, $value = null)
    {
        if (!is_array($spec) && $value === null) {
            unset($_POST[$spec]);
            return $this;
        } elseif (is_array($spec) && empty($spec)) {
            $_POST = [];
            return $this;
        }

        return parent::setPost($spec, $value);
    }

    /**
     * Set SERVER remote address
     *
     * @param string $address
     * @return Enlight_Controller_Request_Request
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
     * @return Enlight_Controller_Request_RequestTestCase
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
     * @return Enlight_Controller_Request_RequestTestCase
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
     * @return Enlight_Controller_Request_RequestTestCase
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
     * Sets the current device type
     * @param string $deviceType
     */
    public function setDeviceType($deviceType)
    {
        $deviceType = strtolower($deviceType);

        $this->deviceType = in_array($deviceType, $this->validDeviceTypes) ? $deviceType : 'desktop';
    }
}
