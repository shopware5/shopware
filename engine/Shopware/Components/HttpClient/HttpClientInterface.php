<?php
/**
 * Shopware 5
 * Copyright (c) shopware AG
 *
 * According to our licensing model, this program can be used
 * under the terms of the GNU Affero General Public License, version 3.
 *
 * The texts of the GNU Affero General Public License with an additional
 * permission can be found at and in the LICENSE file you have received
 * along with this program.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 * See the GNU Affero General Public License for more details.
 *
 * "Shopware" is a registered trademark of shopware AG.
 * The licensing of the program under the AGPLv3 does not imply a
 * trademark license. Therefore, any rights, title and interest in
 * our trademarks remain entirely with the shopware AG.
 */

namespace Shopware\Components\HttpClient;

interface HttpClientInterface
{
    /**
     * Send a GET request
     *
     * @param string $url     URL
     * @param array  $headers array of request options to apply
     *
     * @throws RequestException When an error is encountered
     *
     * @return Response
     */
    public function get($url = null, $headers = []);

    /**
     * Send a HEAD request
     *
     * @param string   $url     URL
     * @param string[] $headers Array of request headers
     *
     * @throws RequestException When an error is encountered
     *
     * @return Response
     */
    public function head($url = null, array $headers = []);

    /**
     * Send a DELETE request
     *
     * @param string   $url     URL
     * @param string[] $headers Array of request headers
     *
     * @throws RequestException When an error is encountered
     *
     * @return Response
     */
    public function delete($url = null, array $headers = []);

    /**
     * Send a PUT request
     *
     * @param string       $url     URL
     * @param string[]     $headers Array of request headers
     * @param string|array $content
     *
     * @throws RequestException When an error is encountered
     *
     * @return Response
     */
    public function put($url = null, array $headers = [], $content = null);

    /**
     * Send a PATCH request
     *
     * @param string       $url     URL
     * @param string[]     $headers Array of request headers
     * @param string|array $content
     *
     * @throws RequestException When an error is encountered
     *
     * @return Response
     */
    public function patch($url = null, array $headers = [], $content = null);

    /**
     * Send a POST request
     *
     * @param string       $url     URL
     * @param string[]     $headers Array of request headers
     * @param string|array $content
     *
     * @throws RequestException When an error is encountered
     *
     * @return Response
     */
    public function post($url = null, array $headers = [], $content = null);
}
