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

interface Enlight_Controller_Request_Request
{
    /**
     * Access values contained in the superglobals as public members
     * Order of precedence: 1. GET, 2. POST, 3. COOKIE, 4. SERVER, 5. ENV
     *
     * @see http://msdn.microsoft.com/en-us/library/system.web.httprequest.item.aspx
     *
     * @param string $key
     *
     * @return mixed
     */
    public function __get($key);

    /**
     * Set values
     *
     * In order to follow {@link __get()}, which operates on a number of
     * superglobals, setting values through overloading is not allowed and will
     * raise an exception. Use setParam() instead.
     *
     * @param string $key
     * @param mixed  $value
     *
     * @throws \Exception
     */
    public function __set($key, $value);

    /**
     * Check to see if a property is set
     *
     * @param string $key
     *
     * @return bool
     */
    public function __isset($key);

    /**
     * Retrieve the module name in lowercase
     *
     * @return string
     */
    public function getModuleName();

    /**
     * Set the module name to use
     *
     * @param string $value
     *
     * @return Enlight_Controller_Request_Request
     */
    public function setModuleName($value);

    /**
     * Retrieve the controller name
     *
     * @return string
     */
    public function getControllerName();

    /**
     * Set the controller name to use
     *
     * @param string $value
     *
     * @return Enlight_Controller_Request_Request
     */
    public function setControllerName($value);

    /**
     * Retrieve the action name
     *
     * @return string
     */
    public function getActionName();

    /**
     * Set the action name
     *
     * @param string $value
     *
     * @return Enlight_Controller_Request_Request
     */
    public function setActionName($value);

    /**
     * Retrieve the module key
     *
     * @return string
     */
    public function getModuleKey();

    /**
     * Set the module key
     *
     * @param string $key
     *
     * @return Enlight_Controller_Request_Request
     */
    public function setModuleKey($key);

    /**
     * Retrieve the controller key
     *
     * @return string
     */
    public function getControllerKey();

    /**
     * Set the controller key
     *
     * @param string $key
     *
     * @return Enlight_Controller_Request_Request
     */
    public function setControllerKey($key);

    /**
     * Retrieve the action key
     *
     * @return string
     */
    public function getActionKey();

    /**
     * Set the action key
     *
     * @param string $key
     *
     * @return Enlight_Controller_Request_Request
     */
    public function setActionKey($key);

    /**
     * Retrieve only user params (i.e, any param specific to the object and not the environment)
     *
     * @return array
     */
    public function getUserParams();

    /**
     * Retrieve a single user param (i.e, a param specific to the object and not the environment)
     *
     * @param string $key
     * @param string $default Default value to use if key not found
     *
     * @return mixed
     */
    public function getUserParam($key, $default = null);

    /**
     * Unset all user parameters
     *
     * @return Enlight_Controller_Request_Request
     */
    public function clearParams();

    /**
     * Set flag indicating whether or not request has been dispatched
     *
     * @param bool $flag
     *
     * @return Enlight_Controller_Request_Request
     */
    public function setDispatched($flag = true);

    /**
     * Determine if the request has been dispatched
     *
     * @return bool
     */
    public function isDispatched();

    /**
     * Alias to __get
     *
     * @param string $key
     *
     * @return mixed
     */
    public function get($key);

    /**
     * Alias to __set()
     *
     * @param string $key
     * @param mixed  $value
     */
    public function set($key, $value);

    /**
     * Alias to __isset()
     *
     * @param string $key
     *
     * @return bool
     */
    public function has($key);

    /**
     * Retrieve a member of the $_GET superglobal
     *
     * If no $key is passed, returns the entire $_GET array.
     *
     * @param string $key
     * @param mixed  $default Default value to use if key not found
     *
     * @return mixed Returns null if key does not exist
     */
    public function getQuery($key = null, $default = null);

    /**
     * Retrieve a member of the $_POST superglobal
     *
     * If no $key is passed, returns the entire $_POST array.
     *
     * @param string $key
     * @param mixed  $default Default value to use if key not found
     *
     * @return mixed Returns null if key does not exist
     */
    public function getPost($key = null, $default = null);

    /**
     * Retrieve a member of the $_COOKIE superglobal
     *
     * If no $key is passed, returns the entire $_COOKIE array.
     *
     * @param string $key
     * @param mixed  $default Default value to use if key not found
     *
     * @return mixed Returns null if key does not exist
     */
    public function getCookie($key = null, $default = null);

    /**
     * Retrieve a member of the $_SERVER superglobal
     *
     * If no $key is passed, returns the entire $_SERVER array.
     *
     * @param string $key
     * @param mixed  $default Default value to use if key not found
     *
     * @return mixed Returns null if key does not exist
     */
    public function getServer($key = null, $default = null);

