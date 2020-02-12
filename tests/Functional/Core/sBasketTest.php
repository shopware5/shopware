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

use Shopware\Components\Random;

class sBasketTest extends PHPUnit\Framework\TestCase
{
    /**
     * Database connection which used for each database operation in this class.
     * Injected over the class constructor
     *
     * @var Enlight_Components_Db_Adapter_Pdo_Mysql
     */
    private $db;

    /**
     * @var sBasket
     */
    private $module;

    /**
     * @var Shopware_Components_Config
     */
    private $config;

    /**
     * @var array The session data
     */
    private $session;

    /**
     * @var Shopware_Components_Snippet_Manager Snippet manager
     */
    private $snippetManager;

    public function setUp(): void
    {
        parent::setUp();

        Shopware()->Front()->setRequest(new Enlight_Controller_Request_RequestHttp());

        $this->snippetManager = Shopware()->Snippets();
        $this->db = Shopware()->Db();
        $this->module = Shopware()->Modules()->Basket();
        $this->session = Shopware()->Session();
        $this->session->offsetSet('sessionId', null);
        $this->module->sSYSTEM->_POST = [];
        $this->module->sSYSTEM->_GET = [];
        $this->config = Shopware()->Config();
        $this->module->sSYSTEM->sCONFIG = &$this->config;
        $this->module->sSYSTEM->sCurrency = Shopware()->Db()->fetchRow('SELECT * FROM s_core_currencies WHERE currency LIKE "EUR"');
        $this->module->sSYSTEM->sSESSION_ID = null;
    }

