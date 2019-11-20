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

namespace Shopware\Tests\Functional\Api;

use PHPUnit\Framework\TestCase;
use Shopware\Kernel;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\HttpKernel\Client;

abstract class AbstractApiTestCase extends TestCase
{
    /**
     * @var Client
     */
    protected $client;

    public function setUp(): void
    {
        /** @var Kernel $kernel */
        $kernel = Shopware()->Container()->get('kernel');
        $this->client = new HttpKernelBrowser($kernel);

        Shopware()->Db()->query('UPDATE s_core_auth SET apiKey = "demo" WHERE username LIKE "demo"');
        Shopware()->Models()->clear();
    }

    public function authenticatedApiRequest(
        string $method,
        string $url,
        array $parameters = [],
        ?array $content = null,
        array $files = []
    ): Crawler {
        $authHeader = 'Basic ' . base64_encode('demo:demo');
        $headers = [
            'HTTP_Authorization' => $authHeader,
            'Content-Type' => 'application/json',
        ];

        return $this->client->request($method, $url, $parameters, $files, $headers, $content ? json_encode($content) : null);
    }
}
