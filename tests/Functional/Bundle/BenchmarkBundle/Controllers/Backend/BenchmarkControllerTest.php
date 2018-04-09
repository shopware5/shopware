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
    public function testLoadSettingsAction()
    {
        /** @var \Shopware_Controllers_Backend_Benchmark $controller */
        $controller = $this->getController();

        $this->installDemoData('order_basic');
        $this->installDemoData('benchmark_config');

        $this->setSetting('business', 1);
        $this->setSetting('last_order_id', 1);

        $controller->loadSettingsAction();
        $settings = $controller->View()->getAssign('data');

        $this->assertArraySubset([
            'active' => null,
            'lastSent' => '1990-01-01 00:00:00',
            'lastReceived' => '1990-01-01 00:00:00',
            'lastOrderNumber' => '20000',
            'ordersBatchSize' => 1000,
            'business' => 1,
            'termsAccepted' => 0,
        ], $settings);

        $this->assertInternalType(\PHPUnit_Framework_Constraint_IsType::TYPE_INT, $settings['business']);
    }

    /**
     * @group BenchmarkBundle
     */
    public function testSaveSettingsAction()
    {
        /** @var \Shopware_Controllers_Backend_Benchmark $controller */
        $controller = $this->getController();

        $this->installDemoData('benchmark_config');

        $controller->Request()->setParam('ordersBatchSize', 5000);

        $controller->saveSettingsAction();

        $this->assertEquals(5000, $this->loadSettingColumn('config.orders_batch_size'));
    }

    /**
     * @group BenchmarkBundle
     */
    public function testSaveBusinessAction()
    {
        /** @var \Shopware_Controllers_Backend_Benchmark $controller */
        $controller = $this->getController();

        $this->installDemoData('benchmark_config');

        $controller->Request()->setParam('business', 15);

        $controller->saveBusinessAction();

        $this->assertEquals(15, $this->loadSettingColumn('config.business'));
    }

    /**
     * @group BenchmarkBundle
     */
    public function testSetActiveAction()
    {
        /** @var \Shopware_Controllers_Backend_Benchmark $controller */
        $controller = $this->getController();

        $this->installDemoData('benchmark_config');

        $controller->Request()->setParam('active', 1);

        $controller->setActiveAction();

        $this->assertEquals(1, $this->loadSettingColumn('config.active'));
    }
}
