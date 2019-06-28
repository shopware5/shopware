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

class Enlight_Controller_Response_ResponseTestCase extends Enlight_Controller_Response_ResponseHttp
{
    /**
     * Sends all cookies
     *
     * @return Enlight_Controller_Response_ResponseTestCase
     */
    public function sendCookies()
    {
        $this->canSendHeaders(true);

        return $this;
    }

    /**
     * Gets a cookie value
     *
     * @param string $name
     * @param string $default
     *
     * @return mixed
     */
    public function getCookie($name, $default = null)
    {
        $cookies = $this->getCookies();

        return isset($cookies[$name]['value']) ? $cookies[$name]['value'] : $default;
    }

    /**
     * Gets all the information for a cookie
     *
     * @param string $name
     *
     * @return mixed
     */
    public function getFullCookie($name)
    {
        $cookies = $this->getCookies();

        return isset($cookies[$name]) ? $cookies[$name] : null;
    }

    /**
     * Gets a header value
     *
     * @param string $name
     * @param string $default
     *
     * @return mixed
     */
    public function getHeader($name, $default = null)
    {
        foreach ($this->getHeaders() as $header) {
            if (isset($header['name']) && $header['name'] === $name) {
                return $header['value'];
            }
        }

        return $default;
    }

    /**
     * Can we send headers?
     *
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
        return $this->content;
    }

    /**
     * Get body and/or body segments
     *
     * @param bool|string $spec
     *
     * @return string|array|null
     */
    public function getBody($spec = false)
    {
        return $this->content;
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
