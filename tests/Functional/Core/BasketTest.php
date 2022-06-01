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

namespace Shopware\Tests\Functional\Core;

use DateTime;
use Doctrine\DBAL\Connection;
use Enlight_Components_Session_Namespace;
use Enlight_Controller_Request_Request;
use Enlight_Controller_Request_RequestHttp;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use sBasket;
use Shopware\Bundle\CartBundle\CartKey;
use Shopware\Bundle\StoreFrontBundle\Service\ContextServiceInterface;
use Shopware\Components\Api\Resource\Customer as CustomerResource;
use Shopware\Components\Model\ModelManager;
use Shopware\Components\Random;
use Shopware\Models\Article\Detail;
use Shopware\Models\Customer\Customer;
use Shopware\Tests\Functional\Bundle\StoreFrontBundle\Helper;
use Shopware\Tests\Functional\Traits\ContainerTrait;
use Shopware\Tests\Functional\Traits\DatabaseTransactionBehaviour;
use Shopware_Components_Config;
use Shopware_Components_Snippet_Manager;
use Zend_Currency;
use Zend_Locale;

class BasketTest extends TestCase
{
    use ContainerTrait;
    use DatabaseTransactionBehaviour;

    private Connection $connection;

    private sBasket $module;

    private Shopware_Components_Config $config;

    private Enlight_Components_Session_Namespace $session;

    private Shopware_Components_Snippet_Manager $snippetManager;

    public function setUp(): void
    {
        parent::setUp();

        $this->getContainer()->get('front')->setRequest(new Enlight_Controller_Request_RequestHttp());

        $this->snippetManager = $this->getContainer()->get('snippets');
        $this->connection = $this->getContainer()->get(Connection::class);
        $this->module = $this->getContainer()->get('modules')->Basket();
        $this->session = $this->getContainer()->get('session');
        $this->session->offsetSet('sessionId', null);
        $this->config = $this->getContainer()->get('config');
        $currency = $this->connection->fetchAssociative("SELECT * FROM s_core_currencies WHERE currency LIKE 'EUR'");
        static::assertIsArray($currency);
        $this->module->sSYSTEM->sCurrency = $currency;
    }

    public function testsGetAmount(): void
    {
        // Test with empty session, expect empty array
        static::assertEquals([], $this->module->sGetAmount());
        $this->module->sSYSTEM->sSESSION_ID = uniqid((string) mt_rand(), true);
        $this->session->offsetSet('sessionId', $this->module->sSYSTEM->sSESSION_ID);

        $this->connection->insert(
            's_order_basket',
            [
                'price' => 123,
                'quantity' => 2,
                'sessionID' => $this->session->get('sessionId'),
            ]
        );

        static::assertEquals(
            ['totalAmount' => 246],
            $this->module->sGetAmount()
        );

        $this->connection->delete(
            's_order_basket',
            ['sessionID' => $this->session->get('sessionId')]
        );
    }

    public function testsCheckBasketQuantitiesWithEmptySession(): void
    {
        $this->generateBasketSession();

        // Test with empty session, expect empty array
        static::assertEquals(
            ['hideBasket' => false, 'articles' => []],
            $this->module->sCheckBasketQuantities()
        );
    }

    public function testsCheckBasketQuantitiesWithLowerQuantityThanAvailable(): void
    {
        $this->generateBasketSession();

        // Fetch a product in stock with stock control
        // Add stock-1 to basket
        // Check that basket is valid
        $inStockProduct = $this->connection->fetchAssociative(
            'SELECT * FROM s_articles_details detail
            INNER JOIN s_articles product
              ON product.id = detail.articleID
            WHERE detail.instock > 2
            AND detail.active = 1
            AND detail.lastStock = 1
            LIMIT 1'
        );
        static::assertIsArray($inStockProduct);

        $this->connection->insert(
            's_order_basket',
            [
                'price' => 123,
                'quantity' => $inStockProduct['instock'] - 1,
                'sessionID' => $this->session->get('sessionId'),
                'ordernumber' => $inStockProduct['ordernumber'],
                'articleID' => $inStockProduct['articleID'],
            ]
        );

        $result = $this->module->sCheckBasketQuantities();
        static::assertIsArray($result);
        static::assertArrayHasKey('hideBasket', $result);
        static::assertArrayHasKey('articles', $result);
        static::assertFalse($result['hideBasket']);
        static::assertArrayHasKey($inStockProduct['ordernumber'], $result['articles']);
        static::assertFalse($result['articles'][$inStockProduct['ordernumber']]['OutOfStock']);
    }

    public function testsCheckBasketQuantitiesWithHigherQuantityThanAvailable(): void
    {
        $this->generateBasketSession();

        // Fetch a product in stock with stock control
        // Add stock+1 to basket
        // Check that basket is invalid
        $outStockProduct = $this->connection->fetchAssociative(
            'SELECT * FROM s_articles_details detail
            INNER JOIN s_articles product
              ON product.id = detail.articleID
            WHERE detail.instock > 5
            AND detail.active = 1
            AND detail.lastStock = 1
            AND product.active = 1
            LIMIT 1'
        );
        static::assertIsArray($outStockProduct);

        $this->connection->insert(
            's_order_basket',
            [
                'price' => 123,
                'quantity' => $outStockProduct['instock'] + 1,
                'sessionID' => $this->session->get('sessionId'),
                'ordernumber' => $outStockProduct['ordernumber'],
                'articleID' => $outStockProduct['articleID'],
            ]
        );

        $inStockProduct = $this->connection->fetchAssociative(
            'SELECT * FROM s_articles_details detail
            INNER JOIN s_articles product
              ON product.id = detail.articleID
            WHERE detail.instock > 5
            AND detail.active = 1
            AND detail.lastStock = 1
            AND product.active = 1
            AND product.id != "' . $outStockProduct['articleID'] . '"
            LIMIT 1'
        );
        static::assertIsArray($inStockProduct);

        $this->connection->insert(
            's_order_basket',
            [
                'price' => 123,
                'quantity' => $inStockProduct['instock'] - 1,
                'sessionID' => $this->session->get('sessionId'),
                'ordernumber' => $inStockProduct['ordernumber'],
                'articleID' => $inStockProduct['articleID'],
            ]
        );

        $result = $this->module->sCheckBasketQuantities();
        static::assertIsArray($result);
        static::assertArrayHasKey('hideBasket', $result);
        static::assertArrayHasKey('articles', $result);
        static::assertTrue($result['hideBasket']);
        static::assertArrayHasKey($inStockProduct['ordernumber'], $result['articles']);
        static::assertFalse($result['articles'][$inStockProduct['ordernumber']]['OutOfStock']);
        static::assertArrayHasKey($outStockProduct['ordernumber'], $result['articles']);
        static::assertTrue($result['articles'][$outStockProduct['ordernumber']]['OutOfStock']);

        // Clear the current cart
        $this->connection->delete(
            's_order_basket',
            ['sessionID' => $this->session->get('sessionId')]
        );
    }

    public function testsCheckBasketQuantitiesWithoutStockControl(): void
    {
        $this->generateBasketSession();

        // Fetch a product in stock without stock control
        // Add stock+1 to basket
        // Check that basket is valid
        $ignoreStockProduct = $this->connection->fetchAssociative(
            'SELECT * FROM s_articles_details detail
            INNER JOIN s_articles product
              ON product.id = detail.articleID
            WHERE detail.active = 1
            AND detail.lastStock = 0
            LIMIT 1'
        );
        static::assertIsArray($ignoreStockProduct);

        $this->connection->insert(
            's_order_basket',
            [
                'price' => 123,
                'quantity' => $ignoreStockProduct['instock'] + 1,
                'sessionID' => $this->session->get('sessionId'),
                'ordernumber' => $ignoreStockProduct['ordernumber'],
                'articleID' => $ignoreStockProduct['articleID'],
            ]
        );

        $result = $this->module->sCheckBasketQuantities();
        static::assertIsArray($result);
        static::assertArrayHasKey('hideBasket', $result);
        static::assertArrayHasKey('articles', $result);
        static::assertFalse($result['hideBasket']);
        static::assertArrayHasKey($ignoreStockProduct['ordernumber'], $result['articles']);
        static::assertFalse($result['articles'][$ignoreStockProduct['ordernumber']]['OutOfStock']);

        // Housekeeping
        $this->connection->delete(
            's_order_basket',
            ['sessionID' => $this->session->get('sessionId')]
        );
    }

    public function testsGetAmountRestrictedArticles(): void
    {
        // Null arguments, empty basket, expect empty array
        static::assertEquals(
            [],
            $this->invokeMethod(
                $this->module,
                'sGetAmountRestrictedArticles',
                [null, null]
            )
        );

        $this->generateBasketSession();

        // Add two products to the basket
        $randomProductOne = $this->getRandomProduct();
        $randomProductTwo = $this->getRandomProduct(null, $randomProductOne['supplierID']);

        $this->connection->insert(
            's_order_basket',
            [
                'price' => 2,
                'quantity' => 1,
                'sessionID' => $this->session->get('sessionId'),
                'ordernumber' => $randomProductOne['ordernumber'],
                'articleID' => $randomProductOne['articleID'],
            ]
        );
        $this->connection->insert(
            's_order_basket',
            [
                'price' => 3,
                'quantity' => 1,
                'sessionID' => $this->session->get('sessionId'),
                'ordernumber' => $randomProductTwo['ordernumber'],
                'articleID' => $randomProductTwo['articleID'],
            ]
        );

        // No filters, expect total basket value
        static::assertEquals(
            ['totalAmount' => 5],
            $this->invokeMethod(
                $this->module,
                'sGetAmountRestrictedArticles',
                [null, null]
            )
        );

        // Filter by product one supplier, expect product one value
        static::assertEquals(
            ['totalAmount' => 2],
            $this->invokeMethod(
                $this->module,
                'sGetAmountRestrictedArticles',
                [null, $randomProductOne['supplierID']]
            )
        );
        // Filter by product two supplier, expect product two value
        static::assertEquals(
            ['totalAmount' => 3],
            $this->invokeMethod(
                $this->module,
                'sGetAmountRestrictedArticles',
                [null, $randomProductTwo['supplierID']]
            )
        );
        // Filter by other supplier, expect empty array
        static::assertEquals(
            [],
            $this->invokeMethod(
                $this->module,
                'sGetAmountRestrictedArticles',
                [null, -1]
            )
        );

        // Filter by product one, expect product one value
        static::assertEquals(
            ['totalAmount' => 2],
            $this->invokeMethod(
                $this->module,
                'sGetAmountRestrictedArticles',
                [[$randomProductOne['ordernumber']], null]
            )
        );
        // Filter by product two, expect product two value
        static::assertEquals(
            ['totalAmount' => 3],
            $this->invokeMethod(
                $this->module,
                'sGetAmountRestrictedArticles',
                [[$randomProductTwo['ordernumber']], null]
            )
        );
        // Filter by both articles, expect total basket value
        static::assertEquals(
            ['totalAmount' => 5],
            $this->invokeMethod(
                $this->module,
                'sGetAmountRestrictedArticles',
                [
                    [$randomProductOne['ordernumber'], $randomProductTwo['ordernumber']],
                    null,
                ]
            )
        );
        // Filter by another product, expect empty value
        static::assertEquals(
            [],
            $this->invokeMethod(
                $this->module,
                'sGetAmountRestrictedArticles',
                [
                    [-1],
                    null,
                ]
            )
        );

        // Housekeeping
        $this->connection->delete(
            's_order_basket',
            ['sessionID' => $this->session->get('sessionId')]
        );
    }

