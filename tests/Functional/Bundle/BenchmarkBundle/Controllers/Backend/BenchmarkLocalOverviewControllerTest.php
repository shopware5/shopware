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

namespace Shopware\Tests\Functional\Bundle\BenchmarkBundle\Controllers\Backend;

use Enlight_Template_Manager;
use Shopware\Tests\Functional\Bundle\BenchmarkBundle\Controllers\Backend\Mocks\ViewMock;
use Shopware_Controllers_Backend_BenchmarkLocalOverview;

class BenchmarkLocalOverviewControllerTest extends BenchmarkControllerTestCase
{
    protected const CONTROLLER_NAME = Shopware_Controllers_Backend_BenchmarkLocalOverview::class;

    /**
     * @group BenchmarkBundle
     */
    public function testRenderActionShouldLoadStart(): void
    {
        $controller = $this->getController();
        static::assertInstanceOf(Shopware_Controllers_Backend_BenchmarkLocalOverview::class, $controller);

        $this->getContainer()->get('dbal_connection')->executeStatement('DELETE FROM s_benchmark_config;');
        $controller->setView(new ViewMock(new Enlight_Template_Manager()));

        $controller->renderAction();

        $view = $controller->View();
        static::assertInstanceOf(ViewMock::class, $view);
        $loadedTemplate = $view->getTemplate();

        static::assertSame('backend/benchmark/template/local/start.tpl', $loadedTemplate);
    }

    /**
     * @group BenchmarkBundle
     */
    public function testRenderActionShouldLoadCustom(): void
    {
        $controller = $this->getController();
        static::assertInstanceOf(Shopware_Controllers_Backend_BenchmarkLocalOverview::class, $controller);

        $this->installDemoData('benchmark_config');
        $controller->setView(new ViewMock(new Enlight_Template_Manager()));
        $controller->Request()->setParam('template', 'custom');

        $controller->renderAction();

        $view = $controller->View();
        static::assertInstanceOf(ViewMock::class, $view);
        $loadedTemplate = $view->getTemplate();

        static::assertSame('backend/benchmark/template/local/custom.tpl', $loadedTemplate);
    }
}
