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
 * This class is highly based on Zend_Controller_Response_HttpTestCase
 *
 * @link https://github.com/zendframework/zf1/blob/release-1.12.20/library/Zend/Controller/Response/HttpTestCase.php
 */
class Enlight_Controller_Response_ResponseTestCase extends Enlight_Controller_Response_ResponseHttp
{
    /**
     * Sends all cookies
     *
     * @return Enlight_Controller_Response_Response
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
     * @return mixed
     */
    public function getCookie($name, $default = null)
    {
        return isset($this->_cookies[$name]['value']) ? $this->_cookies[$name]['value'] : $default;
    }

    /**
     * Gets all the information for a cookie
     *
     * @param string $name
     * @return mixed
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
     * @return mixed
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
     * "send" headers by returning array of all headers that would be sent
     *
     * @return array
     */
    public function sendHeaders()
    {
        $headers = [];
        foreach ($this->_headersRaw as $header) {
            $headers[] = $header;
        }
        foreach ($this->_headers as $header) {
            $name = $header['name'];
            $key  = strtolower($name);
            if (array_key_exists($name, $headers)) {
                if ($header['replace']) {
                    $headers[$key] = $header['name'] . ': ' . $header['value'];
                }
            } else {
                $headers[$key] = $header['name'] . ': ' . $header['value'];
            }
        }
        return $headers;
    }

    /**
     * Can we send headers?
     *
     * @param  bool $throw
     * @return bool
     */
    public function canSendHeaders($throw = false)
    {
        return true;
    }

    /**
     * Return the concatenated body segments
     *
     * @return string
     */
    public function outputBody()
    {
        $fullContent = '';
        foreach ($this->_body as $content) {
            $fullContent .= $content;
        }
        return $fullContent;
    }

    /**
     * Get body and/or body segments
     *
     * @param  bool|string $spec
     * @return string|array|null
     */
    public function getBody($spec = false)
    {
        if (false === $spec) {
            return $this->outputBody();
        } elseif (true === $spec) {
            return $this->_body;
        } elseif (is_string($spec) && isset($this->_body[$spec])) {
            return $this->_body[$spec];
        }

        return null;
    }

    /**
     * "send" Response
     *
     * Concats all response headers, and then final body (separated by two
     * newlines)
     *
     * @return string
     */
    public function sendResponse()
    {
        $headers = $this->sendHeaders();
        $content = implode("\n", $headers) . "\n\n";

        if ($this->isException() && $this->renderExceptions()) {
            $exceptions = '';
            foreach ($this->getException() as $e) {
                $exceptions .= $e->__toString() . "\n";
            }
            $content .= $exceptions;
        } else {
            $content .= $this->outputBody();
        }

        return $content;
    }
}
