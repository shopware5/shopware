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

class Enlight_Controller_Request_RequestTestCase extends Enlight_Controller_Request_RequestHttp
{
    /**
     * Valid request method types
     *
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
     *
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
     * @var string
     */
    private $clientIp;

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
        $this->query->replace([]);

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
        $this->request->replace([]);

        return $this;
    }

    /**
     * Set raw POST body
     *
     * @param string $content
     *
     * @return Enlight_Controller_Request_RequestTestCase
     */
    public function setRawBody($content)
    {
        $this->content = (string) $content;

        return $this;
    }

    /**
     * Get RAW POST body
     *
     * @return string|null
     */
    public function getRawBody()
    {
        return $this->content;
    }

    /**
     * Clear raw POST body
     *
     * @return Enlight_Controller_Request_RequestTestCase
     */
    public function clearRawBody()
    {
        $this->content = null;

        return $this;
    }

    /**
     * Set a cookie
     *
     * @param string $key
     * @param mixed  $value
     *
     * @return Enlight_Controller_Request_RequestTestCase
     */
    public function setCookie($key, $value)
    {
        $this->cookies->set($key, $value);
        $_COOKIE[$key] = $value;

        return $this;
    }

    /**
     * Set multiple cookies at once
     *
     * @param array $cookies
     *
     * @return self
     */
    public function setCookies(array $cookies)
    {
        foreach ($cookies as $key => $value) {
            $this->setCookie($key, $value);
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
        $this->cookies->replace([]);
        $_COOKIE = [];

        return $this;
    }

    /**
     * Set request method
     *
     * @param string $type
     *
     * @throws Exception
     *
     * @return Enlight_Controller_Request_RequestTestCase
     */
    public function setMethod($type)
    {
        $type = strtoupper(trim((string) $type));
        if (!in_array($type, $this->_validMethodTypes, true)) {
            throw new \Exception('Invalid request method specified');
        }

        $this->method = $type;

        return $this;
    }

    /**
     * Get request method
     *
     * @return string|null
     */
    public function getMethod()
    {
        return $this->method;
    }

    /**
     * Sets a request header.
     * The key will normalized for setting and retrieval.
     *
     * @param string $key
     * @param string $value
     *
     * @return Enlight_Controller_Request_RequestTestCase
     */
    public function setHeader($key, $value = null)
    {
        if ($value !== null) {
            $key = $this->_normalizeHeaderName($key);
            $this->headers->set($key, (string) $value);
        } else {
            $this->headers->remove($key);
        }
        $this->setServer('HTTP_' . $key, $value);

        return $this;
    }

    /**
     * Set request headers
     *
     * @param array $headers
     *
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
     * @param string $header
     * @param mixed  $default
     *
     * @return string|null
     */
    public function getHeader($header, $default = null)
    {
        $header = $this->_normalizeHeaderName($header);

        if ($this->headers->has($header)) {
            return $this->headers->get($header);
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
        return $this->headers->all();
    }

    /**
     * Clear request headers
     *
     * @return Enlight_Controller_Request_RequestTestCase
     */
    public function clearHeaders()
    {
        $this->headers->replace([]);

        return $this;
    }

    /**
     * {@inheritdoc}
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
     *
     * @return Enlight_Controller_Request_RequestTestCase
     */
    public function setHttpHost($host)
    {
        $this->setHeader('HOST', $host);

        return $this;
    }

    /**
     * Sets HTTP client method
     *
     * @param string $ip
     *
     * @return Enlight_Controller_Request_RequestTestCase
     */
    public function setClientIp($ip)
    {
        $this->clientIp = $ip;
        $this->setServer('REMOTE_ADDR', $ip);

        return $this;
    }

    /**
     * @return string
     */
    public function getClientIp()
    {
        if ($this->clientIp) {
            return $this->clientIp;
        }

        return parent::getClientIp();
    }

    /**
     * Sets a server param
     *
     * @param string $key
     * @param string $value
     *
     * @return Enlight_Controller_Request_RequestTestCase
     */
    public function setServer($key, $value = null)
    {
        $this->_serverParams[$key] = $value === null ? null : (string) $value;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getServer($key = null, $default = null)
    {
        if ($key === null) {
            return array_merge($_SERVER, $this->_serverParams);
        } elseif (isset($this->_serverParams[$key])) {
            return $this->_serverParams[$key] !== null ? $this->_serverParams[$key] : $default;
        } elseif ($this->server->has($key)) {
            return $this->server->get($key);
        }

        return $default;
    }

    /**
     * {@inheritdoc}
     */
    public function getDeviceType()
    {
        return $this->deviceType;
    }

    /**
     * Sets the current device type
     *
     * @param string $deviceType
     */
    public function setDeviceType($deviceType)
    {
        $deviceType = strtolower($deviceType);
        $this->deviceType = in_array($deviceType, $this->validDeviceTypes, true) ? $deviceType : 'desktop';
    }

    /**
     * Normalize a header name for setting and retrieval
     *
     * @param string $name
     *
     * @return string
     */
    protected function _normalizeHeaderName($name)
    {
        $name = strtoupper((string) $name);
        $name = str_replace('-', '_', $name);

        return $name;
    }
}
