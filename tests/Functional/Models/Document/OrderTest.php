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

use PHPUnit\Framework\TestCase;
use Shopware\Tests\Functional\Traits\ContainerTrait;
use Shopware\Tests\Functional\Traits\DatabaseTransactionBehaviour;
use Shopware\Tests\Functional\Traits\FixtureBehaviour;

class OrderTest extends TestCase
{
    use FixtureBehaviour;
    use ContainerTrait;
    use DatabaseTransactionBehaviour;

    public function testCreateInvoice(): void
    {
        $orderId = 60001;

        $this->executeFixture(__DIR__ . '/_fixtures/order.sql');

        $documentOrder = new \Shopware_Models_Document_Order($orderId, $this->getDefaultConfig());

        $reflectionProperty = (new \ReflectionClass(\Shopware_Models_Document_Order::class))->getProperty('_positions');
        $reflectionProperty->setAccessible(true);

        $result = $reflectionProperty->getValue($documentOrder);

        $tax = 0.0;
        foreach ($result as $basketPosition) {
            $tax += $basketPosition['amount'] - $basketPosition['amount_netto'];
        }

        $expectedResult = 3.3137499999999998;

        static::assertSame($expectedResult, $tax);
    }

    /**
     * @return array <string, mixed>
     */
    private function getDefaultConfig(): array
    {
        return [
            'netto' => false,
            'bid' => '',
            'voucher' => null,
            'date' => '16.07.2021',
            'delivery_date' => '16.07.2021',
            'shippingCostsAsPosition' => true,
            '_renderer' => 'pdf',
            '_preview' => false,
            '_previewForcePagebreak' => null,
            '_previewSample' => null,
            'docComment' => '',
            'forceTaxCheck' => false,
        ];
    }
}
