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

use PHPUnit_Framework_Constraint_IsType as IsType;

class CustomersProviderTest extends ProviderTestCase
{
    const SERVICE_ID = 'shopware.benchmark_bundle.providers.customers';
    const EXPECTED_KEYS_COUNT = 6;
    const EXPECTED_TYPES = [
        'total' => IsType::TYPE_INT,
        'turnOverPerAge' => IsType::TYPE_ARRAY,
        'turnOverPerGender' => IsType::TYPE_ARRAY,
        'sex' => IsType::TYPE_ARRAY,
        'countries' => [
            'billing' => IsType::TYPE_ARRAY,
            'shipping' => IsType::TYPE_ARRAY,
        ],
        'ageBySex' => IsType::TYPE_ARRAY,
    ];

    /**
     * @group BenchmarkBundle
     */
    public function testGetTotalCustomers()
    {
        $this->installDemoData('customers');

        $provider = $this->getProvider();

        $resultData = $provider->getBenchmarkData();

        $this->assertSame(7, $resultData['total']);
    }

    /**
     * @group BenchmarkBundle
     */
    public function testGetCustomersBySex()
    {
        $this->installDemoData('customers');

        $provider = $this->getProvider();

        $resultData = $provider->getBenchmarkData();

        $this->assertArraySubset([
            'male' => 1,
            'female' => 4,
            'other' => 2,
        ], $resultData['sex']);
    }

    /**
     * @group BenchmarkBundle
     */
    public function testGetCustomersByCounty()
    {
        $this->installDemoData('customers');

        $provider = $this->getProvider();

        $resultData = $provider->getBenchmarkData();

        $this->assertArraySubset([
            'Deutschland' => 6,
            'Arabische Emirate' => 1,
        ], $resultData['countries']['billing']);

        $this->assertArraySubset([
            'Arabische Emirate' => 3,
            'Deutschland' => 1,
            'Australien' => 3,
        ], $resultData['countries']['shipping']);
    }
}
