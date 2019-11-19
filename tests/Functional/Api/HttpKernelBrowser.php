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

use Shopware\Kernel;
use Shopware\Tests\Functional\Helper\Utils;
use Symfony\Component\BrowserKit\CookieJar;
use Symfony\Component\BrowserKit\History;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpKernel\Client;

class HttpKernelBrowser extends Client
{
    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * @var \Enlight_Controller_Front
     */
    protected $front;

    public function __construct(Kernel $kernel, array $server = [], History $history = null, CookieJar $cookieJar = null)
    {
        $this->container = $kernel->getContainer();
        $this->front = $this->container->get('front');
        parent::__construct($kernel, $server, $history, $cookieJar);
    }

    public function request($method, $uri, array $parameters = [], array $files = [], array $server = [], $content = null, $changeHistory = true)
    {
        $this->front->throwExceptions(false);
        Utils::hijackProperty($this->front, 'request', null);
        Utils::hijackProperty($this->container->get('plugins')->get('Core')->get('RestApi'), 'isApiCall', false);
        Utils::hijackProperty($this->container->get('errorsubscriber'), 'isInsideErrorHandlerLoop', false);
        $this->front->setResponse(new \Enlight_Controller_Response_ResponseTestCase());
        $shop = $this->container->get('shop');
        $this->container->reset('shop');
        $this->container->reset('session');
        $this->container->reset('auth');

        $response = parent::request($method, $uri, $parameters, $files, $server, $content, $changeHistory);
        $this->container->set('shop', $shop);
        Utils::hijackProperty($this->container->get('plugins')->get('Core')->get('RestApi'), 'isApiCall', false);
        \Shopware_Components_Auth::resetInstance();
        $this->container->reset('auth');
        $this->container->reset('session');

        $this->front->throwExceptions(true);

        return $response;
    }
}
