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

use Symfony\Component\HttpFoundation\Request;

/**
 * This class is a wrapper for Symfony Request
 */
class Enlight_Controller_Request_RequestHttp extends Request implements Enlight_Controller_Request_Request
{
    /**
     * Scheme for http
     */
    public const SCHEME_HTTP = 'http';

    /**
     * Scheme for https
     */
    public const SCHEME_HTTPS = 'https';

    /**
     * @var string[]
     */
    protected $validDeviceTypes = [
        'desktop',
        'tablet',
        'mobile',
    ];

    /**
     * Has the action been dispatched?
     *
     * @var bool
     */
    protected $_dispatched = false;

    /**
     * Module
     *
     * @var string
     */
    protected $_module;

    /**
     * Module key for retrieving module from params
     *
     * @var string
     */
    protected $_moduleKey = 'module';

    /**
     * Controller
     *
     * @var string
     */
    protected $_controller;

    /**
     * Controller key for retrieving controller from params
     *
     * @var string
     */
    protected $_controllerKey = 'controller';

    /**
     * Action
     *
     * @var string
     */
    protected $_action;

    /**
     * Action key for retrieving action from params
     *
     * @var string
     */
    protected $_actionKey = 'action';

    /**
     * Request parameters
     *
     * @var array
     */
    protected $_params = [];

    /**
     * @var \Shopware\Components\DispatchFormatHelper
     */
    private $nameFormatter;

    /**
     * {@inheritdoc}
     */
    public function __get($key)
    {
        return $this->get($key);
    }

    /**
     * {@inheritdoc}
     */
    public function __set($key, $value)
    {
        $this->set($key, $value);
    }

    /**
     * {@inheritdoc}
     */
    public function __isset($key)
    {
        return $this->has($key);
    }

    /**
     * @return \Shopware\Components\DispatchFormatHelper
     */
    public function getNameFormatter()
    {
        if ($this->nameFormatter === null) {
            $this->nameFormatter = Shopware()->Container()->get('shopware.components.dispatch_format_helper');
        }

        return $this->nameFormatter;
    }

    /**
     * {@inheritdoc}
     */
    public function getAttribute($attribute, $default = null)
    {
        return $this->attributes->get($attribute, $default);
    }

    /**
     * {@inheritdoc}
     */
    public function getAttributes()
    {
        return $this->attributes->all();
    }

    /**
     * {@inheritdoc}
     */
    public function setAttribute($attribute, $value)
    {
        $this->attributes->set($attribute, $value);
    }

    /**
     * {@inheritdoc}
     */
    public function unsetAttribute($attribute)
    {
        $this->attributes->remove($attribute);
    }

