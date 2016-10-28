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

interface Enlight_Controller_Response_Response
{
    /**
     * Sets a cookie method
     *
     * @param string $name
     * @param string $value
     * @param int $expire
     * @param string $path
     * @param string $domain
     * @param bool $secure
     * @param bool $httpOnly
     * @return \Enlight_Controller_Response_Response* @link http://www.php.net/manual/de/function.setcookie.php
     */
    public function setCookie($name, $value = null, $expire = 0, $path = null, $domain = null, $secure = false, $httpOnly = false);

    /**
     * @return array
     */
    public function getCookies();

    /**
     * Sends all cookies
     *
     * @return \Enlight_Controller_Response_Response
     */
    public function sendCookies();

    /**
     *
     */
    public function unsetExceptions();

    /**
     * Set a header
     *
     * If $replace is true, replaces any headers already defined with that
     * $name.
     *
     * @param string $name
     * @param string $value
     * @param boolean $replace
     * @return \Enlight_Controller_Response_Response
     */
    public function setHeader($name, $value, $replace = false);

    /**
     * Set redirect URL
     *
     * Sets Location header and response code. Forces replacement of any prior
     * redirects.
     *
     * @param string $url
     * @param int $code
     * @return \Enlight_Controller_Response_Response
     */
    public function setRedirect($url, $code = 302);

    /**
     * Is this a redirect?
     *
     * @return boolean
     */
    public function isRedirect();

    /**
     * Return array of headers; see {@link $_headers} for format
     *
     * @return array
     */
    public function getHeaders();

    /**
     * Clear headers
     *
     * @return \Enlight_Controller_Response_Response
     */
    public function clearHeaders();

    /**
     * Clears the specified HTTP header
     *
     * @param  string $name
     * @return \Enlight_Controller_Response_Response
     */
    public function clearHeader($name);

    /**
     * Set raw HTTP header
     *
     * Allows setting non key => value headers, such as status codes
     *
     * @param string $value
     * @return \Enlight_Controller_Response_Response
     */
    public function setRawHeader($value);

    /**
     * Retrieve all {@link setRawHeader() raw HTTP headers}
     *
     * @return array
     */
    public function getRawHeaders();

    /**
     * Clear all {@link setRawHeader() raw HTTP headers}
     *
     * @return \Enlight_Controller_Response_Response
     */
    public function clearRawHeaders();

    /**
     * Clears the specified raw HTTP header
     *
     * @param  string $headerRaw
     * @return \Enlight_Controller_Response_Response
     */
    public function clearRawHeader($headerRaw);

    /**
     * Clear all headers, normal and raw
     *
     * @return \Enlight_Controller_Response_Response
     */
    public function clearAllHeaders();

    /**
     * Set HTTP response code to use with headers
     *
     * @param int $code
     * @return \Enlight_Controller_Response_Response
     */
    public function setHttpResponseCode($code);

    /**
     * Retrieve HTTP response code
     *
     * @return int
     */
    public function getHttpResponseCode();

    /**
     * Can we send headers?
     *
     * @param boolean $throw Whether or not to throw an exception if headers have been sent; defaults to false
     * @return boolean
     * @throws RuntimeException
     */
    public function canSendHeaders($throw = false);

    /**
     * Send all headers
     *
     * Sends any headers specified. If an {@link setHttpResponseCode() HTTP response code}
     * has been specified, it is sent with the first header.
     *
     * @return \Enlight_Controller_Response_Response
     */
    public function sendHeaders();

    /**
     * Set body content
     *
     * If $name is not passed, or is not a string, resets the entire body and
     * sets the 'default' key to $content.
     *
     * If $name is a string, sets the named segment in the body array to
     * $content.
     *
     * @param string $content
     * @param null|string $name
     * @return \Enlight_Controller_Response_Response
     */
    public function setBody($content, $name = null);

    /**
     * Append content to the body content
     *
     * @param string $content
     * @param null|string $name
     * @return \Enlight_Controller_Response_Response
     */
    public function appendBody($content, $name = null);

    /**
     * Clear body array
     *
     * With no arguments, clears the entire body array. Given a $name, clears
     * just that named segment; if no segment matching $name exists, returns
     * false to indicate an error.
     *
     * @param  string $name Named segment to clear
     * @return boolean
     */
    public function clearBody($name = null);

    /**
     * Return the body content
     *
     * If $spec is false, returns the concatenated values of the body content
     * array. If $spec is boolean true, returns the body content array. If
     * $spec is a string and matches a named segment, returns the contents of
     * that segment; otherwise, returns null.
     *
     * @param boolean $spec
     * @return string|array|null
     */
    public function getBody($spec = false);

    /**
     * Append a named body segment to the body content array
     *
     * If segment already exists, replaces with $content and places at end of
     * array.
     *
     * @param string $name
     * @param string $content
     * @return \Enlight_Controller_Response_Response
     */
    public function append($name, $content);

    /**
     * Prepend a named body segment to the body content array
     *
     * If segment already exists, replaces with $content and places at top of
     * array.
     *
     * @param string $name
     * @param string $content
     */
    public function prepend($name, $content);

    /**
     * Insert a named segment into the body content array
     *
     * @param  string $name
     * @param  string $content
     * @param  string $parent
     * @param  boolean $before Whether to insert the new segment before or
     * after the parent. Defaults to false (after)
     * @return \Enlight_Controller_Response_Response
     */
    public function insert($name, $content, $parent = null, $before = false);

    /**
     * Echo the body segments
     */
    public function outputBody();

    /**
     * Register an exception with the response
     *
     * @param Exception $e
     * @return \Enlight_Controller_Response_Response
     */
    public function setException(Exception $e);

    /**
     * Retrieve the exception stack
     *
     * @return \Exception[]
     */
    public function getException();

    /**
     * Has an exception been registered with the response?
     *
     * @return boolean
     */
    public function isException();

    /**
     * Does the response object contain an exception of a given type?
     *
     * @param  string $type
     * @return boolean
     */
    public function hasExceptionOfType($type);

    /**
     * Does the response object contain an exception with a given message?
     *
     * @param  string $message
     * @return boolean
     */
    public function hasExceptionOfMessage($message);

    /**
     * Does the response object contain an exception with a given code?
     *
     * @param  int $code
     * @return boolean
     */
    public function hasExceptionOfCode($code);

    /**
     * Retrieve all exceptions of a given type
     *
     * @param  string $type
     * @return false|array
     */
    public function getExceptionByType($type);

    /**
     * Retrieve all exceptions of a given message
     *
     * @param  string $message
     * @return false|array
     */
    public function getExceptionByMessage($message);

    /**
     * Retrieve all exceptions of a given code
     *
     * @param mixed $code
     */
    public function getExceptionByCode($code);

    /**
     * Whether or not to render exceptions (off by default)
     *
     * If called with no arguments or a null argument, returns the value of the
     * flag; otherwise, sets it and returns the current value.
     *
     * @param boolean $flag Optional
     * @return boolean
     */
    public function renderExceptions($flag = null);

    /**
     * Send the response, including all headers, rendering exceptions if so
     * requested.
     */
    public function sendResponse();
}