    public function testsInsertPremium(): void
    {
        // Test with empty session, expect true
        static::assertTrue($this->module->sInsertPremium());

        // Create session id
        $this->module->sSYSTEM->sSESSION_ID = uniqid((string) mt_rand(), true);
        $this->session->offsetSet('sessionId', $this->module->sSYSTEM->sSESSION_ID);

        // Test with session, expect true
        static::assertTrue($this->module->sInsertPremium());

        $normalProduct = $this->connection->fetchAssociative(
            'SELECT * FROM s_articles_details detail
             INNER JOIN s_articles product
                ON product.id = detail.articleID
             WHERE detail.active = 1
             AND detail.articleId NOT IN (
                SELECT id FROM s_addon_premiums
             )
             LIMIT 1'
        );
        static::assertIsArray($normalProduct);

        $premiumProductOne = $this->connection->fetchAssociative(
            'SELECT product.id, detail.ordernumber
             FROM s_articles_details detail
             INNER JOIN s_articles product
                ON product.id = detail.articleID
             WHERE detail.active = 1
             AND detail.ordernumber NOT IN (
                SELECT ordernumber FROM s_addon_premiums
             )
             LIMIT 1'
        );
        static::assertIsArray($premiumProductOne);
        $premiumProductTwo = $this->connection->fetchAssociative(
            'SELECT product.id, detail.ordernumber
            FROM s_articles_details detail
            INNER JOIN s_articles product
              ON product.id = detail.articleID
            WHERE detail.active = 1
            AND detail.ordernumber IN (
              SELECT ordernumber FROM s_addon_premiums
            )
            LIMIT 1'
        );
        static::assertIsArray($premiumProductTwo);

        // Add one normal product to basket
        // Test that calling sInsertPremium does nothing
        $this->connection->insert(
            's_order_basket',
            [
                'price' => 1,
                'quantity' => 1,
                'sessionID' => $this->session->get('sessionId'),
                'ordernumber' => $normalProduct['ordernumber'],
                'articleID' => $normalProduct['articleID'],
                'modus' => 0,
            ]
        );
        static::assertTrue($this->module->sInsertPremium());
        static::assertEquals(
            1,
            $this->connection->fetchOne(
                'SELECT count(*) FROM s_order_basket WHERE sessionID = ?',
                [$this->module->sSYSTEM->sSESSION_ID]
            )
        );

        // Add premium articles to basket
        // Test that calling sInsertPremium removes them
        $this->connection->insert(
            's_order_basket',
            [
                'price' => 1,
                'quantity' => 1,
                'sessionID' => $this->session->get('sessionId'),
                'ordernumber' => $premiumProductOne['ordernumber'],
                'articleID' => $premiumProductOne['id'],
                'modus' => 1,
            ]
        );
        $this->connection->insert(
            's_order_basket',
            [
                'price' => 1,
                'quantity' => 1,
                'sessionID' => $this->session->get('sessionId'),
                'ordernumber' => $premiumProductTwo['ordernumber'],
                'articleID' => $premiumProductTwo['id'],
                'modus' => 1,
            ]
        );
        static::assertTrue($this->module->sInsertPremium());
        static::assertEquals(
            1,
            $this->connection->fetchOne(
                'SELECT count(*) FROM s_order_basket WHERE sessionID = ?',
                [$this->module->sSYSTEM->sSESSION_ID]
            )
        );

        // Add sAddPremium to _GET.
        // Basket price is 1, so expect premium articles to be denied
        $request = $this->getContainer()->get('front')->Request();
        static::assertInstanceOf(Enlight_Controller_Request_RequestHttp::class, $request);
        $request->setQuery('sAddPremium', $premiumProductTwo['ordernumber']);
        static::assertFalse($this->module->sInsertPremium());

        // Increase basket price and retry
        $this->connection->insert(
            's_order_basket',
            [
                'price' => 10000,
                'quantity' => 1,
                'sessionID' => $this->session->get('sessionId'),
                'ordernumber' => $normalProduct['ordernumber'],
                'articleID' => $normalProduct['articleID'],
                'modus' => 0,
            ]
        );
        // Will still get false due to cache
        static::assertFalse($this->module->sInsertPremium());

        // Change the premium product to a non-premium, fail
        $request->setQuery('sAddPremium', $premiumProductOne['ordernumber']);
        static::assertFalse($this->module->sInsertPremium());
        static::assertEquals(
            2,
            $this->connection->fetchOne(
                'SELECT count(*) FROM s_order_basket WHERE sessionID = ?',
                [$this->module->sSYSTEM->sSESSION_ID]
            )
        );

        // Change the premium product to a premium, succeed
        $request->setQuery('sAddPremium', $premiumProductTwo['ordernumber']);
        static::assertGreaterThan(0, $this->module->sInsertPremium());
        static::assertEquals(
            3,
            $this->connection->fetchOne(
                'SELECT count(*) FROM s_order_basket WHERE sessionID = ?',
                [$this->module->sSYSTEM->sSESSION_ID]
            )
        );

        // Housekeeping
        $this->connection->delete(
            's_order_basket',
            ['sessionID' => $this->session->get('sessionId')]
        );
    }

    public function testPremiumOrderNumberExport(): void
    {
        $this->connection->beginTransaction();

        // Create session id
        $this->module->sSYSTEM->sSESSION_ID = uniqid((string) mt_rand(), true);
        $this->session->offsetSet('sessionId', $this->module->sSYSTEM->sSESSION_ID);

        // Test with session, expect true
        static::assertTrue($this->module->sInsertPremium());

        $ordernumberExport = 'test' . random_int(1000, 9999);

        // Insert a new Premium product that has a ordernumber_export different from the ordernumber
        $this->connection->insert('s_addon_premiums', ['startprice' => 0, 'ordernumber' => 'SW10137', 'ordernumber_export' => $ordernumberExport, 'subshopID' => 0]);

        // sInsertPremium gets the premium to add from the Request, therefore we do set it here
        $front = $this->getContainer()->get('front');
        static::assertInstanceOf(Enlight_Controller_Request_Request::class, $front->Request());
        $front->Request()->setQuery('sAddPremium', 'SW10137');

        // add the premium item to the basket
        $this->module->sInsertPremium();

        // check if the ordernumber_export from s_addon_premiums has been added to s_order_basket as ordernumber
        static::assertEquals(
            $ordernumberExport,
            $this->connection->fetchOne(
                'SELECT ordernumber FROM s_order_basket WHERE sessionID = ? AND modus = 1',
                [$this->module->sSYSTEM->sSESSION_ID]
            )
        );

        $this->getContainer()->get('front')->setRequest(new Enlight_Controller_Request_RequestHttp());
        $this->connection->rollBack();
    }

    public function testGetMaxTax(): void
    {
        // Test with empty session, expect false
        static::assertFalse($this->module->getMaxTax());

        // Create session id
        $this->module->sSYSTEM->sSESSION_ID = uniqid((string) mt_rand(), true);
        $this->session->offsetSet('sessionId', $this->module->sSYSTEM->sSESSION_ID);

        // Test with session and empty basket, expect false
        static::assertFalse($this->module->getMaxTax());

        $products = $this->connection->fetchAllAssociative(
            'SELECT * FROM s_articles_details detail
             INNER JOIN s_articles product
               ON product.id = detail.articleID
             INNER JOIN s_core_tax tax
               ON tax.id = product.taxID
             WHERE detail.active = 1
             ORDER BY tax.tax
             LIMIT 2'
        );
        $originalTaxId = $products[0]['taxID'];

        $this->connection->update('s_articles', ['taxID' => 4], ['id' => $products[0]['id']]);

        // Add one product, check that he is the new maximum
        $this->connection->insert(
            's_order_basket',
            [
                'price' => 100,
                'quantity' => 1,
                'sessionID' => $this->session->get('sessionId'),
                'ordernumber' => $products[0]['ordernumber'],
                'articleID' => $products[0]['articleID'],
                'tax_rate' => $products[0]['tax'],
            ]
        );
        static::assertEquals($products[0]['tax'], $this->module->getMaxTax());

        // Add another product, check that we get the max of the two
        $this->connection->insert(
            's_order_basket',
            [
                'price' => 100,
                'quantity' => 1,
                'sessionID' => $this->session->get('sessionId'),
                'ordernumber' => $products[1]['ordernumber'],
                'articleID' => $products[1]['articleID'],
                'tax_rate' => $products[1]['tax'],
            ]
        );
        static::assertEquals($products[1]['tax'], $this->module->getMaxTax());

        $this->connection->update('s_articles', ['taxID' => $originalTaxId], ['id' => $products[0]['id']]);

        // Housekeeping
        $this->connection->delete(
            's_order_basket',
            ['sessionID' => $this->session->get('sessionId')]
        );
    }