    /**
     * {@inheritdoc}
     */
    public function setSecure($value = true)
    {
        $secure = $value ? 'on' : null;
        $_SERVER['HTTPS'] = $secure;
        $this->server->set('HTTPS', $secure);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function setRemoteAddress($address)
    {
        $_SERVER['REMOTE_ADDR'] = $address;
        $this->server->set('REMOTE_ADDR', $address);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function setHttpHost($host)
    {
        $_SERVER['HTTP_HOST'] = $host;
        $this->server->set('HOST', $host);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function setHeader($header, $value)
    {
        $temp = strtoupper(str_replace('-', '_', $header));
        $_SERVER['HTTP_' . $temp] = $value;
        $this->headers->set('HTTP_' . $temp, $value);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getModuleName()
    {
        if ($this->_module === null) {
            $module = $this->getParam($this->getModuleKey());
            if ($module) {
                $this->_module = strtolower($this->getNameFormatter()->formatNameForRequest($module));
            }
        }

        return $this->_module;
    }

    /**
     * {@inheritdoc}
     */
    public function getDeviceType()
    {
        $deviceType = strtolower($this->cookies->get('x-ua-device', 'desktop'));

        return in_array($deviceType, $this->validDeviceTypes) ? $deviceType : 'desktop';
    }

    /**
     * {@inheritdoc}
     */
    public function setModuleName($value)
    {
        $this->_module = strtolower(trim($value));

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getControllerName()
    {
        if ($this->_controller === null) {
            $this->_controller = $this->getNameFormatter()->formatNameForRequest($this->getParam($this->getControllerKey()), true);
        }

        return $this->_controller;
    }

    /**
     * {@inheritdoc}
     */
    public function setControllerName($value)
    {
        $this->_controller = $value;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getActionName()
    {
        if ($this->_action === null) {
            $this->_action = $this->getNameFormatter()->formatNameForRequest($this->getParam($this->getActionKey()));
        }

        return $this->_action;
    }

    /**
     * {@inheritdoc}
     */
    public function setActionName($value)
    {
        $this->_action = $value;
        /*
         * @see ZF-3465
         */
        if ($value === null) {
            $this->setParam($this->getActionKey(), $value);
        }

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getModuleKey()
    {
        return $this->_moduleKey;
    }

    /**
     * {@inheritdoc}
     */
    public function setModuleKey($key)
    {
        $this->_moduleKey = (string) $key;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getControllerKey()
    {
        return $this->_controllerKey;
    }

    /**
     * {@inheritdoc}
     */
    public function setControllerKey($key)
    {
        $this->_controllerKey = (string) $key;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getActionKey()
    {
        return $this->_actionKey;
    }

    /**
     * {@inheritdoc}
     */
    public function setActionKey($key)
    {
        $this->_actionKey = (string) $key;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getUserParams()
    {
        return $this->_params;
    }

    /**
     * {@inheritdoc}
     */
    public function getUserParam($key, $default = null)
    {
        if (isset($this->_params[$key])) {
            return $this->_params[$key];
        }

        return $default;
    }

    /**
     * {@inheritdoc}
     */
    public function setParam($key, $value)
    {
        $key = (string) $key;

        if (($value === null) && isset($this->_params[$key])) {
            unset($this->_params[$key]);
        } elseif ($value !== null) {
            $this->_params[$key] = $value;
        }

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function clearParams()
    {
        $this->_params = [];

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function setDispatched($flag = true)
    {
        $this->_dispatched = $flag ? true : false;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function isDispatched()
    {
        return $this->_dispatched;
    }

    /**
     * {@inheritdoc}
     */
    public function get($key, $default = null)
    {
        switch (true) {
            case isset($this->_params[$key]):
                return $this->_params[$key];
            case $this->query->has($key):
                return $this->query->get($key);
            case $this->request->has($key):
                return $this->request->get($key);
            case $this->cookies->has($key):
                return $this->cookies->get($key);
            case $key === 'REQUEST_URI':
                return $this->getRequestUri();
            case $key === 'PATH_INFO':
                return $this->getRequestUri();
            case $this->server->has($key):
                return $this->server->get($key);
            case isset($_ENV[$key]):
                return $_ENV[$key];
            default:
                return $default;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function set($key, $value)
    {
        throw new RuntimeException('Setting values in superglobals not allowed; please use setParam()');
    }

    /**
     * {@inheritdoc}
     */
    public function has($key)
    {
        switch (true) {
            case isset($this->_params[$key]):
                return true;
            case $this->query->has($key):
                return true;
            case $this->request->has($key):
                return true;
            case $this->cookies->has($key):
                return true;
            case $this->server->has($key):
                return true;
            case isset($_ENV[$key]):
                return true;
            default:
                return false;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function setQuery($spec, $value = null)
    {
        if (!is_array($spec) && $value === null) {
            unset($_GET[$spec]);
            $this->query->remove($spec);

            return $this;
        }

        if (is_array($spec) && empty($spec)) {
            $_GET = [];
            $this->query->replace([]);

            return $this;
        }

        if (($value === null) && !is_array($spec)) {
            throw new RuntimeException('Invalid value passed to setQuery(); must be either array of values or key/value pair');
        }
        if (($value === null) && is_array($spec)) {
            /** @var array $spec */
            foreach ($spec as $key => $value) {
                $this->setQuery($key, $value);
            }

            return $this;
        }

        $_GET[(string) $spec] = $value;
        $this->query->set((string) $spec, $value);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getQuery($key = null, $default = null)
    {
        if ($key === null) {
            return $_GET;
        }

        return isset($_GET[$key]) ? $_GET[$key] : $default;
    }

    /**
     * {@inheritdoc}
     */
    public function replacePost($data)
    {
        $_POST = $data;
        $this->request->replace($data);
    }

    /**
     * {@inheritdoc}
     */
    public function setPost($spec, $value = null)
    {
        if (!is_array($spec) && $value === null) {
            unset($_POST[$spec]);
            $this->request->remove($spec);

            return $this;
        }

        if (is_array($spec) && empty($spec)) {
            $_POST = [];
            $this->request->replace([]);

            return $this;
        }

        if (($value === null) && !is_array($spec)) {
            throw new RuntimeException('Invalid value passed to setPost(); must be either array of values or key/value pair');
        }
        if (($value === null) && is_array($spec)) {
            foreach ($spec as $key => $value) {
                $this->setPost($key, $value);
            }

            return $this;
        }
        $_POST[(string) $spec] = $value;
        $this->request->set((string) $spec, $value);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getPost($key = null, $default = null)
    {
        if ($key === null) {
            return $_POST;
        }

        return isset($_POST[$key]) ? $_POST[$key] : $default;
    }

    /**
     * {@inheritdoc}
     */
    public function getCookie($key = null, $default = null)
    {
        if ($key === null) {
            return $_COOKIE;
        }

        return isset($_COOKIE[$key]) ? $_COOKIE[$key] : $default;
    }

    /**
     * {@inheritdoc}
     */
    public function getServer($key = null, $default = null)
    {
        if ($key === null) {
            return $this->server->all();
        }

        return $this->server->get($key, $default);
    }

    /**
     * {@inheritdoc}
     */
    public function getEnv($key = null, $default = null)
    {
        if ($key === null) {
            return $_ENV;
        }

        return isset($_ENV[$key]) ? $_ENV[$key] : $default;
    }

    /**
     * {@inheritdoc}
     */
    public function setRequestUri($requestUri = null)
    {
        if ($requestUri) {
            $this->requestUri = $requestUri;
            $this->server->set('REQUEST_URI', $requestUri);

            // Needed only for Unit Tests
            $query = parse_url($requestUri, PHP_URL_QUERY);

            if (!empty($query)) {
                parse_str($query, $result);

                foreach ($result as $key => $value) {
                    $this->setQuery($key, $value);
                }
            }

            return $this;
        }

        $this->requestUri = $this->prepareRequestUri();

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function setBaseUrl($baseUrl = null)
    {
        if (($baseUrl !== null) && !is_string($baseUrl)) {
            return $this;
        }

        if ($baseUrl !== null) {
            $this->baseUrl = rtrim($baseUrl, '/');

            return $this;
        }

        $this->baseUrl = $this->prepareBaseUrl();

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getBaseUrl($raw = false)
    {
        if ($this->baseUrl === null) {
            $this->baseUrl = $this->prepareBaseUrl();
        }

        return ($raw == false) ? urldecode($this->baseUrl) : $this->baseUrl;
    }

    /**
     * {@inheritdoc}
     */
    public function setBasePath($basePath = null)
    {
        if ($basePath) {
            $this->basePath = $basePath;

            return $this;
        }

        $this->basePath = $this->prepareBasePath();

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function setPathInfo($pathInfo = null)
    {
        if ($pathInfo) {
            $this->pathInfo = $pathInfo;

            return $this;
        }

        $this->pathInfo = $this->preparePathInfo();

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function setParamSources(array $paramSources = [])
    {
        trigger_error(sprintf('%s:%s is deprecated since Shopware 5.6 and will be removed with 5.8. Will be removed without replacement.', __CLASS__, __METHOD__), E_USER_DEPRECATED);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getParamSources()
    {
        trigger_error(sprintf('%s:%s is deprecated since Shopware 5.6 and will be removed with 5.8. Will be removed without replacement.', __CLASS__, __METHOD__), E_USER_DEPRECATED);

        return ['_GET', '_POST'];
    }

    /**
     * {@inheritdoc}
     */
    public function getParam($key, $default = null)
    {
        if (isset($this->_params[$key])) {
            return $this->_params[$key];
        }

        if ($this->query->has($key) && $this->query->get($key) !== null) {
            return $this->query->get($key);
        }

        if ($this->request->has($key) && $this->request->get($key) !== null) {
            return $this->request->get($key);
        }

        return $default;
    }

    /**
     * {@inheritdoc}
     */
    public function getParams()
    {
        return $this->_params + $this->query->all() + $this->request->all();
    }

    /**
     * {@inheritdoc}
     */
    public function setParams(array $params)
    {
        foreach ($params as $key => $value) {
            $this->setParam($key, $value);
        }

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getMethod()
    {
        return $this->getServer('REQUEST_METHOD');
    }

    /**
     * {@inheritdoc}
     */
    public function isPost()
    {
        return $this->getMethod() === 'POST';
    }

    /**
     * {@inheritdoc}
     */
    public function isGet()
    {
        return $this->getMethod() === 'GET';
    }

    /**
     * {@inheritdoc}
     */
    public function isPut()
    {
        return $this->getMethod() === 'PUT';
    }

    /**
     * {@inheritdoc}
     */
    public function isDelete()
    {
        return $this->getMethod() === 'DELETE';
    }

    /**
     * {@inheritdoc}
     */
    public function isHead()
    {
        return $this->getMethod() === 'HEAD';
    }

    /**
     * {@inheritdoc}
     */
    public function isOptions()
    {
        return $this->getMethod() === 'OPTIONS';
    }

    /**
     * {@inheritdoc}
     */
    public function isFlashRequest()
    {
        trigger_error(sprintf('%s:%s is deprecated since Shopware 5.6 and will be removed with 5.8', __CLASS__, __METHOD__), E_USER_DEPRECATED);

        $header = strtolower($this->getHeader('USER_AGENT'));

        return strstr($header, ' flash') ? true : false;
    }

    /**
     * {@inheritdoc}
     */
    public function getRawBody()
    {
        return $this->getContent();
    }

    /**
     * {@inheritdoc}
     */
    public function getHeader($header)
    {
        $temp = strtoupper(str_replace('-', '_', $header));
        if ($this->server->has('HTTP_' . $temp)) {
            return $this->server->get('HTTP_' . $temp);
        }

        if (strpos($temp, 'CONTENT_') === 0 && $this->server->has($temp)) {
            return $this->server->get($temp);
        }

        if (empty($header)) {
            throw new RuntimeException('An HTTP header name is required');
        }

        // Try to get it from the $_SERVER array first
        $temp = 'HTTP_' . strtoupper(str_replace('-', '_', $header));
        if ($this->server->has($temp)) {
            return $this->server->get($temp);
        }

        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function getHttpHost()
    {
        $host = $this->getServer('HTTP_HOST');
        if (!empty($host)) {
            return $host;
        }

        $scheme = $this->getScheme();
        $name = $this->getServer('SERVER_NAME');
        $port = $this->getServer('SERVER_PORT');

        if ($name === null) {
            return '';
        } elseif (($scheme == self::SCHEME_HTTP && $port == 80) || ($scheme == self::SCHEME_HTTPS && $port == 443)) {
            return $name;
        }

        return $name . ':' . $port;
    }
}
