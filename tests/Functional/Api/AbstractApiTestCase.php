<?php

declare(strict_types=1);
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

abstract class AbstractApiTestCase extends TestCase
{
    /**
     * @var HttpKernelBrowser
     */
    protected $client;

    protected function setUp(): void
    {
        /** @var Kernel $kernel */
        $kernel = Shopware()->Container()->get('kernel');
        $this->client = new HttpKernelBrowser($kernel);

        Shopware()->Db()->query('UPDATE s_core_auth SET apiKey = "demo" WHERE username LIKE "demo"');
        Shopware()->Models()->clear();
    }

    /**
     * @param array<string, mixed>                   $parameters
     * @param array<array<string, mixed>|mixed>|null $content
     * @param array<string, mixed>                   $files
     */
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

        return $this->client->request($method, $url, $parameters, $files, $headers, $content ? json_encode($content, JSON_THROW_ON_ERROR) : null);
    }
}
