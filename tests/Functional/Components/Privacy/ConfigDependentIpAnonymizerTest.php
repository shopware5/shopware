<?php
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

namespace Shopware\Tests\Functional\Components\Privacy;

use Enlight_Components_Test_Controller_TestCase;
use Shopware\Components\Privacy\ConfigDependentIpAnonymizer;
use Shopware\Components\Privacy\IpAnonymizer;
use Shopware_Components_Config;

class ConfigDependentIpAnonymizerTest extends Enlight_Components_Test_Controller_TestCase
{
    /**
     * Standard set up for every test - just disable auth
     */
    public function setUp(): void
    {
        parent::setUp();

        // Disable auth and acl
        Shopware()->Plugins()->Backend()->Auth()->setNoAuth();
        Shopware()->Plugins()->Backend()->Auth()->setNoAcl();
    }

    public function tearDown(): void
    {
        $this->setConfig('anonymizeIp', true);
        Shopware()->Container()->reset(\Shopware\Components\Privacy\IpAnonymizerInterface::class);
        // Reset the controller as well, since it already has the service injected
        Shopware()->Container()->reset('shopware_controllers_backend_logger');
        parent::tearDown();
    }

    public function testConfigActiveWorking()
    {
        $configStub = $this->createMock(Shopware_Components_Config::class);
        $configStub
            ->expects(static::exactly(1))
            ->method('get')
            ->with('anonymizeIp')
            ->willReturn(true);

        $service = new ConfigDependentIpAnonymizer(new IpAnonymizer(), $configStub);

        static::assertEquals('127.0.0.0', $service->anonymize('127.0.0.1'));
    }

    public function testConfigInactiveWorking()
    {
        $configStub = $this->createMock(Shopware_Components_Config::class);
        $configStub
            ->expects(static::exactly(1))
            ->method('get')
            ->with('anonymizeIp')
            ->willReturn(false);

        $service = new ConfigDependentIpAnonymizer(new IpAnonymizer(), $configStub);

        static::assertEquals('127.0.0.1', $service->anonymize('127.0.0.1'));
    }

    public function testDispatchLogLocalhostIpv4()
    {
        $this->setConfig('anonymizeIp', true);

        Shopware()->Container()->reset(\Shopware\Components\Privacy\IpAnonymizerInterface::class);

        $this->Request()
            ->setClientIp('127.0.0.1')
            ->setMethod('POST')
            ->setPost([
            'type' => 'backend',
            'key' => 'Shopcache',
            'text' => 'Shopcache wurde geleert',
            'user' => 'Demo user',
            'value4' => '',
        ]);
        $this->dispatch('/backend/Logger/createLog');
        $data = $this->View()->getAssign('data');

        static::assertEquals('127.0.0.0', $data['ipAddress']);
    }

    public function testDispatchLogIpv6()
    {
        $this->setConfig('anonymizeIp', true);

        Shopware()->Container()->reset(\Shopware\Components\Privacy\IpAnonymizerInterface::class);

        $this->Request()
            ->setClientIp('2a00:1450:4001:816::200e')
            ->setMethod('POST')
            ->setPost([
                'type' => 'backend',
                'key' => 'Shopcache',
                'text' => 'Shopcache wurde geleert',
                'user' => 'Demo user',
                'value4' => '',
            ]);
        $this->dispatch('/backend/Logger/createLog');
        $data = $this->View()->getAssign('data');

        static::assertEquals('2a00:1450:4001:::', $data['ipAddress']);
    }

    public function testDispatchLogLocalhostConfigDisabled()
    {
        $this->setConfig('anonymizeIp', false);

        Shopware()->Container()->reset(\Shopware\Components\Privacy\IpAnonymizerInterface::class);
        // Reset the controller as well, since it already has the service injected
        Shopware()->Container()->reset('shopware_controllers_backend_logger');

        $this->Request()
            ->setClientIp('127.0.0.1')
            ->setMethod('POST')
            ->setPost([
                'type' => 'backend',
                'key' => 'Shopcache',
                'text' => 'Shopcache wurde geleert',
                'user' => 'Demo user',
                'value4' => '',
            ]);
        $this->dispatch('/backend/Logger/createLog');
        $data = $this->View()->getAssign('data');

        static::assertEquals('127.0.0.1', $data['ipAddress']);
    }
}
