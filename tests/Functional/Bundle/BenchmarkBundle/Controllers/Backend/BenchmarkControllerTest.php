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

class BenchmarkControllerTest extends BenchmarkControllerTestCase
{
    const CONTROLLER_NAME = \Shopware_Controllers_Backend_Benchmark::class;

    /**
     * @group BenchmarkBundle
     */
    public function testSaveIndustryAction()
    {
        /** @var \Shopware_Controllers_Backend_Benchmark $controller */
        $controller = $this->getController();

        $this->installDemoData('benchmark_config');

        $controller->Request()->setParam('shopId', 1);
        $controller->Request()->setParam('industry', 15);

        $controller->saveIndustryAction();

        static::assertEquals(15, $this->loadSettingColumn('config.industry'));
    }

    /**
     * @group BenchmarkBundle
     */
    public function testSetActiveAction()
    {
        /** @var \Shopware_Controllers_Backend_Benchmark $controller */
        $controller = $this->getController();

        $this->installDemoData('benchmark_config');

        $controller->Request()->setParam('shopId', 1);
        $controller->Request()->setParam('active', 1);

        $controller->setActiveAction();

        static::assertEquals(1, $this->loadSettingColumn('config.active'));
    }

    /**
     * @group BenchmarkBundle
     */
    public function testSaveTypeAction()
    {
        /** @var \Shopware_Controllers_Backend_Benchmark $controller */
        $controller = $this->getController();

        $this->installDemoData('benchmark_config');

        $controller->Request()->setParam('shopId', 1);
        $controller->Request()->setParam('type', 'b2c');

        $controller->saveTypeAction();

        static::assertEquals('b2c', $this->loadSettingColumn('config.type'));
    }
}