    /**
     * @covers \sBasket::sGetAmount
     */
    public function testsGetAmount()
    {
        // Test with empty session, expect empty array
        static::assertEquals([], $this->module->sGetAmount());
        $this->module->sSYSTEM->sSESSION_ID = uniqid(rand(), true);
        $this->session->offsetSet('sessionId', $this->module->sSYSTEM->sSESSION_ID);

        $this->db->insert(
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

        $this->db->delete(
            's_order_basket',
            ['sessionID = ?' => $this->session->get('sessionId')]
        );
    }

    /**
     * @covers \sBasket::sCheckBasketQuantities
     */
    public function testsCheckBasketQuantitiesWithEmptySession()
    {
        $this->generateBasketSession();

        // Test with empty session, expect empty array
        static::assertEquals(
            ['hideBasket' => false, 'articles' => []],
            $this->module->sCheckBasketQuantities()
        );
    }

    public function testsCheckBasketQuantitiesWithLowerQuantityThanAvailable()
    {
        $this->generateBasketSession();

        // Fetch an article in stock with stock control
        // Add stock-1 to basket
        // Check that basket is valid
        $inStockArticle = $this->db->fetchRow(
            'SELECT * FROM s_articles_details detail
            INNER JOIN s_articles article
              ON article.id = detail.articleID
            WHERE detail.instock > 2
            AND detail.active = 1
            AND detail.lastStock = 1
            LIMIT 1'
        );

        $this->db->insert(
            's_order_basket',
            [
                'price' => 123,
                'quantity' => $inStockArticle['instock'] - 1,
                'sessionID' => $this->session->get('sessionId'),
                'ordernumber' => $inStockArticle['ordernumber'],
                'articleID' => $inStockArticle['articleID'],
            ]
        );

        $result = $this->module->sCheckBasketQuantities();
        static::assertIsArray($result);
        static::assertArrayHasKey('hideBasket', $result);
        static::assertArrayHasKey('articles', $result);
        static::assertFalse($result['hideBasket']);
        static::assertArrayHasKey($inStockArticle['ordernumber'], $result['articles']);
        static::assertFalse($result['articles'][$inStockArticle['ordernumber']]['OutOfStock']);
    }

    public function testsCheckBasketQuantitiesWithHigherQuantityThanAvailable()
    {
        $this->generateBasketSession();

        // Fetch an article in stock with stock control
        // Add stock+1 to basket
        // Check that basket is invalid
        $outStockArticle = $this->db->fetchRow(
            'SELECT * FROM s_articles_details detail
            INNER JOIN s_articles article
              ON article.id = detail.articleID
            WHERE detail.instock > 5
            AND detail.active = 1
            AND detail.lastStock = 1
            AND article.active = 1
            LIMIT 1'
        );

        $this->db->insert(
            's_order_basket',
            [
                'price' => 123,
                'quantity' => $outStockArticle['instock'] + 1,
                'sessionID' => $this->session->get('sessionId'),
                'ordernumber' => $outStockArticle['ordernumber'],
                'articleID' => $outStockArticle['articleID'],
            ]
        );

        $inStockArticle = $this->db->fetchRow(
            'SELECT * FROM s_articles_details detail
            INNER JOIN s_articles article
              ON article.id = detail.articleID
            WHERE detail.instock > 5
            AND detail.active = 1
            AND detail.lastStock = 1
            AND article.active = 1
            AND article.id != "' . $outStockArticle['articleID'] . '"
            LIMIT 1'
        );

        $this->db->insert(
            's_order_basket',
            [
                'price' => 123,
                'quantity' => $inStockArticle['instock'] - 1,
                'sessionID' => $this->session->get('sessionId'),
                'ordernumber' => $inStockArticle['ordernumber'],
                'articleID' => $inStockArticle['articleID'],
            ]
        );

        $result = $this->module->sCheckBasketQuantities();
        static::assertIsArray($result);
        static::assertArrayHasKey('hideBasket', $result);
        static::assertArrayHasKey('articles', $result);
        static::assertTrue($result['hideBasket']);
        static::assertArrayHasKey($inStockArticle['ordernumber'], $result['articles']);
        static::assertFalse($result['articles'][$inStockArticle['ordernumber']]['OutOfStock']);
        static::assertArrayHasKey($outStockArticle['ordernumber'], $result['articles']);
        static::assertTrue($result['articles'][$outStockArticle['ordernumber']]['OutOfStock']);

        // Clear the current cart
        $this->db->delete(
            's_order_basket',
            ['sessionID = ?' => $this->session->get('sessionId')]
        );
    }

    public function testsCheckBasketQuantitiesWithoutStockControl()
    {
        $this->generateBasketSession();

        // Fetch an article in stock without stock control
        // Add stock+1 to basket
        // Check that basket is valid
        $ignoreStockArticle = $this->db->fetchRow(
            'SELECT * FROM s_articles_details detail
            INNER JOIN s_articles article
              ON article.id = detail.articleID
            WHERE detail.active = 1
            AND detail.lastStock = 0
            LIMIT 1'
        );

        $this->db->insert(
            's_order_basket',
            [
                'price' => 123,
                'quantity' => $ignoreStockArticle['instock'] + 1,
                'sessionID' => $this->session->get('sessionId'),
                'ordernumber' => $ignoreStockArticle['ordernumber'],
                'articleID' => $ignoreStockArticle['articleID'],
            ]
        );

        $result = $this->module->sCheckBasketQuantities();
        static::assertIsArray($result);
        static::assertArrayHasKey('hideBasket', $result);
        static::assertArrayHasKey('articles', $result);
        static::assertFalse($result['hideBasket']);
        static::assertArrayHasKey($ignoreStockArticle['ordernumber'], $result['articles']);
        static::assertFalse($result['articles'][$ignoreStockArticle['ordernumber']]['OutOfStock']);

        // Housekeeping
        $this->db->delete(
            's_order_basket',
            ['sessionID = ?' => $this->session->get('sessionId')]
        );
    }

    /**
     * @covers \sBasket::sGetAmountRestrictedArticles
     */
    public function testsGetAmountRestrictedArticles()
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

        // Add two articles to the basket
        $randomArticleOne = $this->db->fetchRow(
            'SELECT detail.articleID AS articleID, detail.ordernumber AS ordernumber,
              article.supplierID AS supplierID
            FROM s_articles_details detail
            INNER JOIN s_articles article
              ON article.id = detail.articleID
            WHERE detail.active = 1
            AND detail.ordernumber IS NOT NULL
            AND article.supplierID IS NOT NULL
            LIMIT 1'
        );
        $randomArticleTwo = $this->db->fetchRow(
            'SELECT detail.articleID AS articleID, detail.ordernumber AS ordernumber,
              article.supplierID AS supplierID
            FROM s_articles_details detail
            INNER JOIN s_articles article
              ON article.id = detail.articleID
            WHERE detail.active = 1
            AND article.supplierID <> ?
            AND detail.ordernumber IS NOT NULL
            LIMIT 1',
            [$randomArticleOne['supplierID']]
        );

        $this->db->insert(
            's_order_basket',
            [
                'price' => 2,
                'quantity' => 1,
                'sessionID' => $this->session->get('sessionId'),
                'ordernumber' => $randomArticleOne['ordernumber'],
                'articleID' => $randomArticleOne['articleID'],
            ]
        );
        $this->db->insert(
            's_order_basket',
            [
                'price' => 3,
                'quantity' => 1,
                'sessionID' => $this->session->get('sessionId'),
                'ordernumber' => $randomArticleTwo['ordernumber'],
                'articleID' => $randomArticleTwo['articleID'],
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

        // Filter by article one supplier, expect article one value
        static::assertEquals(
            ['totalAmount' => 2],
            $this->invokeMethod(
                $this->module,
                'sGetAmountRestrictedArticles',
                [null, $randomArticleOne['supplierID']]
            )
        );
        // Filter by article two supplier, expect article two value
        static::assertEquals(
            ['totalAmount' => 3],
            $this->invokeMethod(
                $this->module,
                'sGetAmountRestrictedArticles',
                [null, $randomArticleTwo['supplierID']]
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

        // Filter by article one, expect article one value
        static::assertEquals(
            ['totalAmount' => 2],
            $this->invokeMethod(
                $this->module,
                'sGetAmountRestrictedArticles',
                [[$randomArticleOne['ordernumber']], null]
            )
        );
        // Filter by article two, expect article two value
        static::assertEquals(
            ['totalAmount' => 3],
            $this->invokeMethod(
                $this->module,
                'sGetAmountRestrictedArticles',
                [[$randomArticleTwo['ordernumber']], null]
            )
        );
        // Filter by both articles, expect total basket value
        static::assertEquals(
            ['totalAmount' => 5],
            $this->invokeMethod(
                $this->module,
                'sGetAmountRestrictedArticles',
                [
                    [$randomArticleOne['ordernumber'], $randomArticleTwo['ordernumber']],
                    null,
                ]
            )
        );
        // Filter by another article, expect empty value
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
        $this->db->delete(
            's_order_basket',
            ['sessionID = ?' => $this->session->get('sessionId')]
        );
    }

    /**
     * @covers \sBasket::sInsertPremium
     */
    public function testsInsertPremium()
    {
        // Test with empty session, expect true
        static::assertTrue($this->module->sInsertPremium());

        // Create session id
        $this->module->sSYSTEM->sSESSION_ID = uniqid(rand(), true);
        $this->session->offsetSet('sessionId', $this->module->sSYSTEM->sSESSION_ID);

        // Test with session, expect true
        static::assertTrue($this->module->sInsertPremium());

        $normalArticle = $this->db->fetchRow(
            'SELECT * FROM s_articles_details detail
            INNER JOIN s_articles article
              ON article.id = detail.articleID
            WHERE detail.active = 1
            AND detail.articleId NOT IN (
              SELECT id FROM s_addon_premiums
            )
            LIMIT 1'
        );

        $premiumArticleOne = $this->db->fetchRow(
            'SELECT article.id, detail.ordernumber
            FROM s_articles_details detail
            INNER JOIN s_articles article
              ON article.id = detail.articleID
            WHERE detail.active = 1
            AND detail.ordernumber NOT IN (
              SELECT ordernumber FROM s_addon_premiums
            )
            LIMIT 1'
        );
        $premiumArticleTwo = $this->db->fetchRow(
            'SELECT article.id, detail.ordernumber
            FROM s_articles_details detail
            INNER JOIN s_articles article
              ON article.id = detail.articleID
            WHERE detail.active = 1
            AND detail.ordernumber IN (
              SELECT ordernumber FROM s_addon_premiums
            )
            LIMIT 1'
        );

        // Add one normal article to basket
        // Test that calling sInsertPremium does nothing
        $this->db->insert(
            's_order_basket',
            [
                'price' => 1,
                'quantity' => 1,
                'sessionID' => $this->session->get('sessionId'),
                'ordernumber' => $normalArticle['ordernumber'],
                'articleID' => $normalArticle['articleID'],
                'modus' => 0,
            ]
        );
        static::assertTrue($this->module->sInsertPremium());
        static::assertEquals(
            1,
            $this->db->fetchOne(
                'SELECT count(*) FROM s_order_basket WHERE sessionID = ?',
                [$this->module->sSYSTEM->sSESSION_ID]
            )
        );

        // Add premium articles to basket
        // Test that calling sInsertPremium removes them
        $this->db->insert(
            's_order_basket',
            [
                'price' => 1,
                'quantity' => 1,
                'sessionID' => $this->session->get('sessionId'),
                'ordernumber' => $premiumArticleOne['ordernumber'],
                'articleID' => $premiumArticleOne['id'],
                'modus' => 1,
            ]
        );
        $this->db->insert(
            's_order_basket',
            [
                'price' => 1,
                'quantity' => 1,
                'sessionID' => $this->session->get('sessionId'),
                'ordernumber' => $premiumArticleTwo['ordernumber'],
                'articleID' => $premiumArticleTwo['id'],
                'modus' => 1,
            ]
        );
        static::assertTrue($this->module->sInsertPremium());
        static::assertEquals(
            1,
            $this->db->fetchOne(
                'SELECT count(*) FROM s_order_basket WHERE sessionID = ?',
                [$this->module->sSYSTEM->sSESSION_ID]
            )
        );

        // Add sAddPremium to _GET.
        // Basket price is 1, so expect premium articles to be denied
        $this->module->sSYSTEM->_GET['sAddPremium'] = $premiumArticleTwo['ordernumber'];
        static::assertFalse($this->module->sInsertPremium());

        // Increase basket price and retry
        $this->db->insert(
            's_order_basket',
            [
                'price' => 10000,
                'quantity' => 1,
                'sessionID' => $this->session->get('sessionId'),
                'ordernumber' => $normalArticle['ordernumber'],
                'articleID' => $normalArticle['articleID'],
                'modus' => 0,
            ]
        );
        // Will still get false due to cache
        static::assertFalse($this->module->sInsertPremium());

        // Change the premium article to a non-premium, fail
        $this->module->sSYSTEM->_GET['sAddPremium'] = $premiumArticleOne['ordernumber'];
        static::assertFalse($this->module->sInsertPremium());
        static::assertEquals(
            2,
            $this->db->fetchOne(
                'SELECT count(*) FROM s_order_basket WHERE sessionID = ?',
                [$this->module->sSYSTEM->sSESSION_ID]
            )
        );

        // Change the premium article to a premium, succeed
        $this->module->sSYSTEM->_GET['sAddPremium'] = $premiumArticleTwo['ordernumber'];
        static::assertGreaterThan(0, $this->module->sInsertPremium());
        static::assertEquals(
            3,
            $this->db->fetchOne(
                'SELECT count(*) FROM s_order_basket WHERE sessionID = ?',
                [$this->module->sSYSTEM->sSESSION_ID]
            )
        );

        // Housekeeping
        $this->db->delete(
            's_order_basket',
            ['sessionID = ?' => $this->session->get('sessionId')]
        );
    }

    public function testPremiumOrderNumberExport()
    {
        $this->db->beginTransaction();

        // Create session id
        $this->module->sSYSTEM->sSESSION_ID = uniqid(rand(), true);
        $this->session->offsetSet('sessionId', $this->module->sSYSTEM->sSESSION_ID);

        // Test with session, expect true
        static::assertTrue($this->module->sInsertPremium());

        $ordernumberExport = 'test' . random_int(1000, 9999);

        //Insert a new Premium product that has a ordernumber_export different from the ordernumber
        $this->db->insert('s_addon_premiums', ['startprice' => 0, 'ordernumber' => 'SW10137', 'ordernumber_export' => $ordernumberExport, 'subshopID' => 0]);

        //sInsertPremium gets the premium to add from the Request, therefore we do set it here
        $front = Shopware()->Front();
        $front->Request()->setQuery('sAddPremium', 'SW10137');

        //add the premium item to the basket
        $this->module->sInsertPremium();

        //check if the ordernumber_export from s_addon_premiums has been added to s_order_basket as ordernumber
        static::assertEquals(
            $ordernumberExport,
            $this->db->fetchOne(
                'SELECT ordernumber FROM s_order_basket WHERE sessionID = ? AND modus = 1',
                [$this->module->sSYSTEM->sSESSION_ID]
            )
        );

        Shopware()->Front()->setRequest(new Enlight_Controller_Request_RequestHttp());
        $this->db->rollBack();
    }

    /**
     * @covers \sBasket::getMaxTax
     */
    public function testGetMaxTax()
    {
        // Test with empty session, expect false
        static::assertFalse($this->module->getMaxTax());

        // Create session id
        $this->module->sSYSTEM->sSESSION_ID = uniqid(rand(), true);
        $this->session->offsetSet('sessionId', $this->module->sSYSTEM->sSESSION_ID);

        // Test with session and empty basket, expect false
        static::assertFalse($this->module->getMaxTax());

        $products = $this->db->fetchAll(
            'SELECT * FROM s_articles_details detail
            INNER JOIN s_articles article
              ON article.id = detail.articleID
            INNER JOIN s_core_tax tax
              ON tax.id = article.taxID
            WHERE detail.active = 1
            ORDER BY tax.tax
            LIMIT 2'
        );
        $originalTaxId = $products[0]['taxID'];

        $this->db->update('s_articles', ['taxID' => 4], ['id = ?' => $products[0]['id']]);

        // Add one article, check that he is the new maximum
        $this->db->insert(
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

        // Add another article, check that we get the max of the two
        $this->db->insert(
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

        $this->db->update('s_articles', ['taxID' => $originalTaxId], ['id = ?' => $products[0]['id']]);

        // Housekeeping
        $this->db->delete(
            's_order_basket',
            ['sessionID = ?' => $this->session->get('sessionId')]
        );
    }

    /**
     * @covers \sBasket::sAddVoucher
     */
    public function testsAddVoucherWithAbsoluteVoucher()
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
        $this->module->sSYSTEM->sSESSION_ID = uniqid(rand(), true);
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
            'ordercode' => uniqid(rand(), true),
            'modus' => 0,
        ];
        $this->db->insert(
            's_emarketing_vouchers',
            $voucherData
        );
        $this->module->sSYSTEM->sSESSION_ID = uniqid(rand(), true);
        $this->session->offsetSet('sessionId', $this->module->sSYSTEM->sSESSION_ID);
        $result = $this->module->sAddVoucher('testOne');

        static::assertIsArray($result);
        static::assertArrayHasKey('sErrorFlag', $result);
        static::assertArrayHasKey('sErrorMessages', $result);
        static::assertTrue($result['sErrorFlag']);
        static::assertContains('Der Mindestumsatz für diesen Gutschein beträgt 10,00&nbsp;&euro;', $result['sErrorMessages']);

        // Check if a currency switch is reflected in the snippet correctly
        $currencyDe = Shopware()->Container()->get('currency');
        Shopware()->Container()->set('currency', new \Zend_Currency('GBP', new \Zend_Locale('en_GB')));

        $this->module->sSYSTEM->sSESSION_ID = uniqid(rand(), true);
        $this->session->offsetSet('sessionId', $this->module->sSYSTEM->sSESSION_ID);
        $result = $this->module->sAddVoucher('testOne');
        static::assertIsArray($result);
        static::assertArrayHasKey('sErrorFlag', $result);
        static::assertArrayHasKey('sErrorMessages', $result);
        static::assertTrue($result['sErrorFlag']);

        static::assertContains('Der Mindestumsatz für diesen Gutschein beträgt &pound;10.00', $result['sErrorMessages']);

        Shopware()->Container()->set('currency', $currencyDe);

        // Add one article to the basket with enough value to use discount
        $randomArticle = $this->db->fetchRow(
            'SELECT * FROM s_articles_details detail
            INNER JOIN s_articles article
              ON article.id = detail.articleID
            WHERE detail.active = 1
            LIMIT 1'
        );
        $this->db->insert(
            's_order_basket',
            [
                'price' => $voucherData['minimumcharge'] + 1,
                'quantity' => 1,
                'sessionID' => $this->session->get('sessionId'),
                'ordernumber' => $randomArticle['ordernumber'],
                'articleID' => $randomArticle['articleID'],
            ]
        );

        // Add voucher to the orders table, so we can test the usage limit
        $this->db->insert(
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
        $this->db->delete(
            's_order_details',
            [
                'articleordernumber = ?' => $voucherData['ordercode'],
            ]
        );

        $previousAmount = $this->module->sGetAmount();
        // Voucher should work ok now
        static::assertTrue($this->module->sAddVoucher('testOne'));
        static::assertLessThan($previousAmount, $this->module->sGetAmount());

        // Test the voucher values with tax from user group
        $discount = $this->db->fetchRow(
            'SELECT * FROM s_order_basket WHERE modus = 2 and sessionID = ?',
            [$this->module->sSYSTEM->sSESSION_ID]
        );
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
        $this->db->delete(
            's_order_basket',
            ['sessionID = ?' => $this->session->get('sessionId')]
        );
        $this->db->delete(
            's_emarketing_vouchers',
            ['vouchercode = ?' => 'testOne']
        );
    }

    /**
     * @covers \sBasket::sAddVoucher
     */
    public function testsAddVoucherWithLimitedVoucher()
    {
        $voucherData = [
            'vouchercode' => 'testTwo',
            'description' => 'testTwo description',
            'numberofunits' => 10,
            'value' => 10,
            'minimumcharge' => 10,
            'ordercode' => uniqid(rand(), true),
            'modus' => 1,
            'taxconfig' => 'none',
        ];
        $this->db->insert(
            's_emarketing_vouchers',
            $voucherData
        );
        $voucherId = $this->db->lastInsertId();

        $voucherCodeData = [
            'voucherID' => $voucherId,
            'code' => uniqid(rand(), true),
            'userID' => null,
            'cashed' => 0,
        ];
        $this->db->insert(
            's_emarketing_voucher_codes',
            $voucherCodeData
        );

        $customer = $this->createDummyCustomer();
        $this->session['sUserId'] = $customer->getId();
        $this->module->sSYSTEM->sSESSION_ID = uniqid(rand(), true);
        $this->session->offsetSet('sessionId', $this->module->sSYSTEM->sSESSION_ID);

        // Test with one-time code, fail due to minimum amount (cart is empty)
        $result = $this->module->sAddVoucher($voucherCodeData['code']);
        static::assertIsArray($result);
        static::assertArrayHasKey('sErrorFlag', $result);
        static::assertArrayHasKey('sErrorMessages', $result);
        static::assertTrue($result['sErrorFlag']);
        static::assertContains('Der Mindestumsatz für diesen Gutschein beträgt 10,00&nbsp;&euro;', $result['sErrorMessages']);

        // Add one article to the basket with enough value to use discount
        $randomArticle = $this->db->fetchRow(
            'SELECT * FROM s_articles_details detail
            INNER JOIN s_articles article
              ON article.id = detail.articleID
            WHERE detail.active = 1
            LIMIT 1'
        );
        $this->db->insert(
            's_order_basket',
            [
                'price' => $voucherData['minimumcharge'] + 1,
                'quantity' => 1,
                'sessionID' => $this->session->get('sessionId'),
                'ordernumber' => $randomArticle['ordernumber'],
                'articleID' => $randomArticle['articleID'],
            ]
        );

        $previousAmount = $this->module->sGetAmount();
        // Test with one-time code, success
        static::assertTrue($this->module->sAddVoucher($voucherCodeData['code']));
        static::assertLessThan($previousAmount, $this->module->sGetAmount());

        // Test the voucher values. This voucher has no taxes
        $discount = $this->db->fetchRow(
            'SELECT * FROM s_order_basket WHERE modus = 2 and sessionID = ?',
            [$this->module->sSYSTEM->sSESSION_ID]
        );
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
        $this->db->delete(
            's_order_basket',
            ['sessionID = ?' => $this->session->get('sessionId')]
        );
        $this->db->delete(
            's_emarketing_vouchers',
            ['vouchercode = ?' => 'testOne']
        );
        $this->db->delete(
            's_emarketing_voucher_codes',
            ['code = ?' => $voucherCodeData['code']]
        );
        $this->deleteDummyCustomer($customer);
    }

    /**
     * @covers \sBasket::sAddVoucher
     */
    public function testsAddVoucherWithSubShopVoucher()
    {
        $oldTaxValue = $this->module->sSYSTEM->sUSERGROUPDATA['tax'];
        $this->module->sSYSTEM->sUSERGROUPDATA['tax'] = null;

        $tax = $this->db->fetchRow('SELECT * FROM s_core_tax WHERE tax <> 19 LIMIT 1');

        $voucherData = [
            'vouchercode' => 'testTwo',
            'description' => 'testTwo description',
            'numberofunits' => 1,
            'numorder' => 1,
            'value' => 10,
            'minimumcharge' => 10,
            'ordercode' => uniqid(random_int(PHP_INT_MIN, PHP_INT_MAX), true),
            'modus' => 0,
            'subshopID' => 3,
            'taxconfig' => $tax['id'],
        ];
        $this->db->insert(
            's_emarketing_vouchers',
            $voucherData
        );

        $customer = $this->createDummyCustomer();
        $this->session['sUserId'] = $customer->getId();
        $this->module->sSYSTEM->sSESSION_ID = uniqid(random_int(PHP_INT_MIN, PHP_INT_MAX), true);
        $this->session->offsetSet('sessionId', $this->module->sSYSTEM->sSESSION_ID);

        // Add one article to the basket with enough value to use discount
        $randomArticle = $this->db->fetchRow(
            'SELECT * FROM s_articles_details detail
            INNER JOIN s_articles article
              ON article.id = detail.articleID
            WHERE detail.active = 1
            LIMIT 1'
        );
        $this->db->insert(
            's_order_basket',
            [
                'price' => $voucherData['minimumcharge'] + 1,
                'quantity' => 1,
                'sessionID' => $this->session->get('sessionId'),
                'ordernumber' => $randomArticle['ordernumber'],
                'articleID' => $randomArticle['articleID'],
            ]
        );

        // Change current subshop id, test and expect success
        Shopware()->Container()->get('shopware_storefront.context_service')->getShopContext()->getShop()->setId(3);

        $previousAmount = $this->module->sGetAmount();
        // Test with one-time code, success
        static::assertTrue($this->module->sAddVoucher($voucherData['vouchercode']));
        static::assertLessThan($previousAmount, $this->module->sGetAmount());

        // Test the voucher values with custom tax from voucher
        $discount = $this->db->fetchRow(
            'SELECT * FROM s_order_basket WHERE modus = 2 and sessionID = ?',
            [$this->module->sSYSTEM->sSESSION_ID]
        );
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
        $this->db->delete(
            's_order_basket',
            ['sessionID = ?' => $this->session->get('sessionId')]
        );
        $this->db->delete(
            's_emarketing_vouchers',
            ['vouchercode = ?' => $voucherData['vouchercode']]
        );
        $this->deleteDummyCustomer($customer);
        $this->module->sSYSTEM->sUSERGROUPDATA['tax'] = $oldTaxValue;
    }

    /**
     * @covers \sBasket::sAddVoucher
     */
    public function testsAddVoucherWithMultipleVouchers()
    {
        $voucherOneData = [
            'vouchercode' => 'testOne',
            'description' => 'testOne description',
            'numberofunits' => 1,
            'numorder' => 1,
            'value' => 10,
            'minimumcharge' => 10,
            'ordercode' => uniqid(rand(), true),
            'modus' => 0,
            'subshopID' => 3,
        ];
        $this->db->insert(
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
            'ordercode' => uniqid(rand(), true),
            'modus' => 0,
            'subshopID' => 3,
        ];
        $this->db->insert(
            's_emarketing_vouchers',
            $voucherTwoData
        );

        $customer = $this->createDummyCustomer();
        $this->session['sUserId'] = $customer->getId();
        $this->module->sSYSTEM->sSESSION_ID = uniqid(rand(), true);
        $this->session->offsetSet('sessionId', $this->module->sSYSTEM->sSESSION_ID);

        // Add one article to the basket with enough value to use discount
        $randomArticle = $this->db->fetchRow(
            'SELECT * FROM s_articles_details detail
            INNER JOIN s_articles article
              ON article.id = detail.articleID
            WHERE detail.active = 1
            LIMIT 1'
        );
        $this->db->insert(
            's_order_basket',
            [
                'price' => $voucherOneData['minimumcharge'] + 1,
                'quantity' => 1,
                'sessionID' => $this->session->get('sessionId'),
                'ordernumber' => $randomArticle['ordernumber'],
                'articleID' => $randomArticle['articleID'],
            ]
        );

        // Change current subshop, test and expect success
        Shopware()->Container()->get('shopware_storefront.context_service')->getShopContext()->getShop()->setId(3);

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
        $this->db->delete(
            's_order_basket',
            ['sessionID = ?' => $this->session->get('sessionId')]
        );
        $this->db->delete(
            's_emarketing_vouchers',
            ['vouchercode = ?' => $voucherOneData['vouchercode']]
        );
        $this->db->delete(
            's_emarketing_vouchers',
            ['vouchercode = ?' => $voucherTwoData['vouchercode']]
        );
        $this->deleteDummyCustomer($customer);
    }

    /**
     * @covers \sBasket::sAddVoucher
     */
    public function testsAddVoucherWithCustomerGroup()
    {
        $randomCustomerGroup = $this->db->fetchAll(
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
            'ordercode' => uniqid(rand(), true),
            'modus' => 0,
            'customergroup' => $randomCustomerGroup[0]['id'],
        ];
        // Try with valid voucher code, empty basket
        $this->db->insert(
            's_emarketing_vouchers',
            $voucherData
        );

        $customer = $this->createDummyCustomer();
        $this->db->update(
            's_user',
            ['customergroup' => $randomCustomerGroup[1]['groupkey']],
            ['id = ?' => $customer->getId()]
        );
        $this->module->sSYSTEM->sUSERGROUPDATA['id'] = $randomCustomerGroup[1]['id'];
        $this->session['sUserId'] = $customer->getId();
        $this->module->sSYSTEM->sSESSION_ID = uniqid(rand(), true);
        $this->session->offsetSet('sessionId', $this->module->sSYSTEM->sSESSION_ID);

        // Add one article to the basket with enough value to use discount
        $randomArticle = $this->db->fetchRow(
            'SELECT * FROM s_articles_details detail
            INNER JOIN s_articles article
              ON article.id = detail.articleID
            WHERE detail.active = 1
            LIMIT 1'
        );
        $this->db->insert(
            's_order_basket',
            [
                'price' => $voucherData['minimumcharge'] + 1,
                'quantity' => 1,
                'sessionID' => $this->session->get('sessionId'),
                'ordernumber' => $randomArticle['ordernumber'],
                'articleID' => $randomArticle['articleID'],
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
        $this->db->update(
            's_user',
            ['customergroup' => $randomCustomerGroup[0]['groupkey']],
            ['id = ?' => $customer->getId()]
        );
        $this->module->sSYSTEM->sUSERGROUPDATA['id'] = $randomCustomerGroup[0]['id'];

        $previousAmount = $this->module->sGetAmount();
        // Test with one-time code, success
        static::assertTrue($this->module->sAddVoucher($voucherData['vouchercode']));
        static::assertLessThan($previousAmount, $this->module->sGetAmount());

        // Housekeeping
        $this->db->delete(
            's_order_basket',
            ['sessionID = ?' => $this->session->get('sessionId')]
        );
        $this->db->delete(
            's_emarketing_vouchers',
            ['vouchercode = ?' => $voucherData['vouchercode']]
        );
        $this->deleteDummyCustomer($customer);
    }

    /**
     * @covers \sBasket::sAddVoucher
     */
    public function testsAddVoucherWithArticle()
    {
        $randomArticles = $this->db->fetchAll(
            'SELECT * FROM s_articles_details detail
            INNER JOIN s_articles article
              ON article.id = detail.articleID
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
            'ordercode' => uniqid(rand(), true),
            'modus' => 0,
            'restrictarticles' => $randomArticles[0]['ordernumber'],
        ];
        // Try with valid voucher code, empty basket
        $this->db->insert(
            's_emarketing_vouchers',
            $voucherData
        );

        $customer = $this->createDummyCustomer();
        $this->session['sUserId'] = $customer->getId();
        $this->module->sSYSTEM->sSESSION_ID = uniqid(rand(), true);
        $this->session->offsetSet('sessionId', $this->module->sSYSTEM->sSESSION_ID);

        // Add one article to the basket with enough value to use discount
        $this->db->insert(
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

        $this->db->insert(
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
        $this->db->delete(
            's_order_basket',
            ['sessionID = ?' => $this->session->get('sessionId')]
        );
        $this->db->delete(
            's_emarketing_vouchers',
            ['vouchercode = ?' => $voucherData['vouchercode']]
        );
        $this->deleteDummyCustomer($customer);
    }

    /**
     * @covers \sBasket::sAddVoucher
     */
    public function testsAddVoucherWithCurrencyFactor()
    {
        // Prepare a voucher
        $voucherData = [
            'vouchercode' => 'testOne',
            'description' => 'testOne description',
            'numberofunits' => 1,
            'value' => 10,
            'minimumcharge' => 10,
            'ordercode' => uniqid(rand(), true),
            'modus' => 0,
        ];
        $this->db->insert(
            's_emarketing_vouchers',
            $voucherData
        );

        // Fetch a random article
        $randomArticle = $this->db->fetchRow(
            'SELECT * FROM s_articles_details detail
            INNER JOIN s_articles article
              ON article.id = detail.articleID
            WHERE detail.active = 1
            LIMIT 1'
        );

        // Generate session id
        $this->module->sSYSTEM->sSESSION_ID = uniqid(rand(), true);
        $this->session->offsetSet('sessionId', $this->module->sSYSTEM->sSESSION_ID);

        // Define different currency factors to test
        $currencyFactors = [0, .5, 1, 1.5, 2];

        foreach ($currencyFactors as $currencyFactor) {
            // If the currency factor is set to 0, a fallback of 1 should be used
            $currencyFactorForCalculation = $currencyFactor ?: 1;

            // Prepare values to test against later
            $deltaBetweenVoucherAndArticlePrice = 1;
            $this->module->sSYSTEM->sCurrency['factor'] = $currencyFactor;
            $basketAmountWithoutVoucher = $voucherData['minimumcharge'] * $currencyFactorForCalculation + $deltaBetweenVoucherAndArticlePrice;

            // Add one article to the basket with enough value to use discount
            $this->db->insert(
                's_order_basket',
                [
                    'price' => $basketAmountWithoutVoucher,
                    'quantity' => 1,
                    'sessionID' => $this->session->get('sessionId'),
                    'ordernumber' => $randomArticle['ordernumber'],
                    'articleID' => $randomArticle['articleID'],
                ]
            );

            static::assertEquals($basketAmountWithoutVoucher, $this->module->sGetAmount()['totalAmount']);
            static::assertTrue($this->module->sAddVoucher($voucherData['vouchercode']));
            static::assertEquals($deltaBetweenVoucherAndArticlePrice, $this->module->sGetAmount()['totalAmount']);

            // Housekeeping
            $this->db->delete(
                's_order_basket',
                ['sessionID = ?' => $this->session->get('sessionId')]
            );
        }

        // Housekeeping
        $this->db->delete(
            's_emarketing_vouchers',
            ['vouchercode = ?' => $voucherData['vouchercode']]
        );
    }

    /**
     * @covers \sBasket::sAddVoucher
     */
    public function testsAddVoucherWithSupplier()
    {
        $randomArticleOne = $this->db->fetchRow(
            'SELECT * FROM s_articles_details detail
            INNER JOIN s_articles article
              ON article.id = detail.articleID
            WHERE detail.active = 1
            LIMIT 1'
        );
        $randomArticleTwo = $this->db->fetchRow(
            'SELECT * FROM s_articles_details detail
            INNER JOIN s_articles article
              ON article.id = detail.articleID
            WHERE detail.active = 1
            AND supplierID <> ?
            LIMIT 1,5',
            [$randomArticleOne['supplierID']]
        );

        $voucherData = [
            'vouchercode' => 'testOne',
            'description' => 'testOne description',
            'numberofunits' => 1,
            'numorder' => 1,
            'value' => 10,
            'minimumcharge' => 10,
            'ordercode' => uniqid('ordercode', true),
            'modus' => 0,
            'bindtosupplier' => $randomArticleOne['supplierID'],
        ];
        // Try with valid voucher code, empty basket
        $this->db->insert(
            's_emarketing_vouchers',
            $voucherData
        );

        $customer = $this->createDummyCustomer();
        $this->session['sUserId'] = $customer->getId();
        $this->generateBasketSession();

        // Add first article to the basket with enough value to use discount, should fail
        $this->db->insert(
            's_order_basket',
            [
                'price' => $voucherData['minimumcharge'] + 1,
                'quantity' => 1,
                'sessionID' => $this->session->get('sessionId'),
                'ordernumber' => $randomArticleTwo['ordernumber'],
                'articleID' => $randomArticleTwo['articleID'],
            ]
        );

        $supplierOne = $this->db->fetchOne(
            'SELECT name FROM s_articles_supplier WHERE id = ?',
            [$randomArticleOne['supplierID']]
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

        $this->db->insert(
            's_order_basket',
            [
                'price' => $voucherData['minimumcharge'] + 1,
                'quantity' => 1,
                'sessionID' => $this->session->get('sessionId'),
                'ordernumber' => $randomArticleOne['ordernumber'],
                'articleID' => $randomArticleOne['articleID'],
            ]
        );

        $previousAmount = $this->module->sGetAmount();
        // Test with one-time code, success
        static::assertTrue($this->module->sAddVoucher($voucherData['vouchercode']));
        static::assertLessThan($previousAmount, $this->module->sGetAmount());

        // Housekeeping
        $this->db->delete(
            's_order_basket',
            ['sessionID = ?' => $this->session->get('sessionId')]
        );
        $this->db->delete(
            's_emarketing_vouchers',
            ['vouchercode = ?' => $voucherData['vouchercode']]
        );
        $this->deleteDummyCustomer($customer);
    }

    /**
     * @covers \sBasket::sGetBasketIds
     */
    public function testsGetBasketIds()
    {
        $randomArticles = $this->db->fetchAll(
            'SELECT * FROM s_articles_details detail
            INNER JOIN s_articles article
              ON article.id = detail.articleID
            WHERE detail.active = 1
             LIMIT 2'
        );

        $this->module->sSYSTEM->sSESSION_ID = uniqid(rand(), true);
        $this->session->offsetSet('sessionId', $this->module->sSYSTEM->sSESSION_ID);

        // Test with empty basket, empty
        static::assertNull($this->module->sGetBasketIds());

        // Add the first article to the basket, test we get the article id
        $this->db->insert(
            's_order_basket',
            [
                'price' => 1,
                'quantity' => 1,
                'sessionID' => $this->session->get('sessionId'),
                'ordernumber' => $randomArticles[0]['ordernumber'],
                'articleID' => $randomArticles[0]['articleID'],
            ]
        );
        static::assertEquals(
            [$randomArticles[0]['articleID']],
            $this->module->sGetBasketIds()
        );

        // Add the first article to the basket again, test we get the same result
        $this->db->insert(
            's_order_basket',
            [
                'price' => 1,
                'quantity' => 1,
                'sessionID' => $this->session->get('sessionId'),
                'ordernumber' => $randomArticles[0]['ordernumber'],
                'articleID' => $randomArticles[0]['articleID'],
            ]
        );
        static::assertEquals(
            [$randomArticles[0]['articleID']],
            $this->module->sGetBasketIds()
        );

        // Add the second article to the basket, test we get the two ids
        $this->db->insert(
            's_order_basket',
            [
                'price' => 1,
                'quantity' => 1,
                'sessionID' => $this->session->get('sessionId'),
                'ordernumber' => $randomArticles[1]['ordernumber'],
                'articleID' => $randomArticles[1]['articleID'],
            ]
        );

        $basketIds = $this->module->sGetBasketIds();
        static::assertContains(
            $randomArticles[0]['articleID'],
            $basketIds
        );
        static::assertContains(
            $randomArticles[1]['articleID'],
            $basketIds
        );

        // Housekeeping
        $this->db->delete(
            's_order_basket',
            ['sessionID = ?' => $this->session->get('sessionId')]
        );
    }

    /**
     * @covers \sBasket::sCheckMinimumCharge
     */
    public function testsCheckMinimumCharge()
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

        $this->module->sSYSTEM->sSESSION_ID = uniqid(rand(), true);
        $this->session->offsetSet('sessionId', $this->module->sSYSTEM->sSESSION_ID);

        // Add one article to the basket with enough value to use discount
        $randomArticle = $this->db->fetchRow(
            'SELECT * FROM s_articles_details detail
            INNER JOIN s_articles article
              ON article.id = detail.articleID
            WHERE detail.active = 1
            LIMIT 1'
        );
        $this->db->insert(
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
        $this->db->insert(
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
        $this->db->delete(
            's_order_basket',
            ['sessionID = ?' => $this->session->get('sessionId')]
        );
    }

    /**
     * @covers \sBasket::sInsertSurcharge
     */
    public function testsInsertSurcharge()
    {
        $oldMinimumOrder = $this->module->sSYSTEM->sUSERGROUPDATA['minimumorder'];
        $oldMinimumOrderSurcharge = $this->module->sSYSTEM->sUSERGROUPDATA['minimumordersurcharge'];

        // Empty basket, expect false
        static::assertFalse(
            $this->invokeMethod(
                $this->module,
                'sInsertSurcharge',
                []
            )
        );

        $this->module->sSYSTEM->sSESSION_ID = uniqid(rand(), true);
        $this->session->offsetSet('sessionId', $this->module->sSYSTEM->sSESSION_ID);

        // Add one article to the basket with value lower that minimumordersurcharge
        $randomArticle = $this->db->fetchRow(
            'SELECT * FROM s_articles_details detail
            INNER JOIN s_articles article
              ON article.id = detail.articleID
            WHERE detail.active = 1
            LIMIT 1'
        );
        $this->db->insert(
            's_order_basket',
            [
                'price' => 2,
                'quantity' => 1,
                'sessionID' => $this->session->get('sessionId'),
                'ordernumber' => $randomArticle['ordernumber'],
                'articleID' => $randomArticle['articleID'],
            ]
        );

        $this->module->sSYSTEM->sUSERGROUPDATA['minimumordersurcharge'] = 5;
        $this->module->sSYSTEM->sUSERGROUPDATA['minimumorder'] = 10;

        // Check that we have no surcharge
        static::assertEmpty(
            $this->db->fetchRow(
                'SELECT * FROM s_order_basket WHERE sessionID = ? AND modus=4',
                [$this->module->sSYSTEM->sSESSION_ID]
            )
        );

        // Add surcharge, expect success (null)
        static::assertNull(
            $this->invokeMethod(
                $this->module,
                'sInsertSurcharge',
                []
            )
        );

        // Fetch the surcharge row, should have price 5
        $surchargeRow = $this->db->fetchRow(
            'SELECT * FROM s_order_basket WHERE sessionID = ? AND modus=4',
            [$this->module->sSYSTEM->sSESSION_ID]
        );
        static::assertEquals(5, $surchargeRow['price']);

        // Housekeeping
        $this->module->sSYSTEM->sUSERGROUPDATA['minimumorder'] = $oldMinimumOrder;
        $this->module->sSYSTEM->sUSERGROUPDATA['minimumordersurcharge'] = $oldMinimumOrderSurcharge;
        $this->db->delete(
            's_order_basket',
            ['sessionID = ?' => $this->session->get('sessionId')]
        );
    }

    /**
     * @covers \sBasket::sInsertSurchargePercent
     */
    public function testsInsertSurchargePercent()
    {
        // No user and no payment id, expect false
        static::assertFalse(
            $this->invokeMethod(
                $this->module,
                'sInsertSurchargePercent',
                []
            )
        );

        $customer = $this->createDummyCustomer();
        $paymentData = [
            'name' => 'testPaymentMean',
            'description' => 'testPaymentMean',
            'debit_percent' => 5,
        ];
        $this->db->insert('s_core_paymentmeans', $paymentData);
        $paymentMeanId = $this->db->lastInsertId();
        $this->db->update(
            's_user',
            ['paymentID' => $paymentMeanId],
            ['id = ?' => $customer->getId()]
        );
        $this->session['sUserId'] = $customer->getId();
        $this->module->sSYSTEM->sSESSION_ID = uniqid(rand(), true);
        $this->session->offsetSet('sessionId', $this->module->sSYSTEM->sSESSION_ID);

        // Empty basket, expect false
        static::assertFalse(
            $this->invokeMethod(
                $this->module,
                'sInsertSurchargePercent',
                []
            )
        );

        // Add one article to the basket with low amount
        $randomArticle = $this->db->fetchRow(
            'SELECT * FROM s_articles_details detail
            INNER JOIN s_articles article
              ON article.id = detail.articleID
            WHERE detail.active = 1
            LIMIT 1'
        );
        $this->db->insert(
            's_order_basket',
            [
                'price' => 2,
                'quantity' => 1,
                'sessionID' => $this->session->get('sessionId'),
                'ordernumber' => $randomArticle['ordernumber'],
                'articleID' => $randomArticle['articleID'],
            ]
        );

        // Check that we have no surcharge
        static::assertEmpty(
            $this->db->fetchRow(
                'SELECT * FROM s_order_basket WHERE sessionID = ? AND modus=4',
                [$this->module->sSYSTEM->sSESSION_ID]
            )
        );

        // Add surcharge, expect success (null)
        static::assertNull(
            $this->invokeMethod(
                $this->module,
                'sInsertSurchargePercent',
                []
            )
        );

        // Fetch the surcharge row, should have price 5
        $surchargeRow = $this->db->fetchRow(
            'SELECT * FROM s_order_basket WHERE sessionID = ? AND modus = 4',
            [$this->module->sSYSTEM->sSESSION_ID]
        );
        static::assertEquals(2 / 100 * 5, $surchargeRow['price']);

        // Housekeeping
        $this->deleteDummyCustomer($customer);
        $this->db->delete(
            's_order_basket',
            ['sessionID = ?' => $this->session->get('sessionId')]
        );
        $this->db->delete(
            's_core_paymentmeans',
            ['name = ?' => 'testPaymentMean']
        );
    }

    /**
     * @covers \sBasket::sGetBasket
     */
    public function testsGetBasket()
    {
        // Test with empty basket
        static::assertEquals([], $this->module->sGetBasket());

        $this->module->sSYSTEM->sSESSION_ID = uniqid(rand(), true);
        $this->session->offsetSet('sessionId', $this->module->sSYSTEM->sSESSION_ID);

        // Add one article to the basket with low amount
        $randomArticle = $this->db->fetchRow(
            'SELECT * FROM s_articles_details detail
            INNER JOIN s_articles article
              ON article.id = detail.articleID
            WHERE detail.active = 1
            LIMIT 1'
        );
        $this->db->insert(
            's_order_basket',
            [
                'price' => 2,
                'quantity' => 1,
                'sessionID' => $this->session->get('sessionId'),
                'ordernumber' => $randomArticle['ordernumber'],
                'articleID' => $randomArticle['articleID'],
            ]
        );

        $keys = [
            'content',
            'Amount',
            'AmountNet',
            'Quantity',
            'AmountNumeric',
            'AmountNetNumeric',
            'AmountWithTax',
            'AmountWithTaxNumeric',
        ];

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
        static::assertEquals($keys, array_keys($result));
        static::assertGreaterThanOrEqual(1, count($result['content']));
        foreach ($contentKeys as $key) {
            static::assertArrayHasKey($key, $result['content'][0]);
        }

        static::assertGreaterThanOrEqual(1, count($result['content']));
        static::assertGreaterThanOrEqual(2, $result['Amount']);
        static::assertGreaterThanOrEqual(2, $result['AmountNet']);
        static::assertGreaterThanOrEqual(2, $result['AmountNumeric']);
        static::assertGreaterThanOrEqual(2, $result['AmountNetNumeric']);
        static::assertEquals(1, $result['Quantity']);
    }

    public function testsGetBasketDataHasNumericCartItemAmounts()
    {
        $resourceHelper = new \Shopware\Tests\Functional\Bundle\StoreFrontBundle\Helper();
        try {
            $article = $resourceHelper->createArticle([
                'name' => 'Testartikel',
                'description' => 'Test description',
                'active' => true,
                'mainDetail' => [
                    'number' => 'swTEST' . uniqid(rand(), true),
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
            $customer = $this->createDummyCustomer();
            $this->session['sUserId'] = $customer->getId();
            $this->module->sSYSTEM->sSESSION_ID = uniqid(rand(), true);
            $this->session->offsetSet('sessionId', $this->module->sSYSTEM->sSESSION_ID);
            $this->module->sSYSTEM->sUSERGROUPDATA['id'] = $customerGroup->getId();

            // Add the article to the basket
            $this->module->sAddArticle($article->getMainDetail()->getNumber(), 2);
            $this->module->sRefreshBasket();
            $basketData = $this->module->sGetBasketData();

            // Assert that a valid basket was returned
            static::assertNotEmpty($basketData);
            // Assert that there is a numeric basket amount
            static::assertArrayHasKey('amountNumeric', $basketData['content'][0], 'amountNumeric for cart item should exist');
            static::assertArrayHasKey('amountnetNumeric', $basketData['content'][0], 'amountnetNumeric for cart item should exist');
            static::assertGreaterThan(0, $basketData['content'][0]['amountNumeric']);
            static::assertGreaterThan(0, $basketData['content'][0]['amountnetNumeric']);
            static::assertEquals(29.97 * 2, $basketData['content'][0]['amountNumeric'], 'amountNumeric for cart item should respect cart item quantity');
            static::assertEqualsWithDelta(29.97 * 2, $basketData['content'][0]['amountNumeric'], 0.001, 'amountNumeric for cart item should respect cart item quantity');
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
     * Article                   29.97
     * Shipping discount         -2.80
     * Customer group discount  -27.17 = 90.65 % of the item total
     * ------------------------------
     * Total (double arithmetic) -0.0000000000000035527136788005
     * Total (real world)         0.00
     *
     * @covers \sBasket::sGetBasketData()
     */
    public function testsGetBasketDataNegativeCloseToZeroTotal()
    {
        $resourceHelper = new \Shopware\Tests\Functional\Bundle\StoreFrontBundle\Helper();
        try {
            // Setup article for the first basket position - an article that costs EUR 29.97
            $article = $resourceHelper->createArticle([
                'name' => 'Testartikel',
                'description' => 'Test description',
                'active' => true,
                'mainDetail' => [
                    'number' => 'swTEST' . uniqid(rand(), true),
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
            $dispatchDiscountId = $this->db->fetchCol(
                'SELECT * FROM s_premium_dispatch WHERE type = 3'
            );
            $this->db->update(
                's_premium_shippingcosts',
                ['value' => 2.8],
                ['dispatchID' => $dispatchDiscountId]
            );
            // Setup discount for the third basket position - a basket discount covering the remainder of the basket (-27.17)
            $customerGroup = $resourceHelper->createCustomerGroup();
            $this->db->insert(
                's_core_customergroups_discounts',
                [
                    'groupID' => $customerGroup->getId(),
                    // discount by the full remaining value of the basket - EUR 27.17 / EUR 29.97 = 90.65 %
                    'basketdiscount' => 90.65,
                    'basketdiscountstart' => 10,
                ]
            );
            $customerGroupDiscountId = $this->db->lastInsertId('s_core_customergroups_discounts');
            // Setup the user and their session
            $customer = $this->createDummyCustomer();
            $this->db->update(
                's_user',
                ['customergroup' => $customerGroup->getKey()],
                ['id = ?' => $customer->getId()]
            );
            $this->session['sUserId'] = $customer->getId();
            $this->module->sSYSTEM->sSESSION_ID = uniqid(rand(), true);
            $this->session->offsetSet('sessionId', $this->module->sSYSTEM->sSESSION_ID);
            $this->module->sSYSTEM->sUSERGROUPDATA['id'] = $customerGroup->getId();

            // Actually add the article to the basket
            $this->module->sAddArticle($article->getMainDetail()->getNumber(), 1);
            // Run sBasket::sRefreshBasket() in order to add the discounts to the basket
            $this->module->sRefreshBasket();
            // Run sGetBasketData() to show the rounding error aborting the computation
            $basketData = $this->module->sGetBasketData();
            // Run sGetAmount() to show that this function is affected by the issue as well
            $amount = $this->module->sGetAmount();

            // Assert that a valid basket was returned
            static::assertNotEmpty($basketData);
            // Assert that the total is approximately 0.00
            static::assertEquals(0, $basketData['AmountNumeric'], 'total is approxmately 0.00');
            static::assertEqualsWithDelta(0, $basketData['AmountNumeric'], 0.0001, 'total is approxmately 0.00');
        } finally {
            // Delete test resources
            if ($customerGroupDiscountId) {
                $this->db->delete('s_core_customergroups_discounts', ['id' => $customerGroupDiscountId]);
            }
            $resourceHelper->cleanUp();
        }
    }

    public function testsGetBasketWithInvalidProduct()
    {
        static::assertEquals([], $this->module->sGetBasket());

        $this->module->sSYSTEM->sSESSION_ID = uniqid(rand(), true);
        $this->session->offsetSet('sessionId', $this->module->sSYSTEM->sSESSION_ID);

        $resourceHelper = new \Shopware\Tests\Functional\Bundle\StoreFrontBundle\Helper();

        // Setup article for the first basket position - an article that costs EUR 29.97
        $product = $resourceHelper->createArticle([
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

        $this->db->insert(
            's_order_basket',
            [
                'price' => 2,
                'quantity' => 1,
                'sessionID' => $this->session->get('sessionId'),
                'ordernumber' => $product->getMainDetail()->getNumber(),
                'articleID' => $product->getId(),
            ]
        );

        static::assertEquals(1, count($this->module->sGetBasket()['content']));

        $this->db->delete('s_articles_details', ['articleID = ?' => $product->getId()]);
        $this->db->delete('s_articles', ['id = ?' => $product->getId()]);

        static::assertEquals([], $this->module->sGetBasket());
    }

    /**
     * @covers \sBasket::sAddNote
     */
    public function testsAddNote()
    {
        $_COOKIE['sUniqueID'] = Random::getAlphanumericString(32);

        // Add one article to the basket with low amount
        $randomArticle = $this->db->fetchRow(
            'SELECT detail.id, detail.articleID, article.name, detail.ordernumber
            FROM s_articles_details detail
            INNER JOIN s_articles article
              ON article.id = detail.articleID
            WHERE detail.active = 1 and article.active = 1
            AND ordernumber IS NOT NULL
            AND article.supplierID IS NOT NULL
            AND article.name IS NOT NULL
            LIMIT 1'
        );

        static::assertEquals(0, $this->db->fetchOne(
            'SELECT COUNT(DISTINCT id) FROM s_order_notes WHERE sUniqueID = ? AND ordernumber = ?',
            [$this->module->sSYSTEM->_COOKIE['sUniqueID'], $randomArticle['ordernumber']]
        ));

        static::assertTrue($this->module->sAddNote(
            $randomArticle['articleID'],
            $randomArticle['name'],
            $randomArticle['ordernumber']
        ));

        static::assertEquals(1, $this->db->fetchOne(
            'SELECT COUNT(DISTINCT id) FROM s_order_notes WHERE sUniqueID = ? AND ordernumber = ?',
            [$this->module->sSYSTEM->_COOKIE['sUniqueID'], $randomArticle['ordernumber']]
        ));

        static::assertTrue($this->module->sAddNote(
            $randomArticle['articleID'],
            $randomArticle['name'],
            $randomArticle['ordernumber']
        ));

        return [$randomArticle, $_COOKIE['sUniqueID']];
    }

    /**
     * @covers \sBasket::sGetNotes
     * @depends testsAddNote
     */
    public function testsGetNotes($input)
    {
        list($randomArticle, $cookieId) = $input;

        // Test with no id in cookie
        static::assertEquals([], $this->module->sGetNotes());
        $_COOKIE['sUniqueID'] = $cookieId;

        $result = $this->module->sGetNotes();
        static::assertEquals($randomArticle['articleID'], $result[0]['articleID']);

        return [$randomArticle, $cookieId];
    }

    /**
     * @covers \sBasket::sCountNotes
     * @depends testsGetNotes
     */
    public function testsCountNotes($input)
    {
        list($randomArticleOne, $cookieId) = $input;

        // Test with no id in cookie
        $_COOKIE['sUniqueID'] = $cookieId;
        static::assertEquals(1, $this->module->sCountNotes());

        // Add another article to the basket
        $randomArticleTwo = $this->db->fetchRow(
            'SELECT detail.articleID, article.name, detail.ordernumber
            FROM s_articles_details detail
            INNER JOIN s_articles article
              ON article.id = detail.articleID
            WHERE detail.active = 1
            AND detail.id <> ?
            LIMIT 1',
            [$randomArticleOne['id']]
        );

        static::assertTrue($this->module->sAddNote(
            $randomArticleTwo['articleID'],
            $randomArticleTwo['name'],
            $randomArticleTwo['ordernumber']
        ));

        static::assertEquals(2, $this->module->sCountNotes());

        return [[$randomArticleOne, $randomArticleTwo], $cookieId];
    }

    /**
     * @covers \sBasket::sDeleteNote
     * @depends testsCountNotes
     */
    public function testsDeleteNote($input)
    {
        list($randomArticles, $cookieId) = $input;
        $_COOKIE['sUniqueID'] = $cookieId;

        // Null argument, return null
        static::assertFalse($this->module->sDeleteNote(null));

        // Get random article that's not in the basket
        $randomNotPresentArticleId = $this->db->fetchOne(
            'SELECT detail.id FROM s_articles_details detail
            INNER JOIN s_articles article
              ON article.id = detail.articleID
            WHERE detail.active = 1
            AND detail.id NOT IN (?)
            LIMIT 1',
            [array_column($randomArticles, 'id')]
        );

        // Check that we currently have 2 articles
        static::assertEquals(2, $this->module->sCountNotes());

        // Get true even if article is not in the wishlist
        static::assertTrue($this->module->sDeleteNote($randomNotPresentArticleId));

        // Check that we still have 2 articles
        static::assertEquals(2, $this->module->sCountNotes());

        $noteIds = $this->db->fetchCol(
            'SELECT id FROM s_order_notes detail
            WHERE sUniqueID = ?',
            [$this->module->sSYSTEM->_COOKIE['sUniqueID']]
        );

        // Get true even if article is not in the wishlist
        static::assertTrue($this->module->sDeleteNote($noteIds[0]));

        // Check that we now have 1 article
        static::assertEquals(1, $this->module->sCountNotes());

        // Get true even if article is not in the wishlist
        static::assertTrue($this->module->sDeleteNote($noteIds[1]));

        // Check that we now have an empty wishlist
        static::assertEquals(0, $this->module->sCountNotes());
    }

    /**
     * @covers \sBasket::sUpdateArticle
     */
    public function testsUpdateArticle()
    {
        // Null args, false result
        static::assertFalse($this->module->sUpdateArticle(null, null));

        $this->generateBasketSession();

        // Get random article
        $randomArticle = $this->db->fetchRow(
            'SELECT detail.articleID, detail.ordernumber
            FROM s_articles_details detail
            INNER JOIN s_articles article
              ON article.id = detail.articleID
            WHERE detail.active = 1
            LIMIT 1'
        );
        $this->db->insert(
            's_order_basket',
            [
                'price' => 0.01,
                'quantity' => 1,
                'sessionID' => $this->session->get('sessionId'),
                'ordernumber' => $randomArticle['ordernumber'],
                'articleID' => $randomArticle['articleID'],
            ]
        );
        $basketId = $this->db->lastInsertId();

        // Store previous amount
        $previousAmount = $this->module->sGetAmount();
        static::assertEquals(['totalAmount' => 0.01], $previousAmount);

        // Update the article, prices are recalculated
        static::assertNull($this->module->sUpdateArticle($basketId, 1));
        $oneAmount = $this->module->sGetAmount();
        static::assertGreaterThan($previousAmount['totalAmount'], $oneAmount['totalAmount']);

        // Update from 1 to 2, we should get a more expensive cart
        static::assertNull($this->module->sUpdateArticle($basketId, 2));
        $twoAmount = $this->module->sGetAmount();
        static::assertGreaterThanOrEqual($oneAmount['totalAmount'], $twoAmount['totalAmount']);
        static::assertLessThanOrEqual(2 * $oneAmount['totalAmount'], $twoAmount['totalAmount']);

        // Housekeeping
        $this->db->delete(
            's_order_basket',
            ['sessionID = ?' => $this->session->get('sessionId')]
        );
    }

    /**
     * @covers \sBasket::sCheckForESD
     */
    public function testsCheckForESD()
    {
        // No session, expect false
        static::assertFalse($this->module->sCheckForESD());

        $this->module->sSYSTEM->sSESSION_ID = uniqid(rand(), true);
        $this->session->offsetSet('sessionId', $this->module->sSYSTEM->sSESSION_ID);

        // Get random non-esd article and add it to the basket
        $randomNoESDArticle = $this->db->fetchRow('
            SELECT detail.ordernumber
            FROM s_articles_details detail
            INNER JOIN s_articles article
              ON article.id = detail.articleID
            LEFT JOIN s_articles_esd esd
              ON esd.articledetailsID = detail.id
            WHERE detail.active = 1
            AND esd.id IS NULL LIMIT 1
        ');

        static::assertGreaterThan(0, $this->module->sAddArticle($randomNoESDArticle['ordernumber'], 1));

        static::assertFalse($this->module->sCheckForESD());

        // Get random esd article
        $randomESDArticle = $this->db->fetchRow(
            'SELECT detail.* FROM s_articles_details detail
            INNER JOIN s_articles article
              ON article.id = detail.articleID
            INNER JOIN s_articles_esd esd
              ON esd.articledetailsID = detail.id
            WHERE esd.id IS NOT NULL
            LIMIT 1'
        );
        $this->db->update(
            's_articles_details',
            ['active' => 1],
            ['id = ?' => $randomESDArticle['id']]
        );
        $this->db->update(
            's_articles',
            ['active' => 1],
            ['id = ?' => $randomESDArticle['articleID']]
        );
        $this->module->sAddArticle($randomESDArticle['ordernumber'], 1);

        static::assertTrue($this->module->sCheckForESD());

        // Housekeeping
        $this->db->delete(
            's_order_basket',
            ['sessionID = ?' => $this->session->get('sessionId')]
        );
        $this->db->update(
            's_articles_details',
            ['active' => 0],
            ['id = ?' => $randomESDArticle['id']]
        );
        $this->db->update(
            's_articles',
            ['active' => 0],
            ['id = ?' => $randomESDArticle['articleID']]
        );
    }

    /**
     * @covers \sBasket::sDeleteBasket
     */
    public function testsDeleteBasket()
    {
        // No session, expect false
        static::assertFalse($this->module->sDeleteBasket());

        $this->module->sSYSTEM->sSESSION_ID = uniqid(rand(), true);
        $this->session->offsetSet('sessionId', $this->module->sSYSTEM->sSESSION_ID);

        static::assertNull($this->module->sDeleteBasket());

        // Get random article and add it to the basket
        $randomArticle = $this->db->fetchRow(
            'SELECT detail.* FROM s_articles_details detail
            INNER JOIN s_articles article
              ON article.id = detail.articleID
            LEFT JOIN s_articles_avoid_customergroups avoid
              ON avoid.articleID = article.id
            WHERE detail.active = 1
            AND avoid.articleID IS NULL
            AND article.id NOT IN (
              SELECT articleID
              FROM s_articles_avoid_customergroups
              WHERE customergroupID = 1
            )
            AND (detail.lastStock = 0 OR detail.instock > 0)
            LIMIT 1'
        );

        $this->module->sAddArticle($randomArticle['ordernumber'], 1);

        static::assertNotEquals(0, $this->module->sCountBasket());

        $this->module->sDeleteBasket();

        static::assertEquals(0, $this->module->sCountBasket());
    }

    /**
     * @covers \sBasket::sDeleteArticle
     */
    public function testsDeleteArticle()
    {
        // No id, expect null
        static::assertNull($this->module->sDeleteArticle(null));

        // Random id, expect null
        static::assertNull($this->module->sDeleteArticle(9999999));

        $this->module->sSYSTEM->sSESSION_ID = uniqid(rand(), true);
        $this->session->offsetSet('sessionId', $this->module->sSYSTEM->sSESSION_ID);

        // Get random article and add it to the basket
        $randomArticle = $this->db->fetchRow(
            'SELECT detail.* FROM s_articles_details detail
            LEFT JOIN s_articles article
              ON article.id = detail.articleID
            LEFT JOIN s_articles_avoid_customergroups avoid
              ON avoid.articleID = article.id
            WHERE detail.active = 1
            AND avoid.articleID IS NULL
            AND article.id NOT IN (
              SELECT articleID
              FROM s_articles_avoid_customergroups
              WHERE customergroupID = 1
            )
            AND (detail.lastStock = 0 OR detail.instock > 0)
            LIMIT 1'
        );
        $idOne = $this->module->sAddArticle($randomArticle['ordernumber'], 1);
        static::assertEquals(1, $this->module->sCountBasket());

        $this->module->sDeleteArticle($idOne);
        static::assertEquals(0, $this->module->sCountBasket());
    }

    /**
     * @covers \sBasket::sAddArticle
     */
    public function testsAddArticle()
    {
        // No id, expect false
        static::assertFalse($this->module->sAddArticle(null));

        $this->module->sSYSTEM->sSESSION_ID = uniqid(rand(), true);
        $this->session->offsetSet('sessionId', $this->module->sSYSTEM->sSESSION_ID);

        // Get random article with stock control and add it to the basket
        $randomArticleOne = $this->db->fetchRow(
            'SELECT detail.* FROM s_articles_details detail
            INNER JOIN s_articles article
              ON article.id = detail.articleID
            LEFT JOIN s_articles_avoid_customergroups avoid
              ON avoid.articleID = article.id
            WHERE detail.active = 1
            AND detail.lastStock = 1
            AND instock > 3
            AND avoid.articleID IS NULL
            AND article.id NOT IN (
              SELECT articleID
              FROM s_articles_avoid_customergroups
              WHERE customergroupID = 1
            )
            LIMIT 1'
        );

        // Adding article without quantity adds one
        $this->module->sAddArticle($randomArticleOne['ordernumber']);
        $basket = $this->module->sGetBasket();
        static::assertEquals(1, $basket['Quantity']);
        static::assertEquals(1, $basket['content'][0]['quantity']);

        // Adding article with quantity adds correctly, finds stacks
        $this->module->sAddArticle($randomArticleOne['ordernumber'], 2);
        $basket = $this->module->sGetBasket();
        static::assertEquals(1, $basket['Quantity']);
        static::assertEquals(3, $basket['content'][0]['quantity']);

        // Start over
        $this->module->sDeleteBasket();

        // Adding article with quantity over stock, check that we have the available stock
        $this->module->sAddArticle($randomArticleOne['ordernumber'], $randomArticleOne['instock'] + 200);
        $basket = $this->module->sGetBasket();
        static::assertEquals(1, $basket['Quantity']);
        static::assertEquals(min($randomArticleOne['instock'], 100), $basket['content'][0]['quantity']);

        // Start over
        $this->module->sDeleteBasket();

        // Get random article and add it to the basket
        $randomArticleTwo = $this->db->fetchRow(
            'SELECT detail.* FROM s_articles_details detail
            INNER JOIN s_articles article
              ON article.id = detail.articleID
            WHERE detail.active = 1
            AND detail.laststock = 0
            AND detail.instock > 20
            AND detail.instock < 70
            AND article.id NOT IN (
              SELECT articleID
              FROM s_articles_avoid_customergroups
              WHERE customergroupID = 1
            )
            LIMIT 1'
        );

        // Adding article with quantity over stock, check that we have the desired quantity
        $this->module->sAddArticle($randomArticleTwo['ordernumber'], $randomArticleTwo['instock'] + 20);
        $basket = $this->module->sGetBasket();
        static::assertEquals(1, $basket['Quantity']);
        static::assertEquals(min($randomArticleTwo['instock'] + 20, 100), $basket['content'][0]['quantity']);

        // Housekeeping
        $this->db->delete(
            's_order_basket',
            ['sessionID = ?' => $this->session->get('sessionId')]
        );
    }

    public function testsPriceCalculationTaxfreeWithPriceGroupDiscount()
    {
        $resourceHelper = new \Shopware\Tests\Functional\Bundle\StoreFrontBundle\Helper();

        // Create pricegroup
        $priceGroup = $resourceHelper->createPriceGroup([
            [
                'key' => 'EK',
                'quantity' => 1,
                'discount' => 15,
            ],
        ]);

        // Create test article
        $article = $resourceHelper->createArticle([
            'name' => 'Testartikel',
            'description' => 'Test description',
            'active' => true,
            'mainDetail' => [
                'number' => 'swTEST' . uniqid(rand(), true),
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
        $customerGroupData = Shopware()->Db()->fetchRow(
            'SELECT * FROM s_core_customergroups WHERE groupkey = :key',
            [':key' => 'EK']
        );
        $customerGroupData['tax'] = 0;
        $this->module->sSYSTEM->sUSERGROUPDATA = $customerGroupData;
        Shopware()->Session()->sUserGroupData = $customerGroupData;

        // Setup session
        $this->module->sSYSTEM->sSESSION_ID = uniqid(rand(), true);
        $this->session->offsetSet('sessionId', $this->module->sSYSTEM->sSESSION_ID);

        $basketItemId = $this->module->sAddArticle($article->getMainDetail()->getNumber(), 1);

        // Check that the article has been added to the basket
        static::assertNotEquals(false, $basketItemId);

        // Check that the final price equals the net price for the basket item
        $basketItem = Shopware()->Db()->fetchRow(
            'SELECT * FROM s_order_basket WHERE id = :id',
            [':id' => $basketItemId]
        );
        static::assertEquals($basketItem['price'], $basketItem['netprice']);

        // Check that the final price equals the net price for the whole basket
        $basketData = $this->module->sGetBasketData();
        static::assertEquals($basketData['AmountNumeric'], $basketData['AmountNetNumeric']);

        // Delete test resources
        $resourceHelper->cleanUp();
    }

    private function generateBasketSession()
    {
        // Create session id
        $sessionId = Random::getAlphanumericString(32);
        $this->module->sSYSTEM->sSESSION_ID = $sessionId;
        $this->session->offsetSet('sessionId', $sessionId);

        return $sessionId;
    }

    /**
     * Create dummy customer entity
     *
     * @return \Shopware\Models\Customer\Customer
     */
    private function createDummyCustomer()
    {
        $date = new DateTime();
        $date->modify('-8 days');
        $lastLogin = $date->format(DateTime::ATOM);

        $birthday = DateTime::createFromFormat('Y-m-d', '1986-12-20')->format(DateTime::ATOM);

        $testData = [
            'password' => 'fooobar',
            'email' => uniqid(rand(), true) . 'test@foobar.com',

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

        $customerResource = new \Shopware\Components\Api\Resource\Customer();
        $customerResource->setManager(Shopware()->Models());

        return $customerResource->create($testData);
    }

    /**
     * Deletes all dummy customer entity
     */
    private function deleteDummyCustomer(\Shopware\Models\Customer\Customer $customer)
    {
        $this->db->delete('s_user_addresses', 'user_id = ' . $customer->getId());
        $this->db->delete('s_core_payment_data', 'user_id = ' . $customer->getId());
        $this->db->delete('s_user_attributes', 'userID = ' . $customer->getId());
        $this->db->delete('s_user', 'id = ' . $customer->getId());
    }

    /**
     * Call protected/private method of a class.
     *
     * @param object &$object    Instantiated object that we will run method on
     * @param string $methodName Method name to call
     * @param array  $parameters array of parameters to pass into method
     *
     * @return mixed method return
     */
    private function invokeMethod(&$object, $methodName, array $parameters = [])
    {
        $reflection = new \ReflectionClass(get_class($object));
        $method = $reflection->getMethod($methodName);
        $method->setAccessible(true);

        return $method->invokeArgs($object, $parameters);
    }
}
