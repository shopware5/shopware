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
 * This class is highly based on Zend_Controller_Response_Http
 *
 * @link https://github.com/zendframework/zf1/blob/release-1.12.20/library/Zend/Controller/Response/Http.php
 * @link https://github.com/zendframework/zf1/blob/release-1.12.20/library/Zend/Controller/Response/Abstract.php
 */
class Enlight_Controller_Response_ResponseHttp implements Enlight_Controller_Response_Response
{
    /**
     * @var array Contains all cookies, which have been set by the "setCookie" function.
     */
    protected $_cookies = [];

    /**
     * Body content
     * @var array
     */
    protected $_body = [];

    /**
     * Exception stack
     * @var Exception[]
     */
    protected $_exceptions = [];

    /**
     * Array of headers. Each header is an array with keys 'name' and 'value'
     * @var array
     */
    protected $_headers = [];

    /**
     * Array of raw headers. Each header is a single string, the entire header to emit
     * @var array
     */
    protected $_headersRaw = [];

    /**
     * HTTP response code to use in headers
     * @var int
     */
    protected $_httpResponseCode = 200;

    /**
     * Flag; is this response a redirect?
     * @var boolean
     */
    protected $_isRedirect = false;

    /**
     * Whether or not to render exceptions; off by default
     * @var boolean
     */
    protected $_renderExceptions = false;

    /**
     * Flag; if true, when header operations are called after headers have been
     * sent, an exception will be raised; otherwise, processing will continue
     * as normal. Defaults to true.
     *
     * @see canSendHeaders()
     * @var boolean
     */
    public $headersSentThrowsException = true;

