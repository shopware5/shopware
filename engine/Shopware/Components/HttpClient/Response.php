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

namespace Shopware\Components\HttpClient;

class Response
{
    /**
     * @var string
     */
    private $statusCode;

    /**
     * @var array
     */
    private $headers;

    /**
     * @var array
     */
    private $headersLookup;

    /**
     * @var string
     */
    private $body;

    /**
     * @param string $statusCode
     * @param array  $headers
     * @param string $body
     */
    public function __construct($statusCode, $headers, $body)
    {
        $this->statusCode = $statusCode;
        $this->headers = $headers;
        $this->headersLookup = array_change_key_case($this->headers, CASE_LOWER);
        $this->body = $body;
    }

    /**
     * Get the response status code (e.g. "200", "404", etc.)
     *
     * @return string
     */
    public function getStatusCode()
    {
        return $this->statusCode;
    }

    /**
     * Get the body of the message
     *
     * @return string
     */
    public function getBody()
    {
        return $this->body;
    }

    /**
     * Gets all message headers.
     *
     * The keys represent the header name as it will be sent over the wire, and
     * each value is an array of strings associated with the header.
     *
     *     // Represent the headers as a string
     *     foreach ($message->getHeaders() as $name => $values) {
     *         echo $name . ": " . implode(", ", $values);
     *     }
     *
     * @return array returns an associative array of the message's headers
     */
    public function getHeaders()
    {
        return $this->headers;
    }

    /**
     * Retrieve a header by the given case-insensitive name.
     *
     * By default, this method returns all of the header values of the given
     * case-insensitive header name as a string concatenated together using
     * a comma. Because some header should not be concatenated together using a
     * comma, this method provides a Boolean argument that can be used to
     * retrieve the associated header values as an array of strings.
     *
     * @param string $header  case-insensitive header name
     * @param bool   $asArray set to true to retrieve the header value as an
     *                        array of strings
     *
     * @return array|string
     */
    public function getHeader($header, $asArray = false)
    {
        $name = strtolower($header);

        if (!isset($this->headersLookup[$name])) {
            return $asArray ? [] : '';
        }

        return $asArray
            ? $this->headersLookup[$name]
            : implode(', ', $this->headersLookup[$name]);
    }

    /**
     * Checks if a header exists by the given case-insensitive name.
     *
     * @param string $header case-insensitive header name
     *
     * @return bool Returns true if any header names match the given header
     *              name using a case-insensitive string comparison. Returns false if
     *              no matching header name is found in the message.
     */
    public function hasHeader($header)
    {
        return isset($this->headersLookup[strtolower($header)]);
    }
}
