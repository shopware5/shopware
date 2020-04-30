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

namespace Shopware\Tests\Unit\Components;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\DBAL\Connection;
use Enlight_Collection_ArrayCollection;
use Enlight_Controller_Front;
use Enlight_Controller_Request_Request;
use Enlight_Controller_Request_RequestHttp;
use Enlight_Controller_Response_ResponseHttp;
use Enlight_Event_EventArgs;
use PHPUnit\Framework\TestCase;
use Shopware\Bundle\StoreFrontBundle\Service\Core\ContextService;
use Shopware\Bundle\StoreFrontBundle\Struct\ShopContext;
use Shopware\Components\CustomerStream\CookieSubscriber;
use Shopware\Models\Shop\Shop;
use Symfony\Component\DependencyInjection\ContainerInterface;

class CookieSubscriberTest extends TestCase
{
    /**
     * @var Enlight_Controller_Response_ResponseHttp
     */
    private $response;

    /**
     * @var Enlight_Controller_Request_Request
     */
    private $request;

    protected function setUp(): void
    {
        $this->response = new Enlight_Controller_Response_ResponseHttp();
        $this->request = new Enlight_Controller_Request_RequestHttp();
    }

    public function testSltCookieIsSetHttpOnly(): void
    {
        $cookieSubscriber = $this->getCookieSubscriber();

        $cookieSubscriber->afterLogin($this->getEventArgsMock());

        $cookies = $this->response->getCookies();

        static::assertNotNull($cookies);

        $sltCookie = array_pop($cookies);

        static::assertNotNull($sltCookie);
        static::assertEquals('slt', $sltCookie['name']);
        static::assertTrue($sltCookie['httpOnly']);
    }

    protected function getCookieSubscriber()
    {
        return new CookieSubscriber(
            $this->getConnectionMock(),
            $this->getContainerMock()
        );
    }

    protected function getConnectionMock()
    {
        return static::getMockBuilder(Connection::class)
            ->disableOriginalConstructor()
            ->getMock();
    }

    protected function getContainerMock()
    {
        $mock = static::getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['get', 'set', 'has', 'initialized', 'hasParameter', 'getParameter', 'setParameter'])
            ->getMock();

        $mock->method('get')
            ->willReturnCallback(function ($key) {
                return $this->containerGetMock($key);
            });

        $mock->method('initialized')
            ->with('front')
            ->willReturn(true);

        return $mock;
    }

    protected function containerGetMock($key)
    {
        if ($key === 'config') {
            return new Enlight_Collection_ArrayCollection([
                'useSltCookie' => true,
            ]);
        }

        if ($key === 'front') {
            return $this->getFrontControllerMock($this->response, $this->request);
        }

        if ($key === 'shopware_storefront.context_service') {
            return $this->getContextServiceMock();
        }

        if ($key === 'session') {
            return new ArrayCollection([]);
        }

        return null;
    }

    protected function getFrontControllerMock(Enlight_Controller_Response_ResponseHttp $response, Enlight_Controller_Request_Request $request)
    {
        $mock = static::getMockBuilder(Enlight_Controller_Front::class)
            ->disableOriginalConstructor()
            ->setMethods(['Response', 'Request'])
            ->getMock();

        $mock->method('Response')
            ->willReturn($response);

        $mock->method('Request')
            ->willReturn($request);

        return $mock;
    }

    protected function getContextServiceMock()
    {
        $mock = static::getMockBuilder(ContextService::class)
            ->disableOriginalConstructor()
            ->setMethods(['getShopContext'])
            ->getMock();

        $mock->method('getShopContext')
            ->willReturn($this->getShopContextMock());

        return $mock;
    }

    protected function getShopContextMock()
    {
        $mock = static::getMockBuilder(ShopContext::class)
            ->disableOriginalConstructor()
            ->setMethods(['getShop'])
            ->getMock();

        $mock->method('getShop')
            ->willReturn($this->getShopMock());

        return $mock;
    }

    protected function getShopMock()
    {
        return static::getMockBuilder(Shop::class)
            ->disableOriginalConstructor()
            ->setMethods(['getParentId'])
            ->getMock();
    }

    protected function getEventArgsMock()
    {
        $mock = static::getMockBuilder(Enlight_Event_EventArgs::class)
            ->disableOriginalConstructor()
            ->setMethods(['get'])
            ->getMock();

        $mock->method('get')
            ->with('user')
            ->willReturn(['id' => 1337]);

        return $mock;
    }
}