    /**
     * {@inheritdoc}
     */
    public function setCookie(
        $name,
        $value = null,
        $expire = 0,
        $path = null,
        $domain = null,
        $secure = false,
        $httpOnly = false
    ) {
        $this->_cookies[$name] = [
            'value' => $value,
            'expire' => $expire,
            'path' => $path,
            'domain' => $domain,
            'secure' => $secure,
            'httpOnly' => $httpOnly
        ];

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getCookies()
    {
        return $this->_cookies;
    }

    /**
     * {@inheritdoc}
     */
    public function sendCookies()
    {
        if (!empty($this->_cookies)) {
            $this->canSendHeaders(true);
            foreach ($this->_cookies as $name => $cookie) {
                setcookie(
                    $name,
                    $cookie['value'],
                    $cookie['expire'],
                    $cookie['path'],
                    $cookie['domain'],
                    $cookie['secure'],
                    $cookie['httpOnly']
                );
            }
        }

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function unsetExceptions()
    {
        $this->_exceptions = [];
    }


    /**
     * Normalize a header name
     *
     * Normalizes a header name to X-Capitalized-Names
     *
     * @param  string $name
     * @return string
     */
    protected function _normalizeHeader($name)
    {
        $filtered = str_replace(['-', '_'], ' ', (string) $name);
        $filtered = ucwords(strtolower($filtered));
        $filtered = str_replace(' ', '-', $filtered);
        return $filtered;
    }

    /**
     * {@inheritdoc}
     */
    public function setHeader($name, $value, $replace = false)
    {
        $this->canSendHeaders(true);
        $name  = $this->_normalizeHeader($name);
        $value = (string) $value;

        if ($replace) {
            foreach ($this->_headers as $key => $header) {
                if ($name == $header['name']) {
                    unset($this->_headers[$key]);
                }
            }
        }

        $this->_headers[] = [
            'name'    => $name,
            'value'   => $value,
            'replace' => $replace
        ];

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function setRedirect($url, $code = 302)
    {
        $this->canSendHeaders(true);
        $this->setHeader('Location', $url, true)
            ->setHttpResponseCode($code);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function isRedirect()
    {
        return $this->_isRedirect;
    }

    /**
     * {@inheritdoc}
     */
    public function getHeaders()
    {
        return $this->_headers;
    }

    /**
     * {@inheritdoc}
     */
    public function clearHeaders()
    {
        $this->_headers = [];

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function clearHeader($name)
    {
        if (! count($this->_headers)) {
            return $this;
        }

        foreach ($this->_headers as $index => $header) {
            if ($name == $header['name']) {
                unset($this->_headers[$index]);
            }
        }

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function setRawHeader($value)
    {
        $this->canSendHeaders(true);
        if ('Location' == substr($value, 0, 8)) {
            $this->_isRedirect = true;
        }
        $this->_headersRaw[] = (string) $value;
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getRawHeaders()
    {
        return $this->_headersRaw;
    }

    /**
     * {@inheritdoc}
     */
    public function clearRawHeaders()
    {
        $this->_headersRaw = [];
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function clearRawHeader($headerRaw)
    {
        if (! count($this->_headersRaw)) {
            return $this;
        }

        $key = array_search($headerRaw, $this->_headersRaw);
        if ($key !== false) {
            unset($this->_headersRaw[$key]);
        }

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function clearAllHeaders()
    {
        return $this->clearHeaders()
            ->clearRawHeaders();
    }

    /**
     * {@inheritdoc}
     */
    public function setHttpResponseCode($code)
    {
        if (!is_int($code) || (100 > $code) || (599 < $code)) {
            throw new RuntimeException('Invalid HTTP response code');
        }

        if ((300 <= $code) && (307 >= $code)) {
            $this->_isRedirect = true;
        } else {
            $this->_isRedirect = false;
        }

        $this->_httpResponseCode = $code;
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getHttpResponseCode()
    {
        return $this->_httpResponseCode;
    }

    /**
     * {@inheritdoc}
     */
    public function canSendHeaders($throw = false)
    {
        $ok = headers_sent($file, $line);
        if ($ok && $throw && $this->headersSentThrowsException) {
            throw new RuntimeException('Cannot send headers; headers already sent in ' . $file . ', line ' . $line);
        }

        return !$ok;
    }

    /**
     * {@inheritdoc}
     */
    public function sendHeaders()
    {
        $this->sendCookies();

        // Only check if we can send headers if we have headers to send
        if (count($this->_headersRaw) || count($this->_headers) || (200 != $this->_httpResponseCode)) {
            $this->canSendHeaders(true);
        } elseif (200 == $this->_httpResponseCode) {
            // Haven't changed the response code, and we have no headers
            return $this;
        }

        $httpCodeSent = false;

        foreach ($this->_headersRaw as $header) {
            if (!$httpCodeSent && $this->_httpResponseCode) {
                header($header, true, $this->_httpResponseCode);
                $httpCodeSent = true;
            } else {
                header($header);
            }
        }

        foreach ($this->_headers as $header) {
            if (!$httpCodeSent && $this->_httpResponseCode) {
                header($header['name'] . ': ' . $header['value'], $header['replace'], $this->_httpResponseCode);
                $httpCodeSent = true;
            } else {
                header($header['name'] . ': ' . $header['value'], $header['replace']);
            }
        }

        if (!$httpCodeSent) {
            header('HTTP/1.1 ' . $this->_httpResponseCode);
        }

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function setBody($content, $name = null)
    {
        if ((null === $name) || !is_string($name)) {
            $this->_body = ['default' => (string) $content];
        } else {
            $this->_body[$name] = (string) $content;
        }

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function appendBody($content, $name = null)
    {
        if ((null === $name) || !is_string($name)) {
            if (isset($this->_body['default'])) {
                $this->_body['default'] .= (string) $content;
            } else {
                return $this->append('default', $content);
            }
        } elseif (isset($this->_body[$name])) {
            $this->_body[$name] .= (string) $content;
        } else {
            return $this->append($name, $content);
        }

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function clearBody($name = null)
    {
        if (null !== $name) {
            $name = (string) $name;
            if (isset($this->_body[$name])) {
                unset($this->_body[$name]);
                return true;
            }

            return false;
        }

        $this->_body = [];
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function getBody($spec = false)
    {
        if (false === $spec) {
            ob_start();
            $this->outputBody();
            return ob_get_clean();
        } elseif (true === $spec) {
            return $this->_body;
        } elseif (is_string($spec) && isset($this->_body[$spec])) {
            return $this->_body[$spec];
        }

        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function append($name, $content)
    {
        if (!is_string($name)) {
            throw new RuntimeException('Invalid body segment key ("' . gettype($name) . '")');
        }

        if (isset($this->_body[$name])) {
            unset($this->_body[$name]);
        }
        $this->_body[$name] = (string) $content;
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function prepend($name, $content)
    {
        if (!is_string($name)) {
            throw new RuntimeException('Invalid body segment key ("' . gettype($name) . '")');
        }

        if (isset($this->_body[$name])) {
            unset($this->_body[$name]);
        }

        $new = [$name => (string) $content];
        $this->_body = $new + $this->_body;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function insert($name, $content, $parent = null, $before = false)
    {
        if (!is_string($name)) {
            throw new RuntimeException('Invalid body segment key ("' . gettype($name) . '")');
        }

        if ((null !== $parent) && !is_string($parent)) {
            throw new RuntimeException('Invalid body segment parent key ("' . gettype($parent) . '")');
        }

        if (isset($this->_body[$name])) {
            unset($this->_body[$name]);
        }

        if ((null === $parent) || !isset($this->_body[$parent])) {
            return $this->append($name, $content);
        }

        $ins  = [$name => (string) $content];
        $keys = array_keys($this->_body);
        $loc  = array_search($parent, $keys);
        if (!$before) {
            // Increment location if not inserting before
            ++$loc;
        }

        if (0 === $loc) {
            // If location of key is 0, we're prepending
            $this->_body = $ins + $this->_body;
        } elseif ($loc >= count($this->_body)) {
            // If location of key is maximal, we're appending
            $this->_body = $this->_body + $ins;
        } else {
            // Otherwise, insert at location specified
            $pre  = array_slice($this->_body, 0, $loc);
            $post = array_slice($this->_body, $loc);
            $this->_body = $pre + $ins + $post;
        }

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function outputBody()
    {
        $body = implode('', $this->_body);
        echo $body;
    }

    /**
     * {@inheritdoc}
     */
    public function setException(Exception $e)
    {
        $this->_exceptions[] = $e;
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getException()
    {
        return $this->_exceptions;
    }

    /**
     * {@inheritdoc}
     */
    public function isException()
    {
        return !empty($this->_exceptions);
    }

    /**
     * {@inheritdoc}
     */
    public function hasExceptionOfType($type)
    {
        foreach ($this->_exceptions as $e) {
            if ($e instanceof $type) {
                return true;
            }
        }

        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function hasExceptionOfMessage($message)
    {
        foreach ($this->_exceptions as $e) {
            if ($message == $e->getMessage()) {
                return true;
            }
        }

        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function hasExceptionOfCode($code)
    {
        $code = (int) $code;
        foreach ($this->_exceptions as $e) {
            if ($code == $e->getCode()) {
                return true;
            }
        }

        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function getExceptionByType($type)
    {
        $exceptions = [];
        foreach ($this->_exceptions as $e) {
            if ($e instanceof $type) {
                $exceptions[] = $e;
            }
        }

        if (empty($exceptions)) {
            $exceptions = false;
        }

        return $exceptions;
    }

    /**
     * {@inheritdoc}
     */
    public function getExceptionByMessage($message)
    {
        $exceptions = [];
        foreach ($this->_exceptions as $e) {
            if ($message == $e->getMessage()) {
                $exceptions[] = $e;
            }
        }

        if (empty($exceptions)) {
            $exceptions = false;
        }

        return $exceptions;
    }

    /**
     * {@inheritdoc}
     */
    public function getExceptionByCode($code)
    {
        $code       = (int) $code;
        $exceptions = [];
        foreach ($this->_exceptions as $e) {
            if ($code == $e->getCode()) {
                $exceptions[] = $e;
            }
        }

        if (empty($exceptions)) {
            $exceptions = false;
        }

        return $exceptions;
    }

    /**
     * {@inheritdoc}
     */
    public function renderExceptions($flag = null)
    {
        if (null !== $flag) {
            $this->_renderExceptions = $flag ? true : false;
        }

        return $this->_renderExceptions;
    }

    /**
     * {@inheritdoc}
     */
    public function sendResponse()
    {
        $this->sendHeaders();

        if ($this->isException() && $this->renderExceptions()) {
            $exceptions = '';
            foreach ($this->getException() as $e) {
                $exceptions .= $e->__toString() . "\n";
            }
            echo $exceptions;
            return;
        }

        $this->outputBody();
    }

    /**
     * Magic __toString functionality
     *
     * Proxies to {@link sendResponse()} and returns response value as string
     * using output buffering.
     *
     * @return string
     */
    public function __toString()
    {
        ob_start();
        $this->sendResponse();
        return ob_get_clean();
    }
}