    public function testsAddVoucherWithAbsoluteVoucher(): void
    {
        // Test with empty args and session, expect failure
        $result = $this->module->sAddVoucher('');
        static::assertIsArray($result);
        static::assertArrayHasKey('sErrorFlag', $result);
        static::assertArrayHasKey('sErrorMessages', $result);
        static::assertTrue($result['sErrorFlag']);
        static::assertContains(
            $this->snippetManager->getNamespace('frontend/basket/internalMessages')
                ->get('VoucherFailureNotFound', 'Voucher could not be found or is not valid anymore'),
            $result['sErrorMessages']
        );

        // Create session id and try again, same results
        $this->module->sSYSTEM->sSESSION_ID = uniqid((string) mt_rand(), true);
        $this->session->offsetSet('sessionId', $this->module->sSYSTEM->sSESSION_ID);
        $result = $this->module->sAddVoucher('');
        static::assertIsArray($result);
        static::assertArrayHasKey('sErrorFlag', $result);
        static::assertArrayHasKey('sErrorMessages', $result);
        static::assertTrue($result['sErrorFlag']);
        static::assertContains(
            $this->snippetManager->getNamespace('frontend/basket/internalMessages')
                ->get('VoucherFailureNotFound', 'Voucher could not be found or is not valid anymore'),
            $result['sErrorMessages']
        );

        // Try with valid voucher code, empty basket
        $voucherData = [
            'vouchercode' => 'testOne',
            'description' => 'testOne description',
            'numberofunits' => 1,
            'value' => 10,
            'minimumcharge' => 10,
            'ordercode' => uniqid((string) mt_rand(), true),
            'modus' => 0,
        ];
        $this->connection->insert('s_emarketing_vouchers', $voucherData);
        $this->module->sSYSTEM->sSESSION_ID = uniqid((string) mt_rand(), true);
        $this->session->offsetSet('sessionId', $this->module->sSYSTEM->sSESSION_ID);
        $result = $this->module->sAddVoucher('testOne');

        static::assertIsArray($result);
        static::assertArrayHasKey('sErrorFlag', $result);
        static::assertArrayHasKey('sErrorMessages', $result);
        static::assertTrue($result['sErrorFlag']);
        static::assertStringContainsString('Der Mindestumsatz für diesen Gutschein beträgt 10,00&nbsp;&euro;', $result['sErrorMessages'][0]);

        // Check if a currency switch is reflected in the snippet correctly
        $currencyDe = $this->getContainer()->get(Zend_Currency::class);
        $this->getContainer()->set('currency', new Zend_Currency('GBP', new Zend_Locale('en_GB')));

        $this->module->sSYSTEM->sSESSION_ID = uniqid((string) mt_rand(), true);
        $this->session->offsetSet('sessionId', $this->module->sSYSTEM->sSESSION_ID);
        $result = $this->module->sAddVoucher('testOne');
        static::assertIsArray($result);
        static::assertArrayHasKey('sErrorFlag', $result);
        static::assertArrayHasKey('sErrorMessages', $result);
        static::assertTrue($result['sErrorFlag']);

        static::assertContains('Der Mindestumsatz für diesen Gutschein beträgt &pound;10.00', $result['sErrorMessages']);

        $this->getContainer()->set('currency', $currencyDe);

        // Add one product to the basket with enough value to use discount
        $randomProduct = $this->getRandomProduct();
        $this->connection->insert(
            's_order_basket',
            [
                'price' => $voucherData['minimumcharge'] + 1,
                'quantity' => 1,
                'sessionID' => $this->session->get('sessionId'),
                'ordernumber' => $randomProduct['ordernumber'],
                'articleID' => $randomProduct['articleID'],
            ]
        );

        // Add voucher to the orders table, so we can test the usage limit
        $this->connection->insert(
            's_order_details',
            [
                'orderID' => 15,
                'articleordernumber' => $voucherData['ordercode'],
            ]
        );
        $result = $this->module->sAddVoucher('testOne');
        static::assertIsArray($result);
        static::assertArrayHasKey('sErrorFlag', $result);
        static::assertArrayHasKey('sErrorMessages', $result);
        static::assertTrue($result['sErrorFlag']);
        static::assertContains(
            $this->snippetManager->getNamespace('frontend/basket/internalMessages')->get(
                'VoucherFailureNotFound',
                'Voucher could not be found or is not valid anymore'
            ),
            $result['sErrorMessages']
        );
        $this->connection->delete(
            's_order_details',
            [
                'articleordernumber' => $voucherData['ordercode'],
            ]
        );

        $previousAmount = $this->module->sGetAmount();
        // Voucher should work ok now
        static::assertTrue($this->module->sAddVoucher('testOne'));
        static::assertLessThan($previousAmount, $this->module->sGetAmount());

        // Test the voucher values with tax from user group
        $discount = $this->connection->fetchAssociative(
            'SELECT * FROM s_order_basket WHERE modus = 2 and sessionID = ?',
            [$this->module->sSYSTEM->sSESSION_ID]
        );
        static::assertIsArray($discount);
        static::assertEquals($voucherData['value'] * -1, $discount['price']);
        static::assertEquals($this->config->offsetGet('sVOUCHERTAX'), $discount['tax_rate']);
        static::assertEquals($voucherData['value'] * -1, round($discount['netprice'] * (100 + $discount['tax_rate']) / 100));

        // Second voucher should fail
        $result = $this->module->sAddVoucher('testOne');
        static::assertIsArray($result);
        static::assertArrayHasKey('sErrorFlag', $result);
        static::assertArrayHasKey('sErrorMessages', $result);
        static::assertTrue($result['sErrorFlag']);
        static::assertContains(
            $this->snippetManager->getNamespace('frontend/basket/internalMessages')->get(
                'VoucherFailureOnlyOnes',
                'Only one voucher can be processed in order'
            ),
            $result['sErrorMessages']
        );

        // Housekeeping
        $this->connection->delete(
            's_order_basket',
            ['sessionID' => $this->session->get('sessionId')]
        );
        $this->connection->delete(
            's_emarketing_vouchers',
            ['vouchercode' => 'testOne']
        );
    }

    public function testsAddVoucherWithLimitedVoucher(): void
    {
        $voucherData = [
            'vouchercode' => 'testTwo',
            'description' => 'testTwo description',
            'numberofunits' => 10,
            'value' => 10,
            'minimumcharge' => 10,
            'ordercode' => uniqid((string) mt_rand(), true),
            'modus' => 1,
            'taxconfig' => 'none',
        ];
        $this->connection->insert(
            's_emarketing_vouchers',
            $voucherData
        );
        $voucherId = $this->connection->lastInsertId();

        $voucherCodeData = [
            'voucherID' => $voucherId,
            'code' => uniqid((string) mt_rand(), true),
            'userID' => null,
            'cashed' => 0,
        ];
        $this->connection->insert(
            's_emarketing_voucher_codes',
            $voucherCodeData
        );

        $customer = $this->createDummyCustomer();
        $this->session['sUserId'] = $customer->getId();
        $this->module->sSYSTEM->sSESSION_ID = uniqid((string) mt_rand(), true);
        $this->session->offsetSet('sessionId', $this->module->sSYSTEM->sSESSION_ID);

        // Test with one-time code, fail due to minimum amount (cart is empty)
        $result = $this->module->sAddVoucher($voucherCodeData['code']);
        static::assertIsArray($result);
        static::assertArrayHasKey('sErrorFlag', $result);
        static::assertArrayHasKey('sErrorMessages', $result);
        static::assertTrue($result['sErrorFlag']);
        static::assertContains('Der Mindestumsatz für diesen Gutschein beträgt 10,00&nbsp;&euro;', $result['sErrorMessages']);

        // Add one product to the basket with enough value to use discount
        $randomProduct = $this->getRandomProduct();
        $this->connection->insert(
            's_order_basket',
            [
                'price' => $voucherData['minimumcharge'] + 1,
                'quantity' => 1,
                'sessionID' => $this->session->get('sessionId'),
                'ordernumber' => $randomProduct['ordernumber'],
                'articleID' => $randomProduct['articleID'],
            ]
        );

        $previousAmount = $this->module->sGetAmount();
        // Test with one-time code, success
        static::assertTrue($this->module->sAddVoucher($voucherCodeData['code']));
        static::assertLessThan($previousAmount, $this->module->sGetAmount());

        // Test the voucher values. This voucher has no taxes
        $discount = $this->connection->fetchAssociative(
            'SELECT * FROM s_order_basket WHERE modus = 2 and sessionID = ?',
            [$this->module->sSYSTEM->sSESSION_ID]
        );
        static::assertIsArray($discount);
        static::assertEquals($voucherData['value'] * -1, $discount['price']);
        static::assertEquals($voucherData['value'] * -1, $discount['netprice']);
        static::assertEquals(0, $discount['tax_rate']);

        // Test again with the same one-time code, fail
        $result = $this->module->sAddVoucher($voucherCodeData['code']);
        static::assertIsArray($result);
        static::assertArrayHasKey('sErrorFlag', $result);
        static::assertArrayHasKey('sErrorMessages', $result);
        static::assertTrue($result['sErrorFlag']);
        static::assertContains(
            $this->snippetManager->getNamespace('frontend/basket/internalMessages')->get(
                'VoucherFailureOnlyOnes',
                'Only one voucher can be processed in order'
            ),
            $result['sErrorMessages']
        );

        // Housekeeping
        $this->connection->delete(
            's_order_basket',
            ['sessionID' => $this->session->get('sessionId')]
        );
        $this->connection->delete(
            's_emarketing_vouchers',
            ['vouchercode' => 'testOne']
        );
        $this->connection->delete(
            's_emarketing_voucher_codes',
            ['code' => $voucherCodeData['code']]
        );
        $this->deleteDummyCustomer($customer);
    }

    public function testsAddVoucherWithSubShopVoucher(): void
    {
        $oldTaxValue = $this->module->sSYSTEM->sUSERGROUPDATA['tax'];
        $this->module->sSYSTEM->sUSERGROUPDATA['tax'] = null;

        $tax = $this->connection->fetchAssociative('SELECT * FROM s_core_tax WHERE tax <> 19 LIMIT 1');
        static::assertIsArray($tax);

        $voucherData = [
            'vouchercode' => 'testTwo',
            'description' => 'testTwo description',
            'numberofunits' => 1,
            'numorder' => 1,
            'value' => 10,
            'minimumcharge' => 10,
            'ordercode' => uniqid((string) random_int(PHP_INT_MIN, PHP_INT_MAX), true),
            'modus' => 0,
            'subshopID' => 3,
            'taxconfig' => $tax['id'],
        ];
        $this->connection->insert(
            's_emarketing_vouchers',
            $voucherData
        );

        $customer = $this->createDummyCustomer();
        $this->session['sUserId'] = $customer->getId();
        $this->module->sSYSTEM->sSESSION_ID = uniqid((string) random_int(PHP_INT_MIN, PHP_INT_MAX), true);
        $this->session->offsetSet('sessionId', $this->module->sSYSTEM->sSESSION_ID);

        // Add one product to the basket with enough value to use discount
        $randomProduct = $this->getRandomProduct();
        $this->connection->insert(
            's_order_basket',
            [
                'price' => $voucherData['minimumcharge'] + 1,
                'quantity' => 1,
                'sessionID' => $this->session->get('sessionId'),
                'ordernumber' => $randomProduct['ordernumber'],
                'articleID' => $randomProduct['articleID'],
            ]
        );

        // Change current subshop id, test and expect success
        $this->getContainer()->get(ContextServiceInterface::class)->getShopContext()->getShop()->setId(3);

        $previousAmount = $this->module->sGetAmount();
        // Test with one-time code, success
        static::assertTrue($this->module->sAddVoucher($voucherData['vouchercode']));
        static::assertLessThan($previousAmount, $this->module->sGetAmount());

        // Test the voucher values with custom tax from voucher
        $discount = $this->connection->fetchAssociative(
            'SELECT * FROM s_order_basket WHERE modus = 2 and sessionID = ?',
            [$this->module->sSYSTEM->sSESSION_ID]
        );
        static::assertIsArray($discount);
        static::assertEquals($voucherData['value'] * -1, $discount['price']);
        static::assertEquals((float) $tax['tax'], (float) $discount['tax_rate']);

        // Test again with the same one-time code, fail
        $result = $this->module->sAddVoucher($voucherData['vouchercode']);
        static::assertIsArray($result);
        static::assertArrayHasKey('sErrorFlag', $result);
        static::assertArrayHasKey('sErrorMessages', $result);
        static::assertTrue($result['sErrorFlag']);
        static::assertContains(
            $this->snippetManager->getNamespace('frontend/basket/internalMessages')->get(
                'VoucherFailureOnlyOnes',
                'Only one voucher can be processed in order'
            ),
            $result['sErrorMessages']
        );

        // Housekeeping
        $this->connection->delete(
            's_order_basket',
            ['sessionID' => $this->session->get('sessionId')]
        );
        $this->connection->delete(
            's_emarketing_vouchers',
            ['vouchercode' => $voucherData['vouchercode']]
        );
        $this->deleteDummyCustomer($customer);
        $this->module->sSYSTEM->sUSERGROUPDATA['tax'] = $oldTaxValue;

        $this->getContainer()->get(ContextServiceInterface::class)->getShopContext()->getShop()->setId(1);
    }

