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

use Enlight_Class;
use Enlight_Components_Test_Controller_TestCase as ControllerTestCase;
use Enlight_Template_Manager;
use Enlight_View_Default;
use Shopware\Components\DependencyInjection\Container;
use Shopware\Tests\Functional\Traits\ContainerTrait;
use Shopware\Tests\Functional\Traits\DatabaseTransactionBehaviour;
use Shopware_Controllers_Frontend_Custom;

class CustomTest extends ControllerTestCase
{
    use ContainerTrait;
    use DatabaseTransactionBehaviour;

    public function testIndexAction(): void
    {
        $controller = $this->getController();
        $controller->Request()->setParam('sCustom', 9);

        $controller->indexAction();

        $viewVariables = $controller->View()->getAssign();
        static::assertArrayHasKey('sCustomPage', $viewVariables);
    }

    private function getController(): Shopware_Controllers_Frontend_Custom
    {
        $controller = Enlight_Class::Instance(Shopware_Controllers_Frontend_Custom::class);
        static::assertInstanceOf(Shopware_Controllers_Frontend_Custom::class, $controller);
        $controller->setRequest($this->Request());

        $container = $this->getContainer();
        static::assertInstanceOf(Container::class, $container);
        $controller->setContainer($container);
        $controller->setView(new Enlight_View_Default(new Enlight_Template_Manager()));

        return $controller;
    }
}
