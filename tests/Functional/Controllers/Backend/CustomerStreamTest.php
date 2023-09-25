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

namespace Shopware\Tests\Functional\Controllers\Backend;

use Enlight_Controller_Request_RequestHttp;
use Enlight_Template_Manager;
use Enlight_View_Default;
use PHPUnit\Framework\TestCase;
use Shopware\Tests\Functional\Traits\ContainerTrait;
use Shopware\Tests\Functional\Traits\DatabaseTransactionBehaviour;
use Shopware_Controllers_Backend_CustomerStream;

class CustomerStreamTest extends TestCase
{
    use ContainerTrait;
    use DatabaseTransactionBehaviour;

    public function testLoadStreamAction(): void
    {
        $controller = $this->getController();
        $request = new Enlight_Controller_Request_RequestHttp();
        $request->setParam('streamId', '');

        $controller->setRequest($request);

        $controller->loadStreamAction();

        $assign = $controller->View()->getAssign();
        static::assertTrue($assign['success']);
        static::assertEmpty($assign['data']);
        static::assertSame(0, $assign['total']);
    }

    private function getController(): Shopware_Controllers_Backend_CustomerStream
    {
        $controller = new Shopware_Controllers_Backend_CustomerStream();
        $controller->setContainer($this->getContainer());
        $controller->setView(new Enlight_View_Default(new Enlight_Template_Manager()));

        return $controller;
    }
}