    public function testsAddVoucherWithMultipleVouchers(): void
    {
        $voucherOneData = [
            'vouchercode' => 'testOne',
            'description' => 'testOne description',
            'numberofunits' => 1,
            'numorder' => 1,
            'value' => 10,
            'minimumcharge' => 10,
            'ordercode' => uniqid((string) mt_rand(), true),
            'modus' => 0,
            'subshopID' => 3,
        ];
        $this->connection->insert(
            's_emarketing_vouchers',
            $voucherOneData
        );

        $voucherTwoData = [
            'vouchercode' => 'testTwo',
            'description' => 'testTwo description',
            'numberofunits' => 1,
            'numorder' => 1,
            'value' => 10,
            'minimumcharge' => 10,
            'ordercode' => uniqid((string) mt_rand(), true),
            'modus' => 0,
            'subshopID' => 3,
        ];
        $this->connection->insert(
            's_emarketing_vouchers',
            $voucherTwoData
        );

        $customer = $this->createDummyCustomer();
        $this->session['sUserId'] = $customer->getId();
        $this->module->sSYSTEM->sSESSION_ID = uniqid((string) mt_rand(), true);
        $this->session->offsetSet('sessionId', $this->module->sSYSTEM->sSESSION_ID);

        // Add one product to the basket with enough value to use discount
        $randomProduct = $this->getRandomProduct();
        $this->connection->insert(
            's_order_basket',
            [
                'price' => $voucherOneData['minimumcharge'] + 1,
                'quantity' => 1,
                'sessionID' => $this->session->get('sessionId'),
                'ordernumber' => $randomProduct['ordernumber'],
                'articleID' => $randomProduct['articleID'],
            ]
        );

        // Change current subshop, test and expect success
        $this->getContainer()->get(ContextServiceInterface::class)->getShopContext()->getShop()->setId(3);

        $previousAmount = $this->module->sGetAmount();
        // Test with one-time code, success
        static::assertTrue($this->module->sAddVoucher($voucherOneData['vouchercode']));
        static::assertLessThan($previousAmount, $this->module->sGetAmount());

        // Test again with the same one-time code, fail
        $result = $this->module->sAddVoucher($voucherTwoData['vouchercode']);
        static::assertIsArray($result);
        static::assertArrayHasKey('sErrorFlag', $result);
        static::assertArrayHasKey('sErrorMessages', $result);
        static::assertTrue($result['sErrorFlag']);
        static::assertContains(
            $this->snippetManager->getNamespace('frontend/basket/internalMessages')->get(
                'VoucherFailureOnlyOnes',
                'Only one voucher can be processed in order'
            ),
            $result['sErrorMessages']
        );

        // Housekeeping
        $this->connection->delete(
            's_order_basket',
            ['sessionID' => $this->session->get('sessionId')]
        );
        $this->connection->delete(
            's_emarketing_vouchers',
            ['vouchercode' => $voucherOneData['vouchercode']]
        );
        $this->connection->delete(
            's_emarketing_vouchers',
            ['vouchercode' => $voucherTwoData['vouchercode']]
        );
        $this->deleteDummyCustomer($customer);

        $this->getContainer()->get(ContextServiceInterface::class)->getShopContext()->getShop()->setId(1);
    }

    public function testsAddVoucherWithCustomerGroup(): void
    {
        $randomCustomerGroup = $this->connection->fetchAllAssociative(
            'SELECT * FROM s_core_customergroups
             LIMIT 2'
        );
        $voucherData = [
            'vouchercode' => 'testTwo',
            'description' => 'testTwo description',
            'numberofunits' => 1,
            'numorder' => 1,
            'value' => 10,
            'minimumcharge' => 10,
            'ordercode' => uniqid((string) mt_rand(), true),
            'modus' => 0,
            'customergroup' => $randomCustomerGroup[0]['id'],
        ];
        // Try with valid voucher code, empty basket
        $this->connection->insert(
            's_emarketing_vouchers',
            $voucherData
        );

        $customer = $this->createDummyCustomer();
        $this->connection->update(
            's_user',
            ['customergroup' => $randomCustomerGroup[1]['groupkey']],
            ['id' => $customer->getId()]
        );
        $this->module->sSYSTEM->sUSERGROUPDATA['id'] = $randomCustomerGroup[1]['id'];
        $this->session['sUserId'] = $customer->getId();
        $this->module->sSYSTEM->sSESSION_ID = uniqid((string) mt_rand(), true);
        $this->session->offsetSet('sessionId', $this->module->sSYSTEM->sSESSION_ID);

        // Add one product to the basket with enough value to use discount
        $randomProduct = $this->getRandomProduct();
        $this->connection->insert(
            's_order_basket',
            [
                'price' => $voucherData['minimumcharge'] + 1,
                'quantity' => 1,
                'sessionID' => $this->session->get('sessionId'),
                'ordernumber' => $randomProduct['ordernumber'],
                'articleID' => $randomProduct['articleID'],
            ]
        );

        // Test again with the same one-time code, fail
        $result = $this->module->sAddVoucher($voucherData['vouchercode']);
        static::assertIsArray($result);
        static::assertArrayHasKey('sErrorFlag', $result);
        static::assertArrayHasKey('sErrorMessages', $result);
        static::assertTrue($result['sErrorFlag']);
        static::assertContains(
            $this->snippetManager->getNamespace('frontend/basket/internalMessages')->get(
                'VoucherFailureCustomerGroup',
                'This voucher is not available for your customer group'
            ),
            $result['sErrorMessages']
        );

        // Change the user's customer group
        $this->connection->update(
            's_user',
            ['customergroup' => $randomCustomerGroup[0]['groupkey']],
            ['id' => $customer->getId()]
        );
        $this->module->sSYSTEM->sUSERGROUPDATA['id'] = $randomCustomerGroup[0]['id'];

        $previousAmount = $this->module->sGetAmount();
        // Test with one-time code, success
        static::assertTrue($this->module->sAddVoucher($voucherData['vouchercode']));
        static::assertLessThan($previousAmount, $this->module->sGetAmount());

        // Housekeeping
        $this->connection->delete(
            's_order_basket',
            ['sessionID' => $this->session->get('sessionId')]
        );
        $this->connection->delete(
            's_emarketing_vouchers',
            ['vouchercode' => $voucherData['vouchercode']]
        );
        $this->deleteDummyCustomer($customer);
    }

    public function testsAddVoucherWithArticle(): void
    {
        $randomArticles = $this->connection->fetchAllAssociative(
            'SELECT * FROM s_articles_details detail
            INNER JOIN s_articles product
              ON product.id = detail.articleID
            WHERE detail.active = 1
             LIMIT 2'
        );
        $voucherData = [
            'vouchercode' => 'testOne',
            'description' => 'testOne description',
            'numberofunits' => 1,
            'numorder' => 1,
            'value' => 10,
            'minimumcharge' => 10,
            'ordercode' => uniqid((string) mt_rand(), true),
            'modus' => 0,
            'restrictarticles' => $randomArticles[0]['ordernumber'],
        ];
        // Try with valid voucher code, empty basket
        $this->connection->insert(
            's_emarketing_vouchers',
            $voucherData
        );

        $customer = $this->createDummyCustomer();
        $this->session['sUserId'] = $customer->getId();
        $this->module->sSYSTEM->sSESSION_ID = uniqid((string) mt_rand(), true);
        $this->session->offsetSet('sessionId', $this->module->sSYSTEM->sSESSION_ID);

        // Add one product to the basket with enough value to use discount
        $this->connection->insert(
            's_order_basket',
            [
                'price' => $voucherData['minimumcharge'] + 1,
                'quantity' => 1,
                'sessionID' => $this->session->get('sessionId'),
                'ordernumber' => $randomArticles[1]['ordernumber'],
                'articleID' => $randomArticles[1]['articleID'],
            ]
        );

        // Test again  code, fail
        $result = $this->module->sAddVoucher($voucherData['vouchercode']);
        static::assertIsArray($result);
        static::assertArrayHasKey('sErrorFlag', $result);
        static::assertArrayHasKey('sErrorMessages', $result);
        static::assertTrue($result['sErrorFlag']);
        static::assertContains(
            $this->snippetManager->getNamespace('frontend/basket/internalMessages')->get(
                'VoucherFailureProducts',
                'This voucher is only available in combination with certain products'
            ),
            $result['sErrorMessages']
        );

        $this->connection->insert(
            's_order_basket',
            [
                'price' => $voucherData['minimumcharge'] + 1,
                'quantity' => 1,
                'sessionID' => $this->session->get('sessionId'),
                'ordernumber' => $randomArticles[0]['ordernumber'],
                'articleID' => $randomArticles[0]['articleID'],
            ]
        );

        $previousAmount = $this->module->sGetAmount();
        // Test with one-time code, success
        static::assertTrue($this->module->sAddVoucher($voucherData['vouchercode']));
        static::assertLessThan($previousAmount, $this->module->sGetAmount());

        // Housekeeping
        $this->connection->delete(
            's_order_basket',
            ['sessionID' => $this->session->get('sessionId')]
        );
        $this->connection->delete(
            's_emarketing_vouchers',
            ['vouchercode' => $voucherData['vouchercode']]
        );
        $this->deleteDummyCustomer($customer);
    }

    public function testsAddVoucherWithCurrencyFactor(): void
    {
        $this->session->clear();

        // Prepare a voucher
        $voucherData = [
            'vouchercode' => 'testOne',
            'description' => 'testOne description',
            'numberofunits' => 1,
            'value' => 10,
            'minimumcharge' => 10,
            'ordercode' => uniqid((string) mt_rand(), true),
            'modus' => 0,
        ];
        $this->connection->insert(
            's_emarketing_vouchers',
            $voucherData
        );

        // Fetch a random product
        $randomProduct = $this->getRandomProduct();

        // Generate session id
        $this->module->sSYSTEM->sSESSION_ID = uniqid((string) mt_rand(), true);
        $this->session->offsetSet('sessionId', $this->module->sSYSTEM->sSESSION_ID);

        // Define different currency factors to test
        foreach ([0, .5, 1, 1.5, 2] as $currencyFactor) {
            // If the currency factor is set to 0, a fallback of 1 should be used
            $currencyFactorForCalculation = $currencyFactor ?: 1;

            // Prepare values to test against later
            $deltaBetweenVoucherAndArticlePrice = 1;
            $this->module->sSYSTEM->sCurrency['factor'] = $currencyFactor;
            $basketAmountWithoutVoucher = $voucherData['minimumcharge'] * $currencyFactorForCalculation + $deltaBetweenVoucherAndArticlePrice;

            // Add one product to the basket with enough value to use discount
            $this->connection->insert(
                's_order_basket',
                [
                    'price' => $basketAmountWithoutVoucher,
                    'quantity' => 1,
                    'sessionID' => $this->session->get('sessionId'),
                    'ordernumber' => $randomProduct['ordernumber'],
                    'articleID' => $randomProduct['articleID'],
                ]
            );

            static::assertEquals($basketAmountWithoutVoucher, $this->module->sGetAmount()['totalAmount']);
            static::assertTrue($this->module->sAddVoucher($voucherData['vouchercode']));
            static::assertEquals($deltaBetweenVoucherAndArticlePrice, $this->module->sGetAmount()['totalAmount']);

            // Housekeeping
            $this->connection->delete(
                's_order_basket',
                ['sessionID' => $this->session->get('sessionId')]
            );
        }

        // Housekeeping
        $this->connection->delete(
            's_emarketing_vouchers',
            ['vouchercode' => $voucherData['vouchercode']]
        );
    }

