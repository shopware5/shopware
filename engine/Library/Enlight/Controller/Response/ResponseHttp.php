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

use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\Response;

class Enlight_Controller_Response_ResponseHttp extends Response implements Enlight_Controller_Response_Response
{
    /**
     * Flag; if true, when header operations are called after headers have been
     * sent, an exception will be raised; otherwise, processing will continue
     * as normal. Defaults to true.
     *
     * @see canSendHeaders()
     *
     * @var bool
     */
    public $headersSentThrowsException = true;

    /**
     * Exception stack
     *
     * @var Exception[]
     */
    protected $_exceptions = [];

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
        return $this->getContent();
    }

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
        $cookie = new Cookie(
            $name,
            $value,
            $expire,
            $path,
            $domain,
            $secure,
            $httpOnly
        );

        $this->headers->setCookie($cookie);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getCookies()
    {
        $cookies = [];

        foreach ($this->headers->getCookies() as $cookie) {
            $data = [
                'name' => $cookie->getName(),
                'value' => $cookie->getValue(),
                'expire' => $cookie->getExpiresTime(),
                'path' => $cookie->getPath(),
                'domain' => $cookie->getDomain(),
                'secure' => $cookie->isSecure(),
                'httpOnly' => $cookie->isHttpOnly(),
            ];

            $cookies[$cookie->getName() . '-' . $cookie->getPath()] = $data;

            if ($cookie->getPath() === '/') {
                $cookies[$cookie->getName() . '-'] = $data;
            }
        }

        return $cookies;
    }

    /**
     * @param string      $name
     * @param string|null $path
     */
    public function removeCookie($name, $path = null)
    {
        $this->headers->removeCookie($name, $path);
    }

    /**
     * {@inheritdoc}
     *
     * @deprecated since 5.6, will be removed with 5.8. Use sendHeaders instead
     */
    public function sendCookies()
    {
        trigger_error(__CLASS__ . ':' . __METHOD__ . ' is deprecated. Please use sendHeaders instead', E_USER_DEPRECATED);
        $this->sendHeaders();
    }

    /**
     * {@inheritdoc}
     */
    public function unsetExceptions()
    {
        $this->_exceptions = [];
    }

    /**
     * {@inheritdoc}
     */
    public function setHeader($name, $value, $replace = false)
    {
        $name = strtolower($name);
        $this->canSendHeaders(true);

        $this->headers->set($name, $value, $replace);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function setRedirect($url, $code = 302)
    {
        $this->canSendHeaders(true);

        $this->headers->set('Location', $url);
        $this->setHttpResponseCode($code);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getHeaders()
    {
        $headers = [];

        foreach ($this->headers->all() as $key => $header) {
            $keyFormatted = self::formatHeader($key);

            foreach ($header as $entry) {
                $headers[] = [
                    'name' => $key,
                    'value' => $entry,
                ];

                $headers[] = [
                    'name' => $keyFormatted,
                    'value' => $entry,
                ];
            }
        }

        return $headers;
    }

    /**
     * {@inheritdoc}
     */
    public function clearHeaders()
    {
        $this->headers->replace([]);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function clearHeader($name)
    {
        $this->headers->remove($name);

        return $this;
    }

    /**
     * {@inheritdoc}
     *
     * @deprecated since 5.6, will be removed in 5.8. Use setHeader instead
     */
    public function setRawHeader($value)
    {
        trigger_error(__CLASS__ . ':' . __METHOD__ . ' is deprecated. Please set $response->headers->set instead', E_USER_DEPRECATED);

        $parts = self::getRawHeaderParts($value);

        if (count($parts) !== 2) {
            throw new InvalidArgumentException(sprintf('Given Header "%s" is invalid', $value));
        }

        $this->headers->set($parts[0], $parts[1]);

        return $this;
    }

    /**
     * {@inheritdoc}
     *
     * @deprecated since 5.6, will be removed in 5.8. Use $response->headers->all() instead
     */
    public function getRawHeaders()
    {
        trigger_error(__CLASS__ . ':' . __METHOD__ . ' is deprecated. Please set $response->headers->all() instead', E_USER_DEPRECATED);

        return $this->headers->all();
    }

    /**
     * {@inheritdoc}
     *
     * @deprecated since 5.6, will be removed in 5.8. Use $response->headers->replace() instead
     */
    public function clearRawHeaders()
    {
        trigger_error(__CLASS__ . ':' . __METHOD__ . ' is deprecated. Please set $response->headers->replace() instead', E_USER_DEPRECATED);
        $this->headers->replace([]);

        return $this;
    }

    /**
     * {@inheritdoc}
     *
     * @deprecated since 5.6, will be removed in 5.8. Use clearHeader instead
     */
    public function clearRawHeader($headerRaw)
    {
        trigger_error(__CLASS__ . ':' . __METHOD__ . ' is deprecated. Please set clearRawHeader instead', E_USER_DEPRECATED);

        $parts = self::getRawHeaderParts($headerRaw);

        $this->headers->remove($parts[0]);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function clearAllHeaders()
    {
        $this->headers->replace([]);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function setHttpResponseCode($code)
    {
        $this->setStatusCode($code);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getHttpResponseCode()
    {
        return $this->getStatusCode();
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
    public function setBody($content, $name = null)
    {
        $this->content = $content;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function appendBody($content, $name = null)
    {
        $this->content .= $content;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function clearBody($name = null)
    {
        $this->content = null;

        return true;
    }

    public function isRedirect($location = null)
    {
        return \in_array($this->statusCode, [self::HTTP_MOVED_PERMANENTLY, self::HTTP_FOUND, self::HTTP_SEE_OTHER, self::HTTP_TEMPORARY_REDIRECT, self::HTTP_PERMANENTLY_REDIRECT]) && ($location === null ?: $location == $this->headers->get('Location'));
    }

    /**
     * {@inheritdoc}
     */
    public function getBody($spec = false)
    {
        return $this->content;
    }

    /**
     * {@inheritdoc}
     */
    public function append($name, $content)
    {
        $this->content .= $content;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function prepend($name, $content)
    {
        $this->content = $content . $this->content;

        return $this;
    }

    /**
     * {@inheritdoc}
     *
     * @deprecated since 5.6, will be removed in 5.8. Use getContent with echo instead
     */
    public function outputBody()
    {
        trigger_error(__CLASS__ . ':' . __METHOD__ . ' is deprecated. Please use getContent instead', E_USER_DEPRECATED);
        echo $this->getBody();
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
            if ($message === $e->getMessage()) {
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
            if ($code === $e->getCode()) {
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
            if ($message === $e->getMessage()) {
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
        $code = (int) $code;
        $exceptions = [];
        foreach ($this->_exceptions as $e) {
            if ($code === $e->getCode()) {
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
     *
     * @deprecated since 5.6, will be removed in 5.8. Use showException in config.php instead
     */
    public function renderExceptions($flag = null)
    {
        trigger_error(__CLASS__ . ':' . __METHOD__ . ' is deprecated without replacement', E_USER_DEPRECATED);

        return false;
    }

    /**
     * @deprecated since 5.6, will be removed in 5.8. Use send instead
     */
    public function sendResponse()
    {
        trigger_error(__CLASS__ . ':' . __METHOD__ . ' is deprecated. Please set send instead', E_USER_DEPRECATED);
        $this->sendHeaders();
        $this->sendContent();
    }

    private static function getRawHeaderParts(string $name): array
    {
        return array_map('trim', explode(':', $name, 2));
    }

    private static function formatHeader(string $header): string
    {
        return implode('-', array_map('ucfirst', explode('-', $header)));
    }
}
