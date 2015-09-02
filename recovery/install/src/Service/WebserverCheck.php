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

namespace Shopware\Recovery\Install\Service;

use Shopware\Recovery\Install\FileToken;
use Shopware\Recovery\Common\HttpClient\Client;
use Shopware\Recovery\Common\HttpClient\ClientException;
use Shopware\Recovery\Install\Struct\Shop;

/**
 * @category  Shopware
 * @package   Shopware\Recovery\Install\Service
 * @copyright Copyright (c) shopware AG (http://www.shopware.de)
 */
class WebserverCheck
{
    /**
     * @var string
     */
    private $checkUrl;

    /**
     * @var string
     */
    private $tokenPath;

    /**
     * @var string
     */
    private $pingUrl;

    /**
     * @param string $pingUrl
     * @param string $checkUrl
     * @param string $tokenPath
     * @param Client $httpClient
     */
    public function __construct($pingUrl, $checkUrl, $tokenPath, Client $httpClient)
    {
        $this->checkUrl   = $checkUrl;
        $this->tokenPath  = $tokenPath;
        $this->pingUrl    = $pingUrl;
        $this->httpClient = $httpClient;
    }

    /**
     * @param  Shop              $shop
     * @throws \RuntimeException
     * @return bool
     */
    public function checkPing(Shop $shop)
    {
        $pingUrl = $this->buildPingUrl($shop);

        try {
            $response = $this->httpClient->get($pingUrl);
        } catch (ClientException $e) {
            throw new \RuntimeException("Could not check webserver", $e->getCode(), $e);
        }

        if ($response->getCode() != 200) {
            throw new \RuntimeException("Wrong http code ". $response->getCode());
        }

        if ($response->getBody() != 'pong') {
            throw new \RuntimeException("Content  ". $response->getBody());
        }

        return true;
    }

    /**
     * @param  Shop   $shop
     * @return string
     */
    public function buildPingUrl(Shop $shop)
    {
        if ($shop->basePath) {
            $shop->basePath = '/' . trim($shop->basePath, '/');
        }

        $pingUrl = 'http://' . $shop->host . $shop->basePath . '/' . $this->pingUrl;

        return $pingUrl;
    }
}
