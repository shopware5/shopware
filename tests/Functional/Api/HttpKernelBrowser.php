<?php

declare(strict_types=1);
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

namespace Shopware\Tests\Functional\Api;

use Enlight_Controller_Front;
use Enlight_Controller_Response_ResponseTestCase;
use PHPUnit\Framework\TestCase;
use Shopware\Components\DependencyInjection\Container;
use Shopware\Kernel;
use Shopware\Tests\Functional\Helper\Utils;
use Shopware_Components_Auth;
use Shopware_Components_Plugin_Namespace;
use Symfony\Component\BrowserKit\CookieJar;
use Symfony\Component\BrowserKit\History;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\HttpKernel\HttpKernelBrowser as SymfonyHttpKernelBrowser;

class HttpKernelBrowser extends SymfonyHttpKernelBrowser
{
    protected Container $container;

    protected Enlight_Controller_Front $front;

    /**
     * @param array<string, mixed> $server
     */
    public function __construct(Kernel $kernel, array $server = [], ?History $history = null, ?CookieJar $cookieJar = null)
    {
        $this->container = $kernel->getContainer();
        $this->front = $this->container->get('front');
        parent::__construct($kernel, $server, $history, $cookieJar);
    }

    /**
     * @param array<string, mixed> $parameters
     * @param array<string, mixed> $files
     * @param array<string, mixed> $server
     */
    public function request(
        string $method,
        string $uri,
        array $parameters = [],
        array $files = [],
        array $server = [],
        ?string $content = null,
        bool $changeHistory = true
    ): Crawler {
        $this->front->throwExceptions(false);
        Utils::hijackProperty($this->front, 'request', null);
        $corePluginNamespace = $this->container->get('plugins')->get('Core');
        TestCase::assertInstanceOf(Shopware_Components_Plugin_Namespace::class, $corePluginNamespace);
        Utils::hijackProperty($corePluginNamespace->RestApi(), 'isApiCall', false);
        Utils::hijackProperty($this->container->get('errorsubscriber'), 'isInsideErrorHandlerLoop', false);
        $this->front->setResponse(new Enlight_Controller_Response_ResponseTestCase());
        $shop = $this->container->get('shop');
        $this->container->reset('shop');
        $this->container->reset('session');
        $this->container->reset('auth');

        $response = parent::request($method, $uri, $parameters, $files, $server, $content, $changeHistory);
        $this->container->set('shop', $shop);
        Utils::hijackProperty($corePluginNamespace->RestApi(), 'isApiCall', false);
        Shopware_Components_Auth::resetInstance();
        $this->container->reset('auth');
        $this->container->reset('session');

        $this->front->throwExceptions(true);

        return $response;
    }
}