    public function testsAddVoucherWithSupplier(): void
    {
        $this->session->clear();

        $randomProductOne = $this->getRandomProduct();
        $randomProductTwo = $this->getRandomProduct(null, $randomProductOne['supplierID']);

        $voucherData = [
            'vouchercode' => 'testOne',
            'description' => 'testOne description',
            'numberofunits' => 1,
            'numorder' => 1,
            'value' => 10,
            'minimumcharge' => 10,
            'ordercode' => uniqid('ordercode', true),
            'modus' => 0,
            'bindtosupplier' => $randomProductOne['supplierID'],
        ];
        // Try with valid voucher code, empty basket
        $this->connection->insert('s_emarketing_vouchers', $voucherData);

        $customer = $this->createDummyCustomer();
        $this->session['sUserId'] = $customer->getId();
        $this->generateBasketSession();

        // Add first product to the basket with enough value to use discount, should fail
        $this->connection->insert(
            's_order_basket',
            [
                'price' => $voucherData['minimumcharge'] + 1,
                'quantity' => 1,
                'sessionID' => $this->session->get('sessionId'),
                'ordernumber' => $randomProductTwo['ordernumber'],
                'articleID' => $randomProductTwo['articleID'],
            ]
        );

        $supplierOne = $this->connection->fetchOne(
            'SELECT name FROM s_articles_supplier WHERE id = ?',
            [$randomProductOne['supplierID']]
        );
        $result = $this->module->sAddVoucher($voucherData['vouchercode']);
        static::assertIsArray($result);
        static::assertArrayHasKey('sErrorFlag', $result);
        static::assertArrayHasKey('sErrorMessages', $result);
        static::assertTrue($result['sErrorFlag']);
        static::assertContains(
            str_replace(
                '{sSupplier}',
                $supplierOne,
                $this->snippetManager->getNamespace('frontend/basket/internalMessages')->get(
                    'VoucherFailureSupplier',
                    'This voucher is only available for products from {sSupplier}'
                )
            ),
            $result['sErrorMessages']
        );

        $this->connection->insert(
            's_order_basket',
            [
                'price' => $voucherData['minimumcharge'] + 1,
                'quantity' => 1,
                'sessionID' => $this->session->get('sessionId'),
                'ordernumber' => $randomProductOne['ordernumber'],
                'articleID' => $randomProductOne['articleID'],
            ]
        );

        $previousAmount = $this->module->sGetAmount();
        // Test with one-time code, success
        static::assertTrue($this->module->sAddVoucher($voucherData['vouchercode']));
        static::assertLessThan($previousAmount, $this->module->sGetAmount());

        // Housekeeping
        $this->connection->delete(
            's_order_basket',
            ['sessionID' => $this->session->get('sessionId')]
        );
        $this->connection->delete(
            's_emarketing_vouchers',
            ['vouchercode' => $voucherData['vouchercode']]
        );
        $this->deleteDummyCustomer($customer);
    }

    public function testsGetBasketIds(): void
    {
        $randomProductOne = $this->getRandomProduct();
        $randomProducts = [
            $randomProductOne,
            $this->getRandomProduct($randomProductOne['id']),
        ];

        $this->module->sSYSTEM->sSESSION_ID = uniqid((string) mt_rand(), true);
        $this->session->offsetSet('sessionId', $this->module->sSYSTEM->sSESSION_ID);

        // Test with empty basket, empty
        static::assertNull($this->module->sGetBasketIds());

        // Add the first product to the basket, test we get the product id
        $this->connection->insert(
            's_order_basket',
            [
                'price' => 1,
                'quantity' => 1,
                'sessionID' => $this->session->get('sessionId'),
                'ordernumber' => $randomProducts[0]['ordernumber'],
                'articleID' => $randomProducts[0]['articleID'],
            ]
        );
        static::assertEquals(
            [$randomProducts[0]['articleID']],
            $this->module->sGetBasketIds()
        );

        // Add the first product to the basket again, test we get the same result
        $this->connection->insert(
            's_order_basket',
            [
                'price' => 1,
                'quantity' => 1,
                'sessionID' => $this->session->get('sessionId'),
                'ordernumber' => $randomProducts[0]['ordernumber'],
                'articleID' => $randomProducts[0]['articleID'],
            ]
        );
        static::assertEquals(
            [$randomProducts[0]['articleID']],
            $this->module->sGetBasketIds()
        );

        // Add the second product to the basket, test we get the two ids
        $this->connection->insert(
            's_order_basket',
            [
                'price' => 1,
                'quantity' => 1,
                'sessionID' => $this->session->get('sessionId'),
                'ordernumber' => $randomProducts[1]['ordernumber'],
                'articleID' => $randomProducts[1]['articleID'],
            ]
        );

        $basketIds = $this->module->sGetBasketIds();
        static::assertIsArray($basketIds);
        static::assertContains(
            $randomProducts[0]['articleID'],
            $basketIds
        );
        static::assertContains(
            $randomProducts[1]['articleID'],
            $basketIds
        );

        // Housekeeping
        $this->connection->delete(
            's_order_basket',
            ['sessionID' => $this->session->get('sessionId')]
        );
    }

    public function testsCheckMinimumCharge(): void
    {
        $oldMinimumOrder = $this->module->sSYSTEM->sUSERGROUPDATA['minimumorder'];
        $oldMinimumOrderSurcharge = $this->module->sSYSTEM->sUSERGROUPDATA['minimumordersurcharge'];

        // Test with minimum order surcharge, always returns false
        $this->module->sSYSTEM->sUSERGROUPDATA['minimumordersurcharge'] = 10;
        static::assertFalse($this->module->sCheckMinimumCharge());

        $this->module->sSYSTEM->sUSERGROUPDATA['minimumordersurcharge'] = 0;
        $this->module->sSYSTEM->sUSERGROUPDATA['minimumorder'] = 10;

        // Test with empty cart, expect 10
        static::assertEquals(10, $this->module->sCheckMinimumCharge());

        $this->module->sSYSTEM->sSESSION_ID = uniqid((string) mt_rand(), true);
        $this->session->offsetSet('sessionId', $this->module->sSYSTEM->sSESSION_ID);

        // Add one product to the basket with enough value to use discount
        $randomArticle = $this->getRandomProduct();
        $this->connection->insert(
            's_order_basket',
            [
                'price' => 2,
                'quantity' => 1,
                'sessionID' => $this->session->get('sessionId'),
                'ordernumber' => $randomArticle['ordernumber'],
                'articleID' => $randomArticle['articleID'],
            ]
        );

        // Test with non-empty cart, expect 10
        static::assertEquals(10, $this->module->sCheckMinimumCharge());

        // Pass the minimum value, expect false
        $this->connection->insert(
            's_order_basket',
            [
                'price' => 20,
                'quantity' => 1,
                'sessionID' => $this->session->get('sessionId'),
                'ordernumber' => $randomArticle['ordernumber'],
                'articleID' => $randomArticle['articleID'],
            ]
        );

        static::assertFalse($this->module->sCheckMinimumCharge());

        // Housekeeping
        $this->module->sSYSTEM->sUSERGROUPDATA['minimumorder'] = $oldMinimumOrder;
        $this->module->sSYSTEM->sUSERGROUPDATA['minimumordersurcharge'] = $oldMinimumOrderSurcharge;
        $this->connection->delete(
            's_order_basket',
            ['sessionID' => $this->session->get('sessionId')]
        );
    }

    public function testsInsertSurcharge(): void
    {
        $oldMinimumOrder = $this->module->sSYSTEM->sUSERGROUPDATA['minimumorder'];
        $oldMinimumOrderSurcharge = $this->module->sSYSTEM->sUSERGROUPDATA['minimumordersurcharge'];

        // Empty basket, expect false
        static::assertFalse(
            $this->invokeMethod(
                $this->module,
                'sInsertSurcharge'
            )
        );

        $this->module->sSYSTEM->sSESSION_ID = uniqid((string) mt_rand(), true);
        $this->session->offsetSet('sessionId', $this->module->sSYSTEM->sSESSION_ID);

        // Add one product to the basket with value lower that minimumordersurcharge
        $randomProduct = $this->getRandomProduct();
        $this->connection->insert(
            's_order_basket',
            [
                'price' => 2,
                'quantity' => 1,
                'sessionID' => $this->session->get('sessionId'),
                'ordernumber' => $randomProduct['ordernumber'],
                'articleID' => $randomProduct['articleID'],
            ]
        );

        $this->module->sSYSTEM->sUSERGROUPDATA['minimumordersurcharge'] = 5;
        $this->module->sSYSTEM->sUSERGROUPDATA['minimumorder'] = 10;

        // Check that we have no surcharge
        static::assertEmpty(
            $this->connection->fetchAssociative(
                'SELECT * FROM s_order_basket WHERE sessionID = ? AND modus=4',
                [$this->module->sSYSTEM->sSESSION_ID]
            )
        );

        // Add surcharge, expect success (null)
        static::assertNull(
            $this->invokeMethod(
                $this->module,
                'sInsertSurcharge'
            )
        );

        // Fetch the surcharge row, should have price 5
        $surchargeRow = $this->connection->fetchAssociative(
            'SELECT * FROM s_order_basket WHERE sessionID = ? AND modus=4',
            [$this->module->sSYSTEM->sSESSION_ID]
        );
        static::assertIsArray($surchargeRow);
        static::assertEquals(5, $surchargeRow['price']);

        // Housekeeping
        $this->module->sSYSTEM->sUSERGROUPDATA['minimumorder'] = $oldMinimumOrder;
        $this->module->sSYSTEM->sUSERGROUPDATA['minimumordersurcharge'] = $oldMinimumOrderSurcharge;
        $this->connection->delete(
            's_order_basket',
            ['sessionID' => $this->session->get('sessionId')]
        );
    }

    public function testsInsertSurchargePercent(): void
    {
        $this->session->clear();

        // No user and no payment id, expect false
        static::assertFalse(
            $this->invokeMethod(
                $this->module,
                'sInsertSurchargePercent'
            )
        );

        $customer = $this->createDummyCustomer();
        $paymentData = [
            'name' => 'testPaymentMean',
            'description' => 'testPaymentMean',
            'debit_percent' => 5,
        ];
        $this->connection->insert('s_core_paymentmeans', $paymentData);
        $paymentMeanId = $this->connection->lastInsertId();
        $this->connection->update(
            's_user',
            ['paymentID' => $paymentMeanId],
            ['id' => $customer->getId()]
        );
        $this->session['sUserId'] = $customer->getId();
        $this->module->sSYSTEM->sSESSION_ID = uniqid((string) mt_rand(), true);
        $this->session->offsetSet('sessionId', $this->module->sSYSTEM->sSESSION_ID);

        // Empty basket, expect false
        static::assertFalse(
            $this->invokeMethod(
                $this->module,
                'sInsertSurchargePercent'
            )
        );

        // Add one product to the basket with low amount
        $randomProduct = $this->getRandomProduct();
        $this->connection->insert(
            's_order_basket',
            [
                'price' => 2,
                'quantity' => 1,
                'sessionID' => $this->session->get('sessionId'),
                'ordernumber' => $randomProduct['ordernumber'],
                'articleID' => $randomProduct['articleID'],
            ]
        );

        // Check that we have no surcharge
        static::assertEmpty(
            $this->connection->fetchAssociative(
                'SELECT * FROM s_order_basket WHERE sessionID = ? AND modus=4',
                [$this->module->sSYSTEM->sSESSION_ID]
            )
        );

        // Add surcharge, expect success (null)
        static::assertNull(
            $this->invokeMethod(
                $this->module,
                'sInsertSurchargePercent'
            )
        );

        // Fetch the surcharge row, should have price 5
        $surchargeRow = $this->connection->fetchAssociative(
            'SELECT * FROM s_order_basket WHERE sessionID = ? AND modus = 4',
            [$this->module->sSYSTEM->sSESSION_ID]
        );
        static::assertIsArray($surchargeRow);
        static::assertEquals(2 / 100 * 5, $surchargeRow['price']);

        // Housekeeping
        $this->deleteDummyCustomer($customer);
        $this->connection->delete(
            's_order_basket',
            ['sessionID' => $this->session->get('sessionId')]
        );
        $this->connection->delete(
            's_core_paymentmeans',
            ['name' => 'testPaymentMean']
        );
    }

