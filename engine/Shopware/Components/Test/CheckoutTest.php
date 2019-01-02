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

namespace Shopware\Components\Test;

use Shopware\Components\Random;

abstract class CheckoutTest extends \Enlight_Components_Test_Controller_TestCase
{
    public function reset()
    {
        parent::reset();
        Shopware()->Container()->get('dbal_connection')->executeQuery('DELETE FROM s_order_basket');
    }

    /**
     * @param int   $price
     * @param float $taxRate
     *
     * @return string
     */
    protected function createArticle($price = 10, $taxRate = 19.0)
    {
        $resourceHelper = new \Shopware\Tests\Functional\Bundle\StoreFrontBundle\Helper();
        $orderNumber = 'swTEST' . uniqid(rand());

        $resourceHelper->createArticle([
            'name' => 'Testartikel',
            'description' => 'Test description',
            'active' => true,
            'mainDetail' => [
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
            'categories' => [10],
        ]);

        return $orderNumber;
    }

    /**
     * @param float $value
     * @param int   $taxId
     * @param int   $percent
     *
     * @return string
     */
    protected function createVoucher($value, $taxId, $percent = 1)
    {
        $code = Random::getAlphanumericString(12);
        Shopware()->Container()->get('dbal_connection')
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
                'percental' => $percent,
                'taxconfig' => $taxId,
            ]);

        return $code;
    }

    /**
     * @param array  $sBasket
     * @param string $itemName
     * @param float  $itemPrice
     * @param float  $itemNetPrice
     * @param string $itemOrdernumber
     */
    protected function hasBasketItem($sBasket, $itemName, $itemPrice, $itemNetPrice, $itemOrdernumber)
    {
        $cartItemFound = false;
        foreach ($sBasket as $item) {
            if ($item['articlename'] === $itemName) {
                $this->assertEquals($itemOrdernumber, $item['ordernumber']);
                $this->assertEquals($itemNetPrice, $item['netprice']);
                $this->assertEquals($itemPrice, (float) str_replace(',', '.', $item['price']));
                $this->assertEquals($itemOrdernumber, $item['ordernumber']);
                $this->assertEquals(Shopware()->Modules()->Articles()->sFormatPrice($itemPrice - $itemNetPrice), $item['tax']);
                $cartItemFound = true;
            }
        }

        if (!$cartItemFound) {
            $this->fail(sprintf('Cart item by name "%s" not found', $itemName));
        }
    }

    /**
     * @param int    $surchargeAbsolute
     * @param int    $surchargePercent
     * @param string $surchargeCountry
     */
    protected function setPaymentSurcharge($surchargeAbsolute, $surchargePercent = 0, $surchargeCountry = '')
    {
        Shopware()->Container()->get('dbal_connection')->executeQuery('UPDATE s_core_paymentmeans SET surcharge = ?, debit_percent = ?, surchargestring = ?', [
            $surchargeAbsolute,
            $surchargePercent,
            $surchargeCountry,
        ]);
    }

    /**
     * @param float $minOrderValue
     * @param float $surcharge
     */
    protected function setCustomerGroupSurcharge($minOrderValue, $surcharge)
    {
        Shopware()->Container()->get('dbal_connection')->executeQuery('UPDATE s_core_customergroups SET minimumorder = ?, minimumordersurcharge = ?', [
            $minOrderValue,
            $surcharge,
        ]);
    }

    /**
     * @param string $customerGroupKey
     * @param float  $discountStart
     * @param float  $discountValuePercent
     */
    protected function addCustomerGroupDiscount($customerGroupKey, $discountStart, $discountValuePercent)
    {
        $this->clearCustomerGroupDiscount($customerGroupKey);
        Shopware()->Container()->get('dbal_connection')->executeQuery('INSERT INTO s_core_customergroups_discounts VALUES (null, (SELECT id FROM s_core_customergroups WHERE groupkey = ?), ?, ?)', [
            $customerGroupKey,
            $discountValuePercent,
            $discountStart,
        ]);
    }

    /**
     * @param string $customerGroupKey
     */
    protected function clearCustomerGroupDiscount($customerGroupKey)
    {
        Shopware()->Container()->get('dbal_connection')->executeQuery('DELETE FROM s_core_customergroups_discounts WHERE groupID = (SELECT id FROM s_core_customergroups WHERE groupkey = ?)', [$customerGroupKey]);
    }

    /**
     * @param string $orderCode
     * @param string $taxConfig
     */
    protected function setVoucherTax($orderCode, $taxConfig)
    {
        Shopware()->Container()->get('dbal_connection')->update('s_emarketing_vouchers', [
            'taxconfig' => $taxConfig,
        ], [
            'ordercode' => $orderCode,
        ]);
    }
}
