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

namespace Shopware\Tests\Functional\Models\Document;

use Closure;
use PHPUnit\Framework\TestCase;
use Shopware\Tests\Functional\Traits\DatabaseTransactionBehaviour;
use Shopware\Tests\Functional\Traits\FixtureBehaviour;
use Shopware_Components_Document;

class OrderTest extends TestCase
{
    use FixtureBehaviour;
    use DatabaseTransactionBehaviour;

    public function testCreateInvoice(): void
    {
        $orderId = 60001;
        $documentId = 0;
        $this->executeFixture(__DIR__ . '/_fixtures/order.sql');

        $orderDocument = Shopware_Components_Document::initDocument(
            $orderId,
            $documentId,
            $this->getDefaultConfig()
        )->_order;

        $tax = Closure::bind(
            function (): array {
                return $this->_tax;
            },
            $orderDocument,
            $orderDocument
        )();

        static::assertIsArray($tax);
        static::assertArrayHasKey('20.00', $tax);
        static::assertEquals(3.31, $tax['20.00']);
    }

    public function testProportionalTaxCalculation(): void
    {
        $orderId = 60001;
        $documentId = 0;
        $this->executeFixture(__DIR__ . '/_fixtures/order-proportional.sql');

        $orderDocument = Shopware_Components_Document::initDocument(
            $orderId,
            $documentId,
            $this->getDefaultConfig()
        )->_order;

        $amountNetto = Closure::bind(
            function (): float {
                return $this->_amountNetto;
            },
            $orderDocument,
            $orderDocument
        )();

        static::assertEquals(16.355708418891172, $amountNetto);
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
