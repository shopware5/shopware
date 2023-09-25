<?php

declare(strict_types=1);
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

namespace Shopware\Tests\Functional\Components;

use Doctrine\DBAL\Connection;
use Enlight_Components_Test_Controller_TestCase as ControllerTestCase;
use Shopware\Components\Random;
use Shopware\Tests\Functional\Bundle\StoreFrontBundle\Helper;
use Shopware\Tests\Functional\Traits\ContainerTrait;
use Shopware\Tests\Functional\Traits\CustomerLoginTrait;
use Shopware\Tests\Functional\Traits\DatabaseTransactionBehaviour;

abstract class CheckoutTestCase extends ControllerTestCase
{
    use ContainerTrait;
    use CustomerLoginTrait;
    use DatabaseTransactionBehaviour;

    public const USER_AGENT = 'Mozilla/5.0 (Android; Tablet; rv:14.0) Gecko/14.0 Firefox/14.0';

    public bool $clearBasketOnReset = true;

    protected Helper $apiHelper;

    public function setUp(): void
    {
        parent::setUp();
        $this->apiHelper = new Helper($this->getContainer());
    }

    public function reset(): void
    {
        parent::reset();

        if ($this->clearBasketOnReset) {
            $this->getContainer()->get(Connection::class)->executeQuery('DELETE FROM s_order_basket');
        }

        $this->Request()->setHeader('User-Agent', self::USER_AGENT);
    }

    protected function createProduct(float $price = 10, float $taxRate = 19.0): string
    {
        $orderNumber = 'swTEST' . uniqid((string) rand());

        $this->apiHelper->createProduct([
            'name' => 'Testartikel',
            'description' => 'Test description',
            'active' => true,
            'mainDetail' => [
                'active' => true,
                'number' => $orderNumber,
                'inStock' => 15,
                'lastStock' => true,
                'unitId' => 1,
                'prices' => [
                    [
                        'customerGroupKey' => 'EK',
                        'from' => 1,
                        'to' => '-',
                        'price' => $price,
                    ],
                ],
            ],
            'tax' => $taxRate,
            'supplierId' => 2,
            'categories' => [
                [
                    'id' => 10,
                ],
            ],
        ]);

        return $orderNumber;
    }

    protected function updateProductPrice(string $orderNumber, float $price, float $taxRate): void
    {
        $this->apiHelper->updateProduct($orderNumber, [
            'mainDetail' => [
                'prices' => [
                    [
                        'customerGroupKey' => 'EK',
                        'from' => 1,
                        'to' => '-',
                        'price' => $price,
                    ],
                ],
            ],
            'tax' => $taxRate,
        ]);
    }

    /**
     * @param 0|1 $percental
     */
    protected function createVoucher(float $value, int $taxId, int $percental = 1): string
    {
        $code = Random::getAlphanumericString(12);
        $this->getContainer()->get(Connection::class)
            ->insert('s_emarketing_vouchers', [
                'description' => 'test voucher',
                'value' => $value,
                'vouchercode' => $code,
                'numberofunits' => 1000,
                'minimumcharge' => 1,
                'shippingfree' => 0,
                'ordercode' => $code,
                'modus' => 0,
                'numorder' => 1000,
                'percental' => $percental,
                'taxconfig' => (string) $taxId,
            ]);

        return $code;
    }

    /**
     * @param array<array<string, mixed>> $sBasket
     */
    protected function hasBasketItem(array $sBasket, string $itemName, float $itemPrice, float $itemNetPrice, string $itemOrdernumber): void
    {
        $cartItemFound = false;
        foreach ($sBasket as $item) {
            if ($item['articlename'] === $itemName) {
                static::assertEquals($itemOrdernumber, $item['ordernumber']);
                static::assertEquals($itemNetPrice, $item['netprice']);
                static::assertEquals($itemPrice, (float) str_replace(',', '.', $item['price']));
                static::assertEquals($itemOrdernumber, $item['ordernumber']);
                static::assertEquals(Shopware()->Modules()->Articles()->sFormatPrice($itemPrice - $itemNetPrice), $item['tax']);
                $cartItemFound = true;
            }
        }

        if (!$cartItemFound) {
            static::fail(sprintf('Cart item by name "%s" not found', $itemName));
        }
    }

    protected function setPaymentSurcharge(float $surchargeAbsolute, float $surchargePercent = 0.0, string $surchargeCountry = ''): void
    {
        $this->getContainer()->get(Connection::class)->executeQuery('UPDATE s_core_paymentmeans SET surcharge = ?, debit_percent = ?, surchargestring = ?', [
            $surchargeAbsolute,
            $surchargePercent,
            $surchargeCountry,
        ]);
    }

    protected function setCustomerGroupSurcharge(float $minOrderValue, float $surcharge): void
    {
        $this->getContainer()->get(Connection::class)->executeQuery('UPDATE s_core_customergroups SET minimumorder = ?, minimumordersurcharge = ?', [
            $minOrderValue,
            $surcharge,
        ]);
    }

    protected function addCustomerGroupDiscount(string $customerGroupKey, float $discountStart, float $discountValuePercent): void
    {
        $this->clearCustomerGroupDiscount($customerGroupKey);
        $this->getContainer()->get(Connection::class)->executeQuery('INSERT INTO s_core_customergroups_discounts VALUES (null, (SELECT id FROM s_core_customergroups WHERE groupkey = ?), ?, ?)', [
            $customerGroupKey,
            $discountValuePercent,
            $discountStart,
        ]);
    }

    protected function clearCustomerGroupDiscount(string $customerGroupKey): void
    {
        $this->getContainer()->get(Connection::class)->executeQuery('DELETE FROM s_core_customergroups_discounts WHERE groupID = (SELECT id FROM s_core_customergroups WHERE groupkey = ?)', [$customerGroupKey]);
    }

    protected function setVoucherTax(string $orderCode, string $taxConfig): void
    {
        $this->getContainer()->get(Connection::class)->update('s_emarketing_vouchers', [
            'taxconfig' => $taxConfig,
        ], [
            'ordercode' => $orderCode,
        ]);
    }

    protected function loginCustomerOfGroup(string $group = 'EK'): void
    {
        $customer = Shopware()->Db()->fetchRow(
            'SELECT id, email, password, subshopID, language FROM s_user WHERE customergroup = ? LIMIT 1',
            $group
        );

        $this->loginCustomer(null, (int) $customer['id'], $customer['email'], null, 2, 3, $group);
    }

    protected function addProduct(string $productNumber, int $quantity = 1): void
    {
        $this->reset();
        $this->Request()->setMethod('POST');
        $this->Request()->setHeader('User-Agent', self::USER_AGENT);
        $this->Request()->setParam('sQuantity', $quantity);
        $this->Request()->setParam('sAdd', $productNumber);
        $this->dispatch('/checkout/addArticle');
    }

    protected function visitCart(): void
    {
        $this->reset();
        $this->dispatch('/checkout/cart');
    }

    protected function visitConfirm(): void
    {
        $this->reset();
        $this->Request()->setMethod('POST');
        $this->dispatch('/checkout/confirm');
    }

    protected function visitFinish(): void
    {
        $this->reset();
        $this->Request()->setMethod('POST');
        $this->Request()->setParam('sAGB', 'on');
        $this->dispatch('/checkout/finish');
    }
}
