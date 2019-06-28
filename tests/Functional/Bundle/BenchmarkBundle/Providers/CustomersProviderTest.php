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

namespace Shopware\Tests\Functional\Bundle\BenchmarkBundle\Providers;

use PHPUnit\Framework\Constraint\IsType;

class CustomersProviderTest extends ProviderTestCase
{
    const SERVICE_ID = 'shopware.benchmark_bundle.providers.customers';
    const EXPECTED_KEYS_COUNT = 1;
    const EXPECTED_TYPES = [
        'list' => IsType::TYPE_ARRAY,
    ];

    /**
     * @group BenchmarkBundle
     */
    public function testGetCustomersList()
    {
        $this->resetConfig();
        $this->installDemoData('customers');
        $resultData = $this->getBenchmarkData();
        $customersList = $resultData['list'];

        static::assertEquals(1993, $customersList[0]['birthYear']);
        static::assertEquals(1, $customersList[0]['birthMonth']);

        static::assertEquals(1, $customersList[0]['registered']);
        static::assertEquals(0, $customersList[1]['registered']);

        static::assertEquals('2013-11-25', $customersList[5]['registerDate']);

        static::assertEquals(1, $customersList[4]['hasNewsletter']);
        static::assertEquals(0, $customersList[5]['hasNewsletter']);
    }

    /**
     * @group BenchmarkBundle
     */
    public function testMatchGenders()
    {
        $this->resetConfig();
        $this->installDemoData('customers');

        $resultData = $this->getBenchmarkData();
        $customersList = $resultData['list'];

        static::assertSame('male', $customersList[0]['gender']);
        static::assertSame('female', $customersList[1]['gender']);
        static::assertSame('unknown', $customersList[3]['gender']);
    }

    /**
     * @group BenchmarkBundle
     */
    public function testGetCustomersTurnOver()
    {
        $this->resetConfig();
        $this->installDemoData('customers');

        $resultData = $this->getBenchmarkData();
        $customersList = $resultData['list'];

        static::assertEquals(750, $customersList[3]['turnOver']);
        static::assertEquals(850, $customersList[4]['turnOver']);
    }

    /**
     * @group BenchmarkBundle
     */
    public function testGetCustomersListPerShop()
    {
        $this->installDemoData('customers');
        $this->installDemoData('second_config');
        $provider = $this->getProvider();

        $resultData = $provider->getBenchmarkData($this->getShopContextByShopId(1));
        $customersList = $resultData['list'];
        static::assertCount(7, $customersList);

        $resultData = $provider->getBenchmarkData($this->getShopContextByShopId(2));
        $customersList = $resultData['list'];
        static::assertCount(1, $customersList);
    }

    /**
     * @group BenchmarkBundle
     */
    public function testGetCustomersListConsidersBatchSize()
    {
        $this->resetConfig();
        $this->installDemoData('customers');

        Shopware()->Db()->exec('UPDATE `s_benchmark_config` SET last_customer_id=0, batch_size=1;');

        $resultData = $this->getBenchmarkData();
        $customersList = $resultData['list'];

        static::assertCount(1, $customersList);
    }

    /**
     * @group BenchmarkBundle
     */
    public function testGetCustomersListConsidersLastCustomerId()
    {
        $this->resetConfig();
        $this->installDemoData('customers');

        Shopware()->Db()->exec('UPDATE `s_benchmark_config` SET last_customer_id=4, batch_size=1;');

        $resultData = $this->getBenchmarkData();
        $customersList = $resultData['list'];

        static::assertEquals(850, $customersList[0]['turnOver']);
    }

    /**
     * @group BenchmarkBundle
     */
    public function testGetCustomersListUpdatesCustomerId()
    {
        $this->resetConfig();
        $this->installDemoData('customers');

        Shopware()->Db()->exec('UPDATE `s_benchmark_config` SET last_customer_id=4, batch_size=1 WHERE shop_id=1;');

        $this->sendStatistics();

        static::assertEquals(5, Shopware()->Db()->fetchOne('SELECT last_customer_id FROM s_benchmark_config WHERE shop_id=1'));
    }
}
