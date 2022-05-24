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

use Shopware_Controllers_Backend_Benchmark;

class BenchmarkControllerTest extends BenchmarkControllerTestCase
{
    protected const CONTROLLER_NAME = Shopware_Controllers_Backend_Benchmark::class;

    /**
     * @group BenchmarkBundle
     */
    public function testSaveIndustryAction(): void
    {
        $controller = $this->getController();
        static::assertInstanceOf(Shopware_Controllers_Backend_Benchmark::class, $controller);

        $this->installDemoData('benchmark_config');

        $controller->Request()->setParam('shopId', 1);
        $controller->Request()->setParam('industry', 15);

        $controller->saveIndustryAction();

        static::assertSame('15', $this->loadSettingColumn('config.industry'));
    }

    /**
     * @group BenchmarkBundle
     */
    public function testSetActiveAction(): void
    {
        $controller = $this->getController();
        static::assertInstanceOf(Shopware_Controllers_Backend_Benchmark::class, $controller);

        $this->installDemoData('benchmark_config');

        $controller->Request()->setParam('shopId', 1);
        $controller->Request()->setParam('active', 1);

        $controller->setActiveAction();

        static::assertSame('1', $this->loadSettingColumn('config.active'));
    }

    /**
     * @group BenchmarkBundle
     */
    public function testSaveTypeAction(): void
    {
        $controller = $this->getController();
        static::assertInstanceOf(Shopware_Controllers_Backend_Benchmark::class, $controller);

        $this->installDemoData('benchmark_config');

        $controller->Request()->setParam('shopId', 1);
        $controller->Request()->setParam('type', 'b2c');

        $controller->saveTypeAction();

        static::assertSame('b2c', $this->loadSettingColumn('config.type'));
    }
}