    public function testsGetBasket(): void
    {
        // Test with empty basket
        static::assertEquals([], $this->module->sGetBasket());

        $this->module->sSYSTEM->sSESSION_ID = uniqid((string) mt_rand(), true);
        $this->session->offsetSet('sessionId', $this->module->sSYSTEM->sSESSION_ID);

        // Add one product to the basket with low amount
        $randomProduct = $this->getRandomProduct();
        $this->connection->insert(
            's_order_basket',
            [
                'price' => 2,
                'quantity' => 1,
                'sessionID' => $this->session->get('sessionId'),
                'ordernumber' => $randomProduct['ordernumber'],
                'articleID' => $randomProduct['articleID'],
            ]
        );

        $contentKeys = [
            'id',
            'sessionID',
            'userID',
            'articlename',
            'articleID',
            'ordernumber',
            'shippingfree',
            'quantity',
            'price',
            'netprice',
            'tax_rate',
            'datum',
            'modus',
            'esdarticle',
            'partnerID',
            'lastviewport',
            'useragent',
            'config',
            'currencyFactor',
            'packunit',
            'minpurchase',
            'taxID',
            'instock',
            'suppliernumber',
            'maxpurchase',
            'purchasesteps',
            'purchaseunit',
            'laststock',
            'shippingtime',
            'releasedate',
            'sReleaseDate',
            'stockmin',
            'ob_attr1',
            'ob_attr2',
            'ob_attr3',
            'ob_attr4',
            'ob_attr5',
            'ob_attr6',
            'shippinginfo',
            'esd',
            'additional_details',
            'amount',
            'amountnet',
            'priceNumeric',
            'image',
            'linkDetails',
            'linkDelete',
            'linkNote',
            'tax',
        ];

        $result = $this->module->sGetBasket();
        static::assertArrayHasKey(CartKey::POSITIONS, $result);
        static::assertCount(1, $result[CartKey::POSITIONS]);
        foreach ($contentKeys as $key) {
            static::assertArrayHasKey($key, $result[CartKey::POSITIONS][0]);
        }

        static::assertArrayHasKey(CartKey::AMOUNT, $result);
        static::assertSame('14,95', $result[CartKey::AMOUNT]);
        static::assertArrayHasKey(CartKey::AMOUNT_NET, $result);
        static::assertSame('12,56', $result[CartKey::AMOUNT_NET]);
        static::assertArrayHasKey(CartKey::AMOUNT_NUMERIC, $result);
        static::assertSame(14.95, $result[CartKey::AMOUNT_NUMERIC]);
        static::assertArrayHasKey(CartKey::AMOUNT_NET_NUMERIC, $result);
        static::assertSame(12.56, ($result[CartKey::AMOUNT_NET_NUMERIC]));
        static::assertArrayHasKey(CartKey::AMOUNT_WITH_TAX, $result);
        static::assertSame('0', ($result[CartKey::AMOUNT_WITH_TAX]));
        static::assertArrayHasKey(CartKey::AMOUNT_WITH_TAX_NUMERIC, $result);
        static::assertSame(0.0, ($result[CartKey::AMOUNT_WITH_TAX_NUMERIC]));
        static::assertArrayHasKey(CartKey::QUANTITY, $result);
        static::assertSame(1, $result[CartKey::QUANTITY]);
    }

    public function testsGetBasketDataHasNumericCartItemAmounts(): void
    {
        $resourceHelper = new Helper($this->getContainer());
        try {
            $product = $resourceHelper->createProduct([
                'name' => 'Testartikel',
                'description' => 'Test description',
                'active' => true,
                'mainDetail' => [
                    'number' => 'swTEST' . uniqid((string) mt_rand(), true),
                    'inStock' => 15,
                    'lastStock' => true,
                    'unitId' => 1,
                    'prices' => [
                        [
                            'customerGroupKey' => 'EK',
                            'from' => 1,
                            'to' => '-',
                            'price' => 29.97,
                        ],
                    ],
                ],
                'taxId' => 4,
                'supplierId' => 2,
                'categories' => [
                    [
                        'id' => 10,
                    ],
                ],
            ]);
            $customerGroup = $resourceHelper->createCustomerGroup();
            $this->session['sUserId'] = $this->createDummyCustomer()->getId();
            $this->module->sSYSTEM->sSESSION_ID = uniqid((string) mt_rand(), true);
            $this->session->offsetSet('sessionId', $this->module->sSYSTEM->sSESSION_ID);
            $this->module->sSYSTEM->sUSERGROUPDATA['id'] = $customerGroup->getId();

            // Add the product to the basket
            static::assertInstanceOf(Detail::class, $product->getMainDetail());
            static::assertIsString($product->getMainDetail()->getNumber());
            $this->module->sAddArticle($product->getMainDetail()->getNumber(), 2);
            $this->module->sRefreshBasket();
            $basketData = $this->module->sGetBasketData();

            // Assert that a valid basket was returned
            static::assertArrayHasKey(CartKey::POSITIONS, $basketData);
            // Assert that there is a numeric basket amount
            static::assertArrayHasKey('amountNumeric', $basketData[CartKey::POSITIONS][0], 'amountNumeric for cart item should exist');
            static::assertArrayHasKey('amountnetNumeric', $basketData[CartKey::POSITIONS][0], 'amountnetNumeric for cart item should exist');
            static::assertGreaterThan(0, $basketData[CartKey::POSITIONS][0]['amountNumeric']);
            static::assertGreaterThan(0, $basketData[CartKey::POSITIONS][0]['amountnetNumeric']);
            static::assertEquals(29.97 * 2, $basketData[CartKey::POSITIONS][0]['amountNumeric'], 'amountNumeric for cart item should respect cart item quantity');
            static::assertEqualsWithDelta(29.97 * 2, $basketData[CartKey::POSITIONS][0]['amountNumeric'], 0.001, 'amountNumeric for cart item should respect cart item quantity');
        } finally {
            $resourceHelper->cleanUp();
        }
    }

    /**
     * Assert that rounding basket totals works correctly for a basket that has a decimal-binary conversion inaccuracies
     * which results in a total that is very slightly below zero.
     *
     * The example used here is:
     *
     * product                   29.97
     * Shipping discount         -2.80
     * Customer group discount  -27.17 = 90.65 % of the item total
     * ------------------------------
     * Total (double arithmetic) -0.0000000000000035527136788005
     * Total (real world)         0.00
     */
    public function testsGetBasketDataNegativeCloseToZeroTotal(): void
    {
        $resourceHelper = new Helper($this->getContainer());
        try {
            // Setup product for the first basket position - a product that costs EUR 29.97
            $product = $resourceHelper->createProduct([
                'name' => 'Testartikel',
                'description' => 'Test description',
                'active' => true,
                'mainDetail' => [
                    'number' => 'swTEST' . uniqid((string) mt_rand(), true),
                    'inStock' => 15,
                    'lastStock' => true,
                    'unitId' => 1,
                    'prices' => [
                        [
                            'customerGroupKey' => 'EK',
                            'from' => 1,
                            'to' => '-',
                            'price' => 29.97,
                        ],
                    ],
                ],
                'taxId' => 4,
                'supplierId' => 2,
                'categories' => [
                    [
                        'id' => 10,
                    ],
                ],
            ]);
            // Setup discount for the second basket position - a shipping discount of EUR -2.8
            $dispatchDiscountId = $this->connection->fetchOne(
                'SELECT * FROM s_premium_dispatch WHERE type = 3'
            );
            $this->connection->update(
                's_premium_shippingcosts',
                ['value' => 2.8],
                ['dispatchID' => $dispatchDiscountId]
            );
            // Setup discount for the third basket position - a basket discount covering the remainder of the basket (-27.17)
            $customerGroup = $resourceHelper->createCustomerGroup();
            $this->connection->insert(
                's_core_customergroups_discounts',
                [
                    'groupID' => $customerGroup->getId(),
                    // discount by the full remaining value of the basket - EUR 27.17 / EUR 29.97 = 90.65 %
                    'basketdiscount' => 90.65,
                    'basketdiscountstart' => 10,
                ]
            );
            $customerGroupDiscountId = $this->connection->lastInsertId('s_core_customergroups_discounts');
            // Setup the user and their session
            $customer = $this->createDummyCustomer();
            $this->connection->update(
                's_user',
                ['customergroup' => $customerGroup->getKey()],
                ['id' => $customer->getId()]
            );
            $this->session['sUserId'] = $customer->getId();
            $this->module->sSYSTEM->sSESSION_ID = uniqid((string) mt_rand(), true);
            $this->session->offsetSet('sessionId', $this->module->sSYSTEM->sSESSION_ID);
            $this->module->sSYSTEM->sUSERGROUPDATA['id'] = $customerGroup->getId();

            // Actually add the product to the basket
            static::assertInstanceOf(Detail::class, $product->getMainDetail());
            static::assertIsString($product->getMainDetail()->getNumber());
            $this->module->sAddArticle($product->getMainDetail()->getNumber());
            // Run sBasket::sRefreshBasket() in order to add the discounts to the basket
            $this->module->sRefreshBasket();
            // Run sGetBasketData() to show the rounding error aborting the computation
            $basketData = $this->module->sGetBasketData();
            // Run sGetAmount() to show that this function is affected by the issue as well
            $this->module->sGetAmount();

            // Assert that a valid basket was returned
            static::assertArrayHasKey(CartKey::AMOUNT_NUMERIC, $basketData);
            // Assert that the total is approximately 0.00
            static::assertEquals(0, $basketData[CartKey::AMOUNT_NUMERIC], 'total is approxmately 0.00');
            static::assertEqualsWithDelta(0, $basketData[CartKey::AMOUNT_NUMERIC], 0.0001, 'total is approxmately 0.00');
        } finally {
            // Delete test resources
            if (isset($customerGroupDiscountId)) {
                $this->connection->delete('s_core_customergroups_discounts', ['id' => $customerGroupDiscountId]);
            }
            $resourceHelper->cleanUp();
        }
    }

    public function testsGetBasketWithInvalidProduct(): void
    {
        static::assertEquals([], $this->module->sGetBasket());

        $this->module->sSYSTEM->sSESSION_ID = uniqid((string) mt_rand(), true);
        $this->session->offsetSet('sessionId', $this->module->sSYSTEM->sSESSION_ID);

        // Setup product for the first basket position - a product that costs EUR 29.97
        $product = (new Helper($this->getContainer()))->createProduct([
            'name' => 'Testartikel',
            'description' => 'Test description',
            'active' => true,
            'mainDetail' => [
                'number' => 'swTEST' . Random::getAlphanumericString(12),
                'inStock' => 15,
                'lastStock' => true,
                'unitId' => 1,
                'prices' => [
                    [
                        'customerGroupKey' => 'EK',
                        'from' => 1,
                        'to' => '-',
                        'price' => 29.97,
                    ],
                ],
            ],
            'taxId' => 4,
            'supplierId' => 2,
            'categories' => [
                [
                    'id' => 10,
                ],
            ],
        ]);

        static::assertInstanceOf(Detail::class, $product->getMainDetail());
        $this->connection->insert(
            's_order_basket',
            [
                'price' => 2,
                'quantity' => 1,
                'sessionID' => $this->session->get('sessionId'),
                'ordernumber' => $product->getMainDetail()->getNumber(),
                'articleID' => $product->getId(),
            ]
        );

        $cart = $this->module->sGetBasket();
        static::assertArrayHasKey(CartKey::POSITIONS, $cart);
        static::assertCount(1, $cart[CartKey::POSITIONS]);

        $this->connection->delete('s_articles_details', ['articleID' => $product->getId()]);
        $this->connection->delete('s_articles', ['id' => $product->getId()]);

        static::assertEquals([], $this->module->sGetBasket());
    }

