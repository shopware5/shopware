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

namespace Shopware\Tests\Functional\Controllers\Frontend;

use Enlight_Controller_Request_RequestTestCase;
use Enlight_Controller_Response_ResponseTestCase;
use PHPUnit\Framework\TestCase;
use Shopware\Bundle\StoreFrontBundle\Service\ContextServiceInterface;
use Shopware\Tests\Functional\Traits\ContainerTrait;
use Shopware\Tests\Functional\Traits\ShopContextTrait;

class CrfstokenTest extends TestCase
{
    use ContainerTrait;
    use ShopContextTrait;

    public function testANewCsrfTokenGetsGeneratedAndGetsOverwritten(): void
    {
        $controller = $this->getContainer()->get('shopware_controllers_frontend_csrftoken');

        $this->getContainer()->get(ContextServiceInterface::class)->createShopContext(1);
        $request = new Enlight_Controller_Request_RequestTestCase();
        $response = new Enlight_Controller_Response_ResponseTestCase();
        $controller->setRequest($request);
        $controller->setResponse($response);
        $controller->indexAction($request);

        $token = $response->headers->get('x-csrf-token');
        static::assertNotEmpty($token);
        $cookies = $response->headers->getCookies();
        $sessionToken = $this->getContainer()->get('session')->get('__csrf_token-1');
        static::assertNotEmpty($sessionToken);
        static::assertCount(1, $cookies);
        static::assertNotEmpty($response->headers->getCookies());

        $controller->indexAction($request);
        $renewedToken = $response->headers->get('x-csrf-token');
        static::assertNotEmpty($renewedToken);
        static::assertNotEquals($token, $renewedToken);
        $renewedCookies = $response->headers->getCookies();
        $renewedSessionToken = $this->getContainer()->get('session')->get('__csrf_token-1');

        static::assertNotEquals($sessionToken, $renewedSessionToken);
        static::assertNotEmpty($renewedSessionToken);
        static::assertCount(1, $renewedCookies);
        static::assertNotEmpty($response->headers->getCookies());
    }
}