    /**
     * Retrieve a member of the $_ENV superglobal
     *
     * If no $key is passed, returns the entire $_ENV array.
     *
     * @param string $key
     * @param mixed  $default Default value to use if key not found
     *
     * @return mixed Returns null if key does not exist
     */
    public function getEnv($key = null, $default = null);

    /**
     * Returns the REQUEST_URI taking into account
     * platform differences between Apache and IIS
     *
     * @return string
     */
    public function getRequestUri();

    /**
     * Set the base URL of the request; i.e., the segment leading to the script name
     *
     * E.g.:
     * - /admin
     * - /myapp
     * - /subdir/index.php
     *
     * Do not use the full URI when providing the base. The following are
     * examples of what not to use:
     * - http://example.com/admin (should be just /admin)
     * - http://example.com/subdir/index.php (should be just /subdir/index.php)
     *
     * If no $baseUrl is provided, attempts to determine the base URL from the
     * environment, using SCRIPT_FILENAME, SCRIPT_NAME, PHP_SELF, and
     * ORIG_SCRIPT_NAME in its determination.
     *
     * @param mixed $baseUrl
     *
     * @return Enlight_Controller_Request_Request
     */
    public function setBaseUrl($baseUrl = null);

    /**
     * Everything in REQUEST_URI before PATH_INFO
     * <form action="<?=$baseUrl?>/news/submit" method="POST"/>
     *
     * @param bool $raw
     *
     * @return string
     */
    public function getBaseUrl($raw = false);

    /**
     * Set the base path for the URL
     *
     * @param string|null $basePath
     *
     * @return Enlight_Controller_Request_Request
     */
    public function setBasePath($basePath = null);

    /**
     * Everything in REQUEST_URI before PATH_INFO not including the filename
     * <img src="<?=$basePath?>/images/zend.png"/>
     *
     * @return string
     */
    public function getBasePath();

    /**
     * Set the PATH_INFO string
     *
     * @param string|null $pathInfo
     *
     * @return Enlight_Controller_Request_Request
     */
    public function setPathInfo($pathInfo = null);

    /**
     * Returns everything between the BaseUrl and QueryString.
     * This value is calculated instead of reading PATH_INFO
     * directly from $_SERVER due to cross-platform differences.
     *
     * @return string
     */
    public function getPathInfo();

    /**
     * Set allowed parameter sources
     *
     * Can be empty array, or contain one or more of '_GET' or '_POST'.
     *
     * @param array $paramSources
     *
     * @return Enlight_Controller_Request_Request
     */
    public function setParamSources(array $paramSources = []);

    /**
     * Get list of allowed parameter sources
     *
     * @return array
     */
    public function getParamSources();

    /**
     * Set a userland parameter
     *
     * Uses $key to set a userland parameter.
     *
     * @param mixed $key
     * @param mixed $value
     *
     * @return Enlight_Controller_Request_Request
     */
    public function setParam($key, $value);

    /**
     * Retrieve a parameter
     *
     * Retrieves a parameter from the instance. Priority is in the order of
     * userland parameters (see {@link setParam()}), $_GET, $_POST. If a
     * parameter matching the $key is not found, null is returned.
     *
     * @param mixed $key
     * @param mixed $default Default value to use if key not found
     *
     * @return mixed
     */
    public function getParam($key, $default = null);

    /**
     * Retrieve an array of parameters
     *
     * Retrieves a merged array of parameters, with precedence of userland
     * params (see {@link setParam()}), $_GET, $_POST (i.e., values in the
     * userland params will take precedence over all others).
     *
     * @return array
     */
    public function getParams();

    /**
     * Set parameters
     *
     * Set one or more parameters. Parameters are set as userland parameters,
     * using the keys specified in the array.
     *
     * @param array $params
     *
     * @return Enlight_Controller_Request_Request
     */
    public function setParams(array $params);

    /**
     * Retrieve attributes derived from the request.
     *
     * The request "attributes" may be used to allow injection of any
     * parameters derived from the request: e.g., the results of path
     * match operations; the results of decrypting cookies; the results of
     * deserializing non-form-encoded message bodies; etc. Attributes
     * will be application and request specific, and CAN be mutable.
     *
     * @return mixed[] attributes derived from the request
     */
    public function getAttributes();