    public function testsAddNote(): void
    {
        $front = $this->getContainer()->get('front');
        $_COOKIE['sUniqueID'] = Random::getAlphanumericString(32);

        // Add one product to the basket with low amount
        $randomProduct = $this->getRandomProduct();

        static::assertEquals(0, $this->connection->fetchOne(
            'SELECT COUNT(DISTINCT id) FROM s_order_notes WHERE sUniqueID = ? AND ordernumber = ?',
            [$_COOKIE['sUniqueID'], $randomProduct['ordernumber']]
        ));

        static::assertTrue($this->module->sAddNote(
            $randomProduct['articleID'],
            $randomProduct['name'],
            $randomProduct['ordernumber']
        ));

        static::assertEquals(1, $this->connection->fetchOne(
            'SELECT COUNT(DISTINCT id) FROM s_order_notes WHERE sUniqueID = ? AND ordernumber = ?',
            [$_COOKIE['sUniqueID'], $randomProduct['ordernumber']]
        ));

        static::assertTrue($this->module->sAddNote(
            $randomProduct['articleID'],
            $randomProduct['name'],
            $randomProduct['ordernumber']
        ));
    }

    public function testsGetNotes(): void
    {
        $this->session->clear();
        $_COOKIE['sUniqueID'] = Random::getAlphanumericString(32);

        $randomProduct = $this->getRandomProduct();
        // Test with no id in cookie
        static::assertEquals([], $this->module->sGetNotes());
        static::assertTrue($this->module->sAddNote(
            $randomProduct['articleID'],
            $randomProduct['name'],
            $randomProduct['ordernumber']
        ));

        $result = $this->module->sGetNotes();
        static::assertEquals($randomProduct['articleID'], $result[0]['articleID']);
    }

    public function testsCountNotes(): void
    {
        $_COOKIE['sUniqueID'] = Random::getAlphanumericString(32);
        $randomProductOne = $this->getRandomProduct();
        static::assertTrue($this->module->sAddNote(
            $randomProductOne['articleID'],
            $randomProductOne['name'],
            $randomProductOne['ordernumber']
        ));
        static::assertEquals(1, $this->module->sCountNotes());

        // Add another product to the basket
        $randomProductTwo = $this->getRandomProduct($randomProductOne['id']);

        static::assertTrue($this->module->sAddNote(
            $randomProductTwo['articleID'],
            $randomProductTwo['name'],
            $randomProductTwo['ordernumber']
        ));

        static::assertEquals(2, $this->module->sCountNotes());
    }

    public function testsDeleteNote(): void
    {
        $_COOKIE['sUniqueID'] = Random::getAlphanumericString(32);
        $randomProductOne = $this->getRandomProduct();
        $randomProducts = [
            $randomProductOne,
            $this->getRandomProduct($randomProductOne['id']),
        ];

        static::assertTrue($this->module->sAddNote(
            $randomProducts[0]['articleID'],
            $randomProducts[0]['name'],
            $randomProducts[0]['ordernumber']
        ));
        static::assertTrue($this->module->sAddNote(
            $randomProducts[1]['articleID'],
            $randomProducts[1]['name'],
            $randomProducts[1]['ordernumber']
        ));

        // Null argument, return null
        static::assertFalse($this->module->sDeleteNote(0));

        // Get random product that's not in the basket
        $randomNotPresentProductId = $this->connection->fetchOne(
            'SELECT detail.id FROM s_articles_details detail
            INNER JOIN s_articles product
              ON product.id = detail.articleID
            WHERE detail.active = 1
            AND detail.id NOT IN (?)
            LIMIT 1',
            [array_column($randomProducts, 'id')]
        );

        // Check that we currently have 2 articles
        static::assertEquals(2, $this->module->sCountNotes());

        // Get true even if product is not in the wishlist
        static::assertTrue($this->module->sDeleteNote($randomNotPresentProductId));

        // Check that we still have 2 articles
        static::assertEquals(2, $this->module->sCountNotes());

        $noteIds = $this->connection->fetchFirstColumn(
            'SELECT id FROM s_order_notes detail
            WHERE sUniqueID = ?',
            [$_COOKIE['sUniqueID']]
        );

        // Get true even if product is not in the wishlist
        static::assertTrue($this->module->sDeleteNote($noteIds[0]));

        // Check that we now have 1 product
        static::assertEquals(1, $this->module->sCountNotes());

        // Get true even if product is not in the wishlist
        static::assertTrue($this->module->sDeleteNote($noteIds[1]));

        // Check that we now have an empty wishlist
        static::assertEquals(0, $this->module->sCountNotes());
    }

    public function testsUpdateArticle(): void
    {
        // Null args, false result
        static::assertFalse($this->module->sUpdateArticle(0, 0));

        $this->generateBasketSession();

        // Get random product
        $randomProduct = $this->getRandomProduct();
        $this->connection->insert(
            's_order_basket',
            [
                'price' => 0.01,
                'quantity' => 1,
                'sessionID' => $this->session->get('sessionId'),
                'ordernumber' => $randomProduct['ordernumber'],
                'articleID' => $randomProduct['articleID'],
            ]
        );
        $basketId = (int) $this->connection->lastInsertId();

        // Store previous amount
        $previousAmount = $this->module->sGetAmount();
        static::assertEquals(['totalAmount' => 0.01], $previousAmount);

        // Update the product, prices are recalculated
        static::assertNull($this->module->sUpdateArticle($basketId, 1));
        $oneAmount = $this->module->sGetAmount();
        static::assertGreaterThan($previousAmount['totalAmount'], $oneAmount['totalAmount']);

        // Update from 1 to 2, we should get a more expensive cart
        static::assertNull($this->module->sUpdateArticle($basketId, 2));
        $twoAmount = $this->module->sGetAmount();
        static::assertGreaterThanOrEqual($oneAmount['totalAmount'], $twoAmount['totalAmount']);
        static::assertLessThanOrEqual(2 * $oneAmount['totalAmount'], $twoAmount['totalAmount']);

        // Housekeeping
        $this->connection->delete(
            's_order_basket',
            ['sessionID' => $this->session->get('sessionId')]
        );
    }

    public function testsCheckForESD(): void
    {
        // No session, expect false
        static::assertFalse($this->module->sCheckForESD());

        $this->module->sSYSTEM->sSESSION_ID = uniqid((string) mt_rand(), true);
        $this->session->offsetSet('sessionId', $this->module->sSYSTEM->sSESSION_ID);

        // Get random non-esd product and add it to the basket
        $randomNoESDProduct = $this->connection->fetchAssociative(
            'SELECT detail.ordernumber
             FROM s_articles_details detail
             INNER JOIN s_articles product
                ON product.id = detail.articleID
             LEFT JOIN s_articles_esd esd
                ON esd.articledetailsID = detail.id
             WHERE detail.active = 1
             AND esd.id IS NULL
             LIMIT 1'
        );
        static::assertIsArray($randomNoESDProduct);

        static::assertGreaterThan(0, $this->module->sAddArticle($randomNoESDProduct['ordernumber']));

        static::assertFalse($this->module->sCheckForESD());

        // Get random esd product
        $randomESDProduct = $this->connection->fetchAssociative(
            'SELECT detail.* FROM s_articles_details detail
             INNER JOIN s_articles product
                ON product.id = detail.articleID
             INNER JOIN s_articles_esd esd
                ON esd.articledetailsID = detail.id
             WHERE esd.id IS NOT NULL
             LIMIT 1'
        );
        static::assertIsArray($randomESDProduct);
        $this->connection->update(
            's_articles_details',
            ['active' => 1],
            ['id' => $randomESDProduct['id']]
        );
        $this->connection->update(
            's_articles',
            ['active' => 1],
            ['id' => $randomESDProduct['articleID']]
        );
        $this->module->sAddArticle($randomESDProduct['ordernumber']);

        static::assertTrue($this->module->sCheckForESD());

        // Housekeeping
        $this->connection->delete(
            's_order_basket',
            ['sessionID' => $this->session->get('sessionId')]
        );
        $this->connection->update(
            's_articles_details',
            ['active' => 0],
            ['id' => $randomESDProduct['id']]
        );
        $this->connection->update(
            's_articles',
            ['active' => 0],
            ['id' => $randomESDProduct['articleID']]
        );
    }

    public function testsDeleteBasket(): void
    {
        // No session, expect false
        static::assertFalse($this->module->sDeleteBasket());

        $this->module->sSYSTEM->sSESSION_ID = uniqid((string) mt_rand(), true);
        $this->session->offsetSet('sessionId', $this->module->sSYSTEM->sSESSION_ID);

        static::assertNull($this->module->sDeleteBasket());

        // Get random product and add it to the basket
        $randomProduct = $this->connection->fetchAssociative(
            'SELECT detail.* FROM s_articles_details detail
             INNER JOIN s_articles product
                ON product.id = detail.articleID
             LEFT JOIN s_articles_avoid_customergroups avoid
                ON avoid.articleID = product.id
             WHERE detail.active = 1
             AND avoid.articleID IS NULL
             AND product.id NOT IN (
                SELECT articleID
                FROM s_articles_avoid_customergroups
                WHERE customergroupID = 1
             )
             AND (detail.lastStock = 0 OR detail.instock > 0)
             LIMIT 1'
        );
        static::assertIsArray($randomProduct);

        $this->module->sAddArticle($randomProduct['ordernumber']);

        static::assertNotEquals(0, $this->module->sCountBasket());

        $this->module->sDeleteBasket();

        static::assertEquals(0, $this->module->sCountBasket());
    }

    public function testsDeleteArticle(): void
    {
        // No id, expect null
        static::assertNull($this->module->sDeleteArticle(0));

        // Random id, expect null
        static::assertNull($this->module->sDeleteArticle(9999999));

        $this->module->sSYSTEM->sSESSION_ID = uniqid((string) mt_rand(), true);
        $this->session->offsetSet('sessionId', $this->module->sSYSTEM->sSESSION_ID);

        // Get random product and add it to the basket
        $randomProduct = $this->connection->fetchAssociative(
            'SELECT detail.* FROM s_articles_details detail
             LEFT JOIN s_articles product
                ON product.id = detail.articleID
             LEFT JOIN s_articles_avoid_customergroups avoid
                ON avoid.articleID = product.id
             WHERE detail.active = 1
             AND avoid.articleID IS NULL
             AND product.id NOT IN (
                SELECT articleID
                FROM s_articles_avoid_customergroups
                WHERE customergroupID = 1
             )
             AND (detail.lastStock = 0 OR detail.instock > 0)
             LIMIT 1'
        );
        static::assertIsArray($randomProduct);
        $idOne = $this->module->sAddArticle($randomProduct['ordernumber']);
        static::assertIsInt($idOne);
        static::assertEquals(1, $this->module->sCountBasket());

        $this->module->sDeleteArticle($idOne);
        static::assertEquals(0, $this->module->sCountBasket());
    }

