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

/**
 * @category  Shopware
 * @package   Shopware\Components\HttpClient
 * @copyright Copyright (c) shopware AG (http://www.shopware.de)
 */
interface HttpClientInterface
{
    /**
     * Send a GET request
     *
     * @param string $url     URL
     * @param array  $headers Array of request options to apply.
     *
     * @return Response
     * @throws RequestException When an error is encountered
     */
    public function get($url = null, $headers = []);

    /**
     * Send a HEAD request
     *
     * @param string   $url     URL
     * @param string[] $headers Array of request headers
     *
     * @return Response
     * @throws RequestException When an error is encountered
     */
    public function head($url = null, array $headers = []);

    /**
     * Send a DELETE request
     *
     * @param string   $url     URL
     * @param string[] $headers Array of request headers
     *
     * @return Response
     * @throws RequestException When an error is encountered
     */
    public function delete($url = null, array $headers = []);

    /**
     * Send a PUT request
     *
     * @param  string           $url     URL
     * @param  string[]         $headers Array of request headers
     * @param  string|array     $content
     * @return Response
     * @throws RequestException When an error is encountered
     */
    public function put($url = null, array $headers = [], $content = null);

    /**
     * Send a PATCH request
     *
     * @param  string           $url     URL
     * @param  string[]         $headers Array of request headers
     * @param  string|array     $content
     * @return Response
     * @throws RequestException When an error is encountered
     */
    public function patch($url = null, array $headers = [], $content = null);

    /**
     * Send a POST request
     *
     * @param  string           $url     URL
     * @param  string[]         $headers Array of request headers
     * @param  string|array     $content
     * @return Response
     * @throws RequestException When an error is encountered
     */
    public function post($url = null, array $headers = [], $content = null);
}