    /**
     * Retrieve a single derived request attribute.
     *
     * Retrieves a single derived request attribute as described in
     * getAttributes(). If the attribute has not been previously set, returns
     * the default value as provided.
     *
     * This method obviates the need for a hasAttribute() method, as it allows
     * specifying a default value to return if the attribute is not found.
     *
     * @see getAttributes()
     *
     * @param string $name    the attribute name
     * @param mixed  $default default value to return if the attribute does not exist
     *
     * @return mixed
     */
    public function getAttribute($name, $default = null);

    /**
     * This method allows setting a single derived request attribute as
     * described in getAttributes().
     *
     * @see getAttributes()
     *
     * @param string $name  the attribute name
     * @param mixed  $value the value of the attribute
     */
    public function setAttribute($name, $value);

    /**
     * This method allows removing a single derived request attribute as
     * described in getAttributes().
     *
     * @see getAttributes()
     *
     * @param string $name the attribute name
     */
    public function unsetAttribute($name);

    /**
     * Return the method by which the request was made
     *
     * @return string
     */
    public function getMethod();

    /**
     * Was the request made by POST?
     *
     * @return bool
     */
    public function isPost();

    /**
     * Was the request made by GET?
     *
     * @return bool
     */
    public function isGet();

    /**
     * Was the request made by PUT?
     *
     * @return bool
     */
    public function isPut();

    /**
     * Was the request made by DELETE?
     *
     * @return bool
     */
    public function isDelete();

    /**
     * Was the request made by HEAD?
     *
     * @return bool
     */
    public function isHead();

    /**
     * Was the request made by OPTIONS?
     *
     * @return bool
     */
    public function isOptions();

    /**
     * Is the request a Javascript XMLHttpRequest?
     *
     * Should work with Prototype/Script.aculo.us, possibly others.
     *
     * @return bool
     */
    public function isXmlHttpRequest();

    /**
     * Is this a Flash request?
     *
     * @return bool
     */
    public function isFlashRequest();

    /**
     * Is https secure request
     *
     * @return bool
     */
    public function isSecure();

    /**
     * Return the raw body of the request, if present
     *
     * @return string|false Raw body, or false if not present
     */
    public function getRawBody();

    /**
     * Get the request URI scheme
     *
     * @return string
     */
    public function getScheme();

    /**
     * Get the HTTP host.
     *
     * "Host" ":" host [ ":" port ] ; Section 3.2.2
     * Note the HTTP Host header is not the same as the URI host.
     * It includes the port while the URI host doesn't.
     *
     * @return string
     */
    public function getHttpHost();

    /**
     * Get the client's IP addres
     *
     * @return string
     */
    public function getClientIp();

    /**
     * Set GET values method
     *
     * @param string|array $spec
     * @param null|mixed   $value
     *
     * @return Enlight_Controller_Request_Request
     */
    public function setQuery($spec, $value = null);

    /**
     * Replace POST values
     *
     * @param array $data
     */
    public function replacePost($data);

    /**
     * Set POST values method
     *
     * @param string|array $spec
     * @param null|mixed   $value
     *
     * @return Enlight_Controller_Request_Request
     */
    public function setPost($spec, $value = null);

    /**
     * Sets the request URI scheme
     *
     * @param bool $value
     *
     * @return Enlight_Controller_Request_Request
     */
    public function setSecure($value = true);

    /**
     * Set SERVER remote address
     *
     * @param string $address
     *
     * @return Enlight_Controller_Request_Request
     */
    public function setRemoteAddress($address);

    /**
     * Sets HTTP host method
     *
     * @param string $host
     *
     * @return Enlight_Controller_Request_Request
     */
    public function setHttpHost($host);

    /**
     * Sets the REQUEST_URI on which the instance operates.
     *
     * If no request URI is passed, it uses the value in $_SERVER['REQUEST_URI'],
     * $_SERVER['HTTP_X_REWRITE_URL'], or $_SERVER['ORIG_PATH_INFO'] + $_SERVER['QUERY_STRING'].
     *
     * @param string $requestUri
     *
     * @return Enlight_Controller_Request_Request
     */
    public function setRequestUri($requestUri = null);

    /**
     * Return the value of the given HTTP header. Pass the header name as the
     * plain, HTTP-specified header name. Ex.: Ask for 'Accept' to get the
     * Accept header, 'Accept-Encoding' to get the Accept-Encoding header.
     *
     * @param string $header HTTP header name
     *
     * @throws \Exception
     *
     * @return string|false HTTP header value, or false if not found
     */
    public function getHeader($header);

    /**
     * Sets HTTP header method
     *
     * @param string $header
     * @param        $value
     *
     * @return Enlight_Controller_Request_Request
     */
    public function setHeader($header, $value);

    /**
     * Returns the current device type, or false if detection could not be done
     *
     * @return string
     */
    public function getDeviceType();
}
