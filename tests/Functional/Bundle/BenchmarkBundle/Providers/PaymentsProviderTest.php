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

namespace Shopware\Tests\Functional\Bundle\BenchmarkBundle\Providers;

use PHPUnit\Framework\Constraint\IsType;
use Shopware\Bundle\BenchmarkBundle\Provider\PaymentsProvider;

class PaymentsProviderTest extends ProviderTestCase
{
    protected const SERVICE_ID = PaymentsProvider::class;
    protected const EXPECTED_KEYS_COUNT = 6;
    protected const EXPECTED_TYPES = [
        'activePayments' => IsType::TYPE_INT,
        'paymentsWithSurcharge' => IsType::TYPE_INT,
        'paymentsWithReduction' => IsType::TYPE_INT,
        'paymentsWithPercentagePrice' => IsType::TYPE_INT,
        'paymentsWithAbsolutePrice' => IsType::TYPE_INT,
        'paymentUsages' => IsType::TYPE_ARRAY,
    ];

    /**
     * @group BenchmarkBundle
     */
    public function testGetTotalActivePayments(): void
    {
        $this->installDemoData('payments');

        $resultData = $this->getBenchmarkData();

        static::assertSame(4, $resultData['activePayments']);
    }

    /**
     * @group BenchmarkBundle
     */
    public function testGetPaymentsWithSurcharge(): void
    {
        $this->installDemoData('payments');

        $resultData = $this->getBenchmarkData();

        static::assertSame(4, $resultData['paymentsWithSurcharge']);
    }

    /**
     * @group BenchmarkBundle
     */
    public function testGetPaymentsWithReduction(): void
    {
        $this->installDemoData('payments');

        $resultData = $this->getBenchmarkData();

        static::assertSame(5, $resultData['paymentsWithReduction']);
    }

    /**
     * @group BenchmarkBundle
     */
    public function testGetPaymentsWithPercentagePrice(): void
    {
        $this->installDemoData('payments');

        $resultData = $this->getBenchmarkData();

        static::assertSame(4, $resultData['paymentsWithPercentagePrice']);
    }

    /**
     * @group BenchmarkBundle
     */
    public function testGetPaymentsWithAbsolutePrice(): void
    {
        $this->installDemoData('payments');

        $resultData = $this->getBenchmarkData();

        static::assertSame(6, $resultData['paymentsWithAbsolutePrice']);
    }

    /**
     * @group BenchmarkBundle
     */
    public function testGetTotalActivePaymentsPerShop(): void
    {
        $this->installDemoData('payments');

        $provider = $this->getProvider();

        $resultData = $provider->getBenchmarkData($this->getShopContextByShopId(1));
        static::assertSame(4, $resultData['activePayments']);

        $resultData = $provider->getBenchmarkData($this->getShopContextByShopId(2));
        static::assertSame(5, $resultData['activePayments']);
    }
}
