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

namespace Shopware\Tests\Functional\Bundle\BenchmarkBundle\Controllers\Backend;

use Shopware\Tests\Functional\Bundle\BenchmarkBundle\Controllers\Backend\Mocks\ViewMock;

class BenchmarkLocalOverviewControllerTest extends BenchmarkControllerTestCase
{
    const CONTROLLER_NAME = \Shopware_Controllers_Backend_BenchmarkLocalOverview::class;

    /**
     * @group BenchmarkBundle
     */
    public function testRenderAction_should_load_start()
    {
        /** @var \Shopware_Controllers_Backend_BenchmarkLocalOverview $controller */
        $controller = $this->getController();

        Shopware()->Db()->exec('DELETE FROM s_benchmark_config;');
        $controller->setView(new ViewMock(new \Enlight_Template_Manager()));

        $controller->renderAction();

        $loadedTemplate = $controller->View()->getTemplate();

        static::assertSame('backend/benchmark/template/local/start.tpl', $loadedTemplate);
    }

    /**
     * @group BenchmarkBundle
     */
    public function testRenderAction_should_load_custom()
    {
        /** @var \Shopware_Controllers_Backend_BenchmarkLocalOverview $controller */
        $controller = $this->getController();

        $this->installDemoData('benchmark_config');
        $controller->setView(new ViewMock(new \Enlight_Template_Manager()));
        $controller->Request()->setParam('template', 'custom');

        $controller->renderAction();

        $loadedTemplate = $controller->View()->getTemplate();

        static::assertSame('backend/benchmark/template/local/custom.tpl', $loadedTemplate);
    }
}
