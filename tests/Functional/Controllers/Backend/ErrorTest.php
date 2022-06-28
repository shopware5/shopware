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

namespace Shopware\Tests\Functional\Controllers\Backend;

use Enlight_Controller_Front;
use Enlight_Controller_Plugins_Json_Bootstrap;
use Enlight_Controller_Request_RequestHttp;
use Enlight_Controller_Response_ResponseHttp;
use Enlight_Plugin_Namespace_Loader;
use Enlight_Template_Manager;
use Enlight_View_Default;
use PHPUnit\Framework\TestCase;
use Shopware\Tests\Functional\Traits\ContainerTrait;
use Shopware_Controllers_Backend_Error;

class ErrorTest extends TestCase
{
    use ContainerTrait;

    public function testJsonRenderWillBeActivated(): void
    {
        $controller = new Shopware_Controllers_Backend_Error();
        $controller->setContainer($this->getContainer());
        $request = new Enlight_Controller_Request_RequestHttp();
        $request->setHeader('CONTENT_TYPE', 'application/json');
        $controller->setRequest($request);
        $controller->setResponse(new Enlight_Controller_Response_ResponseHttp());

        $jsonRender = $this->createMock(Enlight_Controller_Plugins_Json_Bootstrap::class);
        $jsonRender->expects(static::once())->method('setRenderer')->willReturn(true);

        $pluginLoader = $this->createMock(Enlight_Plugin_Namespace_Loader::class);
        $pluginLoader->method('Json')->willReturn($jsonRender);

        $front = $this->createMock(Enlight_Controller_Front::class);
        $front->method('Plugins')->willReturn($pluginLoader);

        $controller->setView(new Enlight_View_Default(new Enlight_Template_Manager()));
        $controller->setFront($front);
        $controller->preDispatch();

        static::assertFalse($controller->View()->getAssign('success'));
    }
}
