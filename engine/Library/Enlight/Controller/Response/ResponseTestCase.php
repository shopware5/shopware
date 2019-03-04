<?php
/**
 * Shopware 5
 * Copyright (c) shopware AG
 *
 * According to our dual licensing model, this program can be used either
 * under the terms of the GNU Affero General Public License, version 3,
 * or under a proprietary license.
 *
 * The texts of the GNU Affero General Public License with an additional
 * permission and of our proprietary license can be found at and
 * in the LICENSE file you have received along with this program.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Affero General Public License for more details.
 *
 * "Shopware" is a registered trademark of shopware AG.
 * The licensing of the program under the AGPLv3 does not imply a
 * trademark license. Therefore any rights, title and interest in
 * our trademarks remain entirely with us.
 */

/**
 * This class is highly based on Zend_Controller_Response_HttpTestCase
 *
 * @see https://github.com/zendframework/zf1/blob/release-1.12.20/library/Zend/Controller/Response/HttpTestCase.php
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
