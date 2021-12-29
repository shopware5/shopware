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

namespace Shopware\Tests\Functional\Plugins\Core\Router;

use Enlight_Controller_EventArgs;
use Enlight_Controller_Request_RequestTestCase;
use Enlight_Controller_Response_ResponseTestCase;
use PHPUnit\Framework\TestCase;
use Shopware\Tests\Functional\Traits\ContainerTrait;
use Symfony\Component\HttpFoundation\Response;

class BootstrapTest extends TestCase
{
    use ContainerTrait;

    public function testOnRouteShutdown(): void
    {
        $pluginBootstrap = $this->getContainer()->get('plugins')->Core()->Router();

        $request = new Enlight_Controller_Request_RequestTestCase();
        $request->setRequestUri('shopware.php///www.test.de');
        $response = new Enlight_Controller_Response_ResponseTestCase();
        $args = new Enlight_Controller_EventArgs([
            'request' => $request,
            'response' => $response,
        ]);
        $pluginBootstrap->onRouteShutdown($args);

        static::assertSame(Response::HTTP_MOVED_PERMANENTLY, $response->getStatusCode());
        static::assertSame('shopware.php/www.test.de', $response->getHeader('location'));
    }
}