    public function testsAddArticle(): void
    {
        // No id, expect false
        static::assertFalse($this->module->sAddArticle(''));

        $this->module->sSYSTEM->sSESSION_ID = uniqid((string) mt_rand(), true);
        $this->session->offsetSet('sessionId', $this->module->sSYSTEM->sSESSION_ID);

        // Get random product with stock control and add it to the basket
        $randomProductOne = $this->connection->fetchAssociative(
            'SELECT detail.* FROM s_articles_details detail
             INNER JOIN s_articles product
                ON product.id = detail.articleID
             LEFT JOIN s_articles_avoid_customergroups avoid
                ON avoid.articleID = product.id
             WHERE detail.active = 1
             AND detail.lastStock = 1
             AND instock > 3
             AND avoid.articleID IS NULL
             AND product.id NOT IN (
                SELECT articleID
                FROM s_articles_avoid_customergroups
                WHERE customergroupID = 1
             )
             LIMIT 1'
        );
        static::assertIsArray($randomProductOne);

        // Adding product without quantity adds one
        $this->module->sAddArticle($randomProductOne['ordernumber']);
        $basket = $this->module->sGetBasket();
        static::assertArrayHasKey(CartKey::QUANTITY, $basket);
        static::assertEquals(1, $basket[CartKey::QUANTITY]);
        static::assertArrayHasKey(CartKey::POSITIONS, $basket);
        static::assertEquals(1, $basket[CartKey::POSITIONS][0]['quantity']);

        // Adding product with quantity adds correctly, finds stacks
        $this->module->sAddArticle($randomProductOne['ordernumber'], 2);
        $basket = $this->module->sGetBasket();
        static::assertArrayHasKey(CartKey::QUANTITY, $basket);
        static::assertEquals(1, $basket[CartKey::QUANTITY]);
        static::assertEquals(1, $basket[CartKey::QUANTITY]);
        static::assertArrayHasKey(CartKey::POSITIONS, $basket);
        static::assertEquals(3, $basket[CartKey::POSITIONS][0]['quantity']);

        // Start over
        $this->module->sDeleteBasket();

        // Adding product with quantity over stock, check that we have the available stock
        $this->module->sAddArticle($randomProductOne['ordernumber'], $randomProductOne['instock'] + 200);
        $basket = $this->module->sGetBasket();
        static::assertArrayHasKey(CartKey::QUANTITY, $basket);
        static::assertEquals(1, $basket[CartKey::QUANTITY]);
        static::assertArrayHasKey(CartKey::POSITIONS, $basket);
        static::assertEquals(min($randomProductOne['instock'], 100), $basket[CartKey::POSITIONS][0]['quantity']);

        // Start over
        $this->module->sDeleteBasket();

        // Get random product and add it to the basket
        $randomProductTwo = $this->connection->fetchAssociative(
            'SELECT detail.* FROM s_articles_details detail
             INNER JOIN s_articles product
                ON product.id = detail.articleID
             WHERE detail.active = 1
             AND detail.laststock = 0
             AND detail.instock > 20
             AND detail.instock < 70
             AND product.id NOT IN (
                SELECT articleID
                FROM s_articles_avoid_customergroups
                WHERE customergroupID = 1
             )
             LIMIT 1'
        );
        static::assertIsArray($randomProductTwo);

        // Adding product with quantity over stock, check that we have the desired quantity
        $this->module->sAddArticle($randomProductTwo['ordernumber'], $randomProductTwo['instock'] + 20);
        $basket = $this->module->sGetBasket();
        static::assertArrayHasKey(CartKey::QUANTITY, $basket);
        static::assertEquals(1, $basket[CartKey::QUANTITY]);
        static::assertArrayHasKey(CartKey::POSITIONS, $basket);
        static::assertEquals(min($randomProductTwo['instock'] + 20, 100), $basket[CartKey::POSITIONS][0]['quantity']);

        // Housekeeping
        $this->connection->delete(
            's_order_basket',
            ['sessionID' => $this->session->get('sessionId')]
        );
    }

    public function testsPriceCalculationTaxfreeWithPriceGroupDiscount(): void
    {
        $resourceHelper = new Helper($this->getContainer());

        // Create pricegroup
        $priceGroup = $resourceHelper->createPriceGroup([
            [
                'key' => 'EK',
                'quantity' => 1,
                'discount' => 15.0,
            ],
        ]);

        // Create test product
        $product = $resourceHelper->createProduct([
            'name' => 'Testartikel',
            'description' => 'Test description',
            'active' => true,
            'mainDetail' => [
                'number' => 'swTEST' . uniqid((string) mt_rand(), true),
                'inStock' => 15,
                'unitId' => 1,
                'prices' => [
                    [
                        'customerGroupKey' => 'EK',
                        'from' => 1,
                        'to' => '-',
                        'price' => 38.90,
                    ],
                ],
            ],
            'taxId' => 4,
            'supplierId' => 2,
            'categories' => [10],
            'priceGroupActive' => true,
            'priceGroupId' => $priceGroup->getId(),
        ]);

        // Set customergroup to taxfree in session
        $customerGroupData = $this->connection->fetchAssociative(
            'SELECT * FROM s_core_customergroups WHERE groupkey = :key',
            ['key' => 'EK']
        );
        static::assertIsArray($customerGroupData);
        $customerGroupData['tax'] = 0;
        $this->module->sSYSTEM->sUSERGROUPDATA = $customerGroupData;
        $this->session->set('sUserGroupData', $customerGroupData);

        // Setup session
        $this->module->sSYSTEM->sSESSION_ID = uniqid((string) mt_rand(), true);
        $this->session->offsetSet('sessionId', $this->module->sSYSTEM->sSESSION_ID);

        static::assertInstanceOf(Detail::class, $product->getMainDetail());
        static::assertIsString($product->getMainDetail()->getNumber());
        $basketItemId = $this->module->sAddArticle($product->getMainDetail()->getNumber());

        // Check that the product has been added to the basket
        static::assertNotFalse($basketItemId);

        // Check that the final price equals the net price for the basket item
        $basketItem = $this->connection->fetchAssociative(
            'SELECT * FROM s_order_basket WHERE id = :id',
            ['id' => $basketItemId]
        );
        static::assertIsArray($basketItem);
        static::assertEquals($basketItem['price'], $basketItem['netprice']);

        // Check that the final price equals the net price for the whole basket
        $basketData = $this->module->sGetBasketData();
        static::assertArrayHasKey(CartKey::AMOUNT_NUMERIC, $basketData);
        static::assertArrayHasKey(CartKey::AMOUNT_NET_NUMERIC, $basketData);
        static::assertEquals($basketData[CartKey::AMOUNT_NUMERIC], $basketData[CartKey::AMOUNT_NET_NUMERIC]);

        // Delete test resources
        $resourceHelper->cleanUp();
    }

    public function testMinPurchaseMultipleTimesAdded(): void
    {
        $this->module->sSYSTEM->sSESSION_ID = uniqid((string) mt_rand(), true);
        $this->session->offsetSet('sessionId', $this->module->sSYSTEM->sSESSION_ID);

        $this->connection->executeStatement('UPDATE s_articles_details SET minpurchase = 2 WHERE ordernumber = "SW10239"');

        $this->module->sAddArticle('SW10239');
        $this->module->sAddArticle('SW10239');

        $this->connection->executeStatement('UPDATE s_articles_details SET minpurchase = 0 WHERE ordernumber = "SW10239"');

        $cart = $this->module->sGetBasketData();
        static::assertArrayHasKey(CartKey::POSITIONS, $cart);
        static::assertSame(4, (int) $cart[CartKey::POSITIONS][0]['quantity']);
    }

    private function generateBasketSession(): string
    {
        // Create session id
        $sessionId = Random::getAlphanumericString(32);
        $this->module->sSYSTEM->sSESSION_ID = $sessionId;
        $this->session->offsetSet('sessionId', $sessionId);

        return $sessionId;
    }

    /**
     * Create dummy customer entity
     */
    private function createDummyCustomer(): Customer
    {
        $date = new DateTime();
        $date->modify('-8 days');
        $lastLogin = $date->format(DateTime::ATOM);

        $date = DateTime::createFromFormat('Y-m-d', '1986-12-20');
        static::assertInstanceOf(DateTime::class, $date);
        $birthday = $date->format(DateTime::ATOM);

        $testData = [
            'password' => 'fooobar',
            'email' => uniqid((string) mt_rand(), true) . 'test@foobar.com',

            'lastlogin' => $lastLogin,

            'salutation' => 'mr',
            'firstname' => 'Max',
            'lastname' => 'Mustermann',
            'birthday' => $birthday,

            'billing' => [
                'salutation' => 'mr',
                'firstname' => 'Max',
                'lastname' => 'Mustermann',
                'street' => 'Musterstr. 123',
                'city' => 'Musterhausen',
                'attribute' => [
                    'text1' => 'Freitext1',
                    'text2' => 'Freitext2',
                ],
                'zipcode' => '12345',
                'country' => '2',
            ],

            'shipping' => [
                'salutation' => 'mr',
                'company' => 'Widgets Inc.',
                'firstname' => 'Max',
                'lastname' => 'Mustermann',
                'street' => 'Merkel Strasse, 10',
                'city' => 'Musterhausen',
                'zipcode' => '12345',
                'country' => '3',
                'attribute' => [
                    'text1' => 'Freitext1',
                    'text2' => 'Freitext2',
                ],
            ],

            'debit' => [
                'account' => 'Fake Account',
                'bankCode' => '55555555',
                'bankName' => 'Fake Bank',
                'accountHolder' => 'Max Mustermann',
            ],
        ];

        $customerResource = new CustomerResource();
        $customerResource->setManager($this->getContainer()->get(ModelManager::class));

        return $customerResource->create($testData);
    }

    /**
     * Deletes all dummy customer entity
     */
    private function deleteDummyCustomer(Customer $customer): void
    {
        $this->connection->delete('s_user_addresses', ['user_id' => $customer->getId()]);
        $this->connection->delete('s_core_payment_data', ['user_id' => $customer->getId()]);
        $this->connection->delete('s_user_attributes', ['userID' => $customer->getId()]);
        $this->connection->delete('s_user', ['id' => $customer->getId()]);
    }

    /**
     * Call protected/private method of a class.
     *
     * @param object                       $object     Instantiated object that we will run method on
     * @param string                       $methodName Method name to call
     * @param array<array|string|int|null> $parameters array of parameters to pass into method
     *
     * @return mixed method return
     */
    private function invokeMethod(object $object, string $methodName, array $parameters = [])
    {
        $method = (new ReflectionClass(\get_class($object)))->getMethod($methodName);
        $method->setAccessible(true);

        return $method->invokeArgs($object, $parameters);
    }

    /**
     * @return array{id: int, articleID: int, name: string, ordernumber: string, supplierID: int}
     */
    private function getRandomProduct(?int $excludeProductId = null, ?int $excludeManufacturerId = null): array
    {
        $query = $this->connection->createQueryBuilder()
            ->select('variant.id, variant.articleID, product.name, variant.ordernumber, product.supplierID')
            ->from('s_articles_details', 'variant')
            ->innerJoin('variant', 's_articles', 'product', 'product.id = variant.articleID')
            ->where('variant.active = 1')
            ->andWhere('product.active = 1')
            ->andWhere('ordernumber IS NOT NULL')
            ->andWhere('product.supplierID IS NOT NULL')
            ->andWhere('product.name IS NOT NULL')
            ->setMaxResults(1);

        if ($excludeProductId !== null) {
            $query->andWhere('variant.id <> :excludeProductId');
            $query->setParameter('excludeProductId', $excludeProductId);
        }

        if ($excludeManufacturerId !== null) {
            $query->andWhere('product.supplierID <> :excludeManufacturerId');
            $query->setParameter('excludeManufacturerId', $excludeManufacturerId);
        }

        $randomProduct = $query->execute()->fetchAssociative();
        static::assertIsArray($randomProduct);
        static::assertArrayHasKey('id', $randomProduct);
        static::assertArrayHasKey('articleID', $randomProduct);
        static::assertArrayHasKey('name', $randomProduct);
        static::assertArrayHasKey('ordernumber', $randomProduct);
        static::assertArrayHasKey('supplierID', $randomProduct);

        $randomProduct['id'] = (int) $randomProduct['id'];
        $randomProduct['articleID'] = (int) $randomProduct['articleID'];
        $randomProduct['supplierID'] = (int) $randomProduct['supplierID'];

        return $randomProduct;
    }
}
