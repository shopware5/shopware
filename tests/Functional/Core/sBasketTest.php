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

    public function setUp()
    {
        parent::setUp();

        Shopware()->Front()->setRequest(new Enlight_Controller_Request_RequestHttp());

        $this->snippetManager = Shopware()->Snippets();
        $this->db = Shopware()->Db();
        $this->module = Shopware()->Modules()->Basket();
        $this->session = Shopware()->Session();
        $this->session->offsetSet('sessionId', null);
        $this->module->sSYSTEM->_POST = array();
        $this->module->sSYSTEM->_GET = array();
        $this->config = Shopware()->Config();
        $this->module->sSYSTEM->sCONFIG = &$this->config;
        $this->module->sSYSTEM->sCurrency = Shopware()->Db()->fetchRow('SELECT * FROM s_core_currencies WHERE currency LIKE "EUR"');
        $this->module->sSYSTEM->sSESSION_ID = null;
    }

    /**
     * @covers sBasket::sGetAmount
     */
    public function testsGetAmount()
    {
        // Test with empty session, expect empty array
        $this->assertEquals(array(), $this->module->sGetAmount());
        $this->module->sSYSTEM->sSESSION_ID = uniqid(rand());
        $this->session->offsetSet('sessionId', $this->module->sSYSTEM->sSESSION_ID);

        $this->db->insert(
            's_order_basket',
            array(
                'price' => 123,
                'quantity' => 2,
                'sessionID' => $this->session->get('sessionId')
            )
        );

        $this->assertEquals(
            array('totalAmount' => 246),
            $this->module->sGetAmount()
        );

        $this->db->delete(
            's_order_basket',
            array('sessionID = ?' => $this->session->get('sessionId'))
        );
    }

    private function generateBasketSession()
    {
        // Create session id
        $sessionId = md5(uniqid('sCheckBasket', true));
        $this->module->sSYSTEM->sSESSION_ID = $sessionId;
        $this->session->offsetSet('sessionId', $sessionId);

        return $sessionId;
    }

    /**
     * @covers sBasket::sCheckBasketQuantities
     */
    public function testsCheckBasketQuantitiesWithEmptySession()
    {
        $this->generateBasketSession();

        // Test with empty session, expect empty array
        $this->assertEquals(
            array('hideBasket' => false, 'articles' => null),
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
            AND article.laststock = 1
            LIMIT 1'
        );

        $this->db->insert(
            's_order_basket',
            array(
                'price' => 123,
                'quantity' => $inStockArticle['instock'] - 1,
                'sessionID' => $this->session->get('sessionId'),
                'ordernumber' => $inStockArticle['ordernumber'],
                'articleID' => $inStockArticle['articleID']
            )
        );

        $result = $this->module->sCheckBasketQuantities();
        $this->assertInternalType('array', $result);
        $this->assertArrayHasKey('hideBasket', $result);
        $this->assertArrayHasKey('articles', $result);
        $this->assertFalse($result['hideBasket']);
        $this->assertArrayHasKey($inStockArticle['ordernumber'], $result['articles']);
        $this->assertFalse($result['articles'][$inStockArticle['ordernumber']]['OutOfStock']);
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
            AND article.laststock = 1
            AND article.active = 1
            LIMIT 1'
        );

        $this->db->insert(
            's_order_basket',
            array(
                'price' => 123,
                'quantity' => $outStockArticle['instock'] + 1,
                'sessionID' => $this->session->get('sessionId'),
                'ordernumber' => $outStockArticle['ordernumber'],
                'articleID' => $outStockArticle['articleID']
            )
        );

        $inStockArticle = $this->db->fetchRow(
            'SELECT * FROM s_articles_details detail
            INNER JOIN s_articles article
              ON article.id = detail.articleID
            WHERE detail.instock > 5
            AND detail.active = 1
            AND article.laststock = 1
            AND article.active = 1
            AND article.id != "'.$outStockArticle['articleID'].'"
            LIMIT 1'
        );

        $this->db->insert(
            's_order_basket',
            array(
                'price' => 123,
                'quantity' => $inStockArticle['instock'] - 1,
                'sessionID' => $this->session->get('sessionId'),
                'ordernumber' => $inStockArticle['ordernumber'],
                'articleID' => $inStockArticle['articleID']
            )
        );

        $result = $this->module->sCheckBasketQuantities();
        $this->assertInternalType('array', $result);
        $this->assertArrayHasKey('hideBasket', $result);
        $this->assertArrayHasKey('articles', $result);
        $this->assertTrue($result['hideBasket']);
        $this->assertArrayHasKey($inStockArticle['ordernumber'], $result['articles']);
        $this->assertFalse($result['articles'][$inStockArticle['ordernumber']]['OutOfStock']);
        $this->assertArrayHasKey($outStockArticle['ordernumber'], $result['articles']);
        $this->assertTrue($result['articles'][$outStockArticle['ordernumber']]['OutOfStock']);

        // Clear the current cart
        $this->db->delete(
            's_order_basket',
            array('sessionID = ?' => $this->session->get('sessionId'))
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
            AND article.laststock = 0
            LIMIT 1'
        );

        $this->db->insert(
            's_order_basket',
            array(
                'price' => 123,
                'quantity' => $ignoreStockArticle['instock']+1,
                'sessionID' => $this->session->get('sessionId'),
                'ordernumber' => $ignoreStockArticle['ordernumber'],
                'articleID' => $ignoreStockArticle['articleID']
            )
        );

        $result = $this->module->sCheckBasketQuantities();
        $this->assertInternalType('array', $result);
        $this->assertArrayHasKey('hideBasket', $result);
        $this->assertArrayHasKey('articles', $result);
        $this->assertFalse($result['hideBasket']);
        $this->assertArrayHasKey($ignoreStockArticle['ordernumber'], $result['articles']);
        $this->assertFalse($result['articles'][$ignoreStockArticle['ordernumber']]['OutOfStock']);

        // Housekeeping
        $this->db->delete(
            's_order_basket',
            array('sessionID = ?' => $this->session->get('sessionId'))
        );
    }

    /**
     * @covers sBasket::sGetAmountRestrictedArticles
     */
    public function testsGetAmountRestrictedArticles()
    {
        // Null arguments, empty basket, expect empty array
        $this->assertEquals(
            array(),
            $this->invokeMethod(
                $this->module,
                'sGetAmountRestrictedArticles',
                array(null, null)
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
            array($randomArticleOne['supplierID'])
        );

        $this->db->insert(
            's_order_basket',
            array(
                'price' => 2,
                'quantity' => 1,
                'sessionID' => $this->session->get('sessionId'),
                'ordernumber' => $randomArticleOne['ordernumber'],
                'articleID' => $randomArticleOne['articleID'],
            )
        );
        $this->db->insert(
            's_order_basket',
            array(
                'price' => 3,
                'quantity' => 1,
                'sessionID' => $this->session->get('sessionId'),
                'ordernumber' => $randomArticleTwo['ordernumber'],
                'articleID' => $randomArticleTwo['articleID'],
            )
        );

        // No filters, expect total basket value
        $this->assertEquals(
            array('totalAmount' => 5),
            $this->invokeMethod(
                $this->module,
                'sGetAmountRestrictedArticles',
                array(null, null)
            )
        );

        // Filter by article one supplier, expect article one value
        $this->assertEquals(
            array('totalAmount' => 2),
            $this->invokeMethod(
                $this->module,
                'sGetAmountRestrictedArticles',
                array(null, $randomArticleOne['supplierID'])
            )
        );
        // Filter by article two supplier, expect article two value
        $this->assertEquals(
            array('totalAmount' => 3),
            $this->invokeMethod(
                $this->module,
                'sGetAmountRestrictedArticles',
                array(null, $randomArticleTwo['supplierID'])
            )
        );
        // Filter by other supplier, expect empty array
        $this->assertEquals(
            array(),
            $this->invokeMethod(
                $this->module,
                'sGetAmountRestrictedArticles',
                array(null, -1)
            )
        );

        // Filter by article one, expect article one value
        $this->assertEquals(
            array('totalAmount' => 2),
            $this->invokeMethod(
                $this->module,
                'sGetAmountRestrictedArticles',
                array(array($randomArticleOne['ordernumber']), null)
            )
        );
        // Filter by article two, expect article two value
        $this->assertEquals(
            array('totalAmount' => 3),
            $this->invokeMethod(
                $this->module,
                'sGetAmountRestrictedArticles',
                array(array($randomArticleTwo['ordernumber']), null)
            )
        );
        // Filter by both articles, expect total basket value
        $this->assertEquals(
            array('totalAmount' => 5),
            $this->invokeMethod(
                $this->module,
                'sGetAmountRestrictedArticles',
                array(
                    array($randomArticleOne['ordernumber'], $randomArticleTwo['ordernumber']),
                    null
                )
            )
        );
        // Filter by another article, expect empty value
        $this->assertEquals(
            array(),
            $this->invokeMethod(
                $this->module,
                'sGetAmountRestrictedArticles',
                array(
                    array(-1),
                    null
                )
            )
        );

        // Housekeeping
        $this->db->delete(
            's_order_basket',
            array('sessionID = ?' => $this->session->get('sessionId'))
        );
    }

    /**
     * @covers sBasket::sInsertPremium
     */
    public function testsInsertPremium()
    {
        // Test with empty session, expect true
        $this->assertTrue($this->module->sInsertPremium());

        // Create session id
        $this->module->sSYSTEM->sSESSION_ID = uniqid(rand());
        $this->session->offsetSet('sessionId', $this->module->sSYSTEM->sSESSION_ID);

        // Test with session, expect true
        $this->assertTrue($this->module->sInsertPremium());

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
            array(
                'price' => 1,
                'quantity' => 1,
                'sessionID' => $this->session->get('sessionId'),
                'ordernumber' => $normalArticle['ordernumber'],
                'articleID' => $normalArticle['articleID'],
                'modus' => 0
            )
        );
        $this->assertTrue($this->module->sInsertPremium());
        $this->assertEquals(
            1,
            $this->db->fetchOne(
                'SELECT count(*) FROM s_order_basket WHERE sessionID = ?',
                array($this->module->sSYSTEM->sSESSION_ID)
            )
        );

        // Add premium articles to basket
        // Test that calling sInsertPremium removes them
        $this->db->insert(
            's_order_basket',
            array(
                'price' => 1,
                'quantity' => 1,
                'sessionID' => $this->session->get('sessionId'),
                'ordernumber' => $premiumArticleOne['ordernumber'],
                'articleID' => $premiumArticleOne['id'],
                'modus' => 1
            )
        );
        $this->db->insert(
            's_order_basket',
            array(
                'price' => 1,
                'quantity' => 1,
                'sessionID' => $this->session->get('sessionId'),
                'ordernumber' => $premiumArticleTwo['ordernumber'],
                'articleID' => $premiumArticleTwo['id'],
                'modus' => 1
            )
        );
        $this->assertTrue($this->module->sInsertPremium());
        $this->assertEquals(
            1,
            $this->db->fetchOne(
                'SELECT count(*) FROM s_order_basket WHERE sessionID = ?',
                array($this->module->sSYSTEM->sSESSION_ID)
            )
        );

        // Add sAddPremium to _GET.
        // Basket price is 1, so expect premium articles to be denied
        $this->module->sSYSTEM->_GET['sAddPremium'] = $premiumArticleTwo['ordernumber'];
        $this->assertFalse($this->module->sInsertPremium());

        // Increase basket price and retry
        $this->db->insert(
            's_order_basket',
            array(
                'price' => 10000,
                'quantity' => 1,
                'sessionID' => $this->session->get('sessionId'),
                'ordernumber' => $normalArticle['ordernumber'],
                'articleID' => $normalArticle['articleID'],
                'modus' => 0
            )
        );
        // Will still get false due to cache
        $this->assertFalse($this->module->sInsertPremium());

        // Change the premium article to a non-premium, fail
        $this->module->sSYSTEM->_GET['sAddPremium'] = $premiumArticleOne['ordernumber'];
        $this->assertFalse($this->module->sInsertPremium());
        $this->assertEquals(
            2,
            $this->db->fetchOne(
                'SELECT count(*) FROM s_order_basket WHERE sessionID = ?',
                array($this->module->sSYSTEM->sSESSION_ID)
            )
        );

        // Change the premium article to a premium, succeed
        $this->module->sSYSTEM->_GET['sAddPremium'] = $premiumArticleTwo['ordernumber'];
        $this->assertGreaterThan(0, $this->module->sInsertPremium());
        $this->assertEquals(
            3,
            $this->db->fetchOne(
                'SELECT count(*) FROM s_order_basket WHERE sessionID = ?',
                array($this->module->sSYSTEM->sSESSION_ID)
            )
        );

        // Housekeeping
        $this->db->delete(
            's_order_basket',
            array('sessionID = ?' => $this->session->get('sessionId'))
        );
    }

    /**
     * @covers sBasket::getMaxTax
     */
    public function testGetMaxTax()
    {
        // Test with empty session, expect false
        $this->assertFalse($this->module->getMaxTax());

        // Create session id
        $this->module->sSYSTEM->sSESSION_ID = uniqid(rand());
        $this->session->offsetSet('sessionId', $this->module->sSYSTEM->sSESSION_ID);

        // Test with session and empty basket, expect false
        $this->assertFalse($this->module->getMaxTax());

        $randomArticle = $this->db->fetchRow(
            'SELECT * FROM s_articles_details detail
            INNER JOIN s_articles article
              ON article.id = detail.articleID
            WHERE detail.active = 1
            LIMIT 1'
        );

        $randOne = rand(1, 100);
        $randTwo = rand(1, 100);

        // Add one article, check that he is the new maximum
        $this->db->insert(
            's_order_basket',
            array(
                'price' => 100,
                'quantity' => 1,
                'sessionID' => $this->session->get('sessionId'),
                'ordernumber' => $randomArticle['ordernumber'],
                'articleID' => $randomArticle['articleID'],
                'tax_rate' => $randOne
            )
        );
        $this->assertEquals($randOne, $this->module->getMaxTax());

        // Add another article, check that we get the max of the two
        $this->db->insert(
            's_order_basket',
            array(
                'price' => 100,
                'quantity' => 1,
                'sessionID' => $this->session->get('sessionId'),
                'ordernumber' => $randomArticle['ordernumber'],
                'articleID' => $randomArticle['articleID'],
                'tax_rate' => $randTwo
            )
        );
        $this->assertEquals(max($randOne, $randTwo), $this->module->getMaxTax());

        // Housekeeping
        $this->db->delete(
            's_order_basket',
            array('sessionID = ?' => $this->session->get('sessionId'))
        );
    }

    /**
     * @covers sBasket::sAddVoucher
     */
    public function testsAddVoucherWithAbsoluteVoucher()
    {
        // Test with empty args and session, expect failure
        $result = $this->module->sAddVoucher('');
        $this->assertInternalType('array', $result);
        $this->assertArrayHasKey('sErrorFlag', $result);
        $this->assertArrayHasKey('sErrorMessages', $result);
        $this->assertTrue($result['sErrorFlag']);
        $this->assertContains(
            $this->snippetManager->getNamespace('frontend/basket/internalMessages')
                ->get('VoucherFailureNotFound', 'Voucher could not be found or is not valid anymore'),
            $result['sErrorMessages']
        );

        // Create session id and try again, same results
        $this->module->sSYSTEM->sSESSION_ID = uniqid(rand());
        $this->session->offsetSet('sessionId', $this->module->sSYSTEM->sSESSION_ID);
        $result = $this->module->sAddVoucher('');
        $this->assertInternalType('array', $result);
        $this->assertArrayHasKey('sErrorFlag', $result);
        $this->assertArrayHasKey('sErrorMessages', $result);
        $this->assertTrue($result['sErrorFlag']);
        $this->assertContains(
            $this->snippetManager->getNamespace('frontend/basket/internalMessages')
                ->get('VoucherFailureNotFound', 'Voucher could not be found or is not valid anymore'),
            $result['sErrorMessages']
        );

        $voucherData = array(
            'vouchercode' => 'testOne',
            'description' => 'testOne description',
            'numberofunits' => 1,
            'value' => 10,
            'minimumcharge' => 10,
            'ordercode' => uniqid(rand()),
            'modus' => 0
        );
        // Try with valid voucher code, empty basket
        $this->db->insert(
            's_emarketing_vouchers',
            $voucherData
        );
        $this->module->sSYSTEM->sSESSION_ID = uniqid(rand());
        $this->session->offsetSet('sessionId', $this->module->sSYSTEM->sSESSION_ID);
        $result = $this->module->sAddVoucher('testOne');
        $this->assertInternalType('array', $result);
        $this->assertArrayHasKey('sErrorFlag', $result);
        $this->assertArrayHasKey('sErrorMessages', $result);
        $this->assertTrue($result['sErrorFlag']);
        $this->assertContains(
            str_replace(
                "{sMinimumCharge}",
                $voucherData["minimumcharge"],
                $this->snippetManager->getNamespace('frontend/basket/internalMessages')->get(
                    'VoucherFailureMinimumCharge',
                    'The minimum charge for this voucher is {sMinimumCharge}'
                )
            ),
            $result['sErrorMessages']
        );

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
            array(
                'price' => $voucherData["minimumcharge"]+1,
                'quantity' => 1,
                'sessionID' => $this->session->get('sessionId'),
                'ordernumber' => $randomArticle['ordernumber'],
                'articleID' => $randomArticle['articleID'],
            )
        );

        // Add voucher to the orders table, so we can test the usage limit
        $this->db->insert('s_order_details',
            array(
                'articleordernumber' => $voucherData['ordercode']
            )
        );
        $result = $this->module->sAddVoucher('testOne');
        $this->assertInternalType('array', $result);
        $this->assertArrayHasKey('sErrorFlag', $result);
        $this->assertArrayHasKey('sErrorMessages', $result);
        $this->assertTrue($result['sErrorFlag']);
        $this->assertContains(
            $this->snippetManager->getNamespace('frontend/basket/internalMessages')->get(
                'VoucherFailureNotFound', 'Voucher could not be found or is not valid anymore'
            ),
            $result['sErrorMessages']
        );
        $this->db->delete('s_order_details',
            array(
                'articleordernumber = ?' => $voucherData['ordercode']
            )
        );

        $previousAmount = $this->module->sGetAmount();
        // Voucher should work ok now
        $this->assertTrue($this->module->sAddVoucher('testOne'));
        $this->assertLessThan($previousAmount, $this->module->sGetAmount());

        // Test the voucher values with tax from user group
        $discount = $this->db->fetchRow(
            'SELECT * FROM s_order_basket WHERE modus = 2 and sessionID = ?',
            array($this->module->sSYSTEM->sSESSION_ID)
        );
        $this->assertEquals($voucherData['value']*-1, $discount['price']);
        $this->assertEquals($this->config->offsetGet('sVOUCHERTAX'), $discount['tax_rate']);
        $this->assertEquals($voucherData['value']*-1, round($discount['netprice']*(100+$discount['tax_rate'])/100));

        // Second voucher should fail
        $result = $this->module->sAddVoucher('testOne');
        $this->assertInternalType('array', $result);
        $this->assertArrayHasKey('sErrorFlag', $result);
        $this->assertArrayHasKey('sErrorMessages', $result);
        $this->assertTrue($result['sErrorFlag']);
        $this->assertContains(
            $this->snippetManager->getNamespace('frontend/basket/internalMessages')->get(
                'VoucherFailureOnlyOnes', 'Only one voucher can be processed in order'
            ),
            $result['sErrorMessages']
        );

        // Housekeeping
        $this->db->delete(
            's_order_basket',
            array('sessionID = ?' => $this->session->get('sessionId'))
        );
        $this->db->delete(
            's_emarketing_vouchers',
            array('vouchercode = ?' => 'testOne')
        );
    }

    /**
     * @covers sBasket::calculateVoucherValues
     */
    public function testsAddVoucherWithPercentageVoucher()
    {
        // Create percentage voucher
        $voucherData = array(
            'vouchercode' => 'testOne',
            'description' => 'testOne description',
            'numberofunits' => 1,
            'value' => 10,
            'minimumcharge' => 10,
            'ordercode' => uniqid(rand()),
            'modus' => 0,
            'percental' => 1
        );
        $this->db->insert(
            's_emarketing_vouchers',
            $voucherData
        );

        // Setup session
        $this->module->sSYSTEM->sSESSION_ID = uniqid(rand());
        $this->session->offsetSet('sessionId', $this->module->sSYSTEM->sSESSION_ID);

        // Add one article to the basket with specified value
        $randomArticle = $this->db->fetchRow(
            'SELECT * FROM s_articles_details detail
            INNER JOIN s_articles article
              ON article.id = detail.articleID
            WHERE detail.active = 1
            ORDER BY RAND() LIMIT 1'
        );
        $this->db->insert(
            's_order_basket',
            array(
                'price' => 99.99,
                'quantity' => 1,
                'sessionID' => $this->session->get('sessionId'),
                'ordernumber' => $randomArticle['ordernumber'],
                'articleID' => $randomArticle['articleID'],
            )
        );

        // Add voucher to basket
        $this->assertTrue($this->module->sAddVoucher('testOne'));

        // Retrieve voucher values from basket
        $discount = $this->db->fetchRow(
            'SELECT * FROM s_order_basket WHERE modus = 2 and sessionID = ?',
            array($this->module->sSYSTEM->sSESSION_ID)
        );

        $this->assertEquals(-9.999, $discount['price'], '', 0.00001);
        // Test that more than 3 decimal places are present for net prices
        $this->assertEquals(-8.4025210084034, $discount['netprice'], '', 0.00001);

        // Housekeeping
        $this->db->delete(
            's_order_basket',
            array('sessionID = ?' => $this->session->get('sessionId'))
        );
        $this->db->delete(
            's_emarketing_vouchers',
            array('vouchercode = ?' => 'testOne')
        );
    }

    /**
     * @covers sBasket::sAddVoucher
     */
    public function testsAddVoucherWithLimitedVoucher()
    {
        $voucherData = array(
            'vouchercode' => 'testTwo',
            'description' => 'testTwo description',
            'numberofunits' => 10,
            'value' => 10,
            'minimumcharge' => 10,
            'ordercode' => uniqid(rand()),
            'modus' => 1,
            'taxconfig' => 'none'
        );
        $this->db->insert(
            's_emarketing_vouchers',
            $voucherData
        );
        $voucherId = $this->db->lastInsertId();

        $voucherCodeData = array(
            'voucherID' => $voucherId,
            'code' => uniqid(rand()),
            'userID' => null,
            'cashed' => 0
        );
        $this->db->insert(
            's_emarketing_voucher_codes',
            $voucherCodeData
        );

        $customer = $this->createDummyCustomer();
        $this->session['sUserId'] = $customer->getId();
        $this->module->sSYSTEM->sSESSION_ID = uniqid(rand());
        $this->session->offsetSet('sessionId', $this->module->sSYSTEM->sSESSION_ID);

        // Test with one-time code, fail due to minimum amount (cart is empty)
        $result = $this->module->sAddVoucher($voucherCodeData['code']);
        $this->assertInternalType('array', $result);
        $this->assertArrayHasKey('sErrorFlag', $result);
        $this->assertArrayHasKey('sErrorMessages', $result);
        $this->assertTrue($result['sErrorFlag']);
        $this->assertContains(
            str_replace(
                "{sMinimumCharge}",
                $voucherData["minimumcharge"],
                $this->snippetManager->getNamespace('frontend/basket/internalMessages')->get(
                    'VoucherFailureMinimumCharge',
                    'The minimum charge for this voucher is {sMinimumCharge}'
                )
            ),
            $result['sErrorMessages']
        );

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
            array(
                'price' => $voucherData["minimumcharge"]+1,
                'quantity' => 1,
                'sessionID' => $this->session->get('sessionId'),
                'ordernumber' => $randomArticle['ordernumber'],
                'articleID' => $randomArticle['articleID'],
            )
        );

        $previousAmount = $this->module->sGetAmount();
        // Test with one-time code, success
        $this->assertTrue($this->module->sAddVoucher($voucherCodeData['code']));
        $this->assertLessThan($previousAmount, $this->module->sGetAmount());

        // Test the voucher values. This voucher has no taxes
        $discount = $this->db->fetchRow(
            'SELECT * FROM s_order_basket WHERE modus = 2 and sessionID = ?',
            array($this->module->sSYSTEM->sSESSION_ID)
        );
        $this->assertEquals($voucherData['value']*-1, $discount['price']);
        $this->assertEquals($voucherData['value']*-1, $discount['netprice']);
        $this->assertEquals(0, $discount['tax_rate']);

        // Test again with the same one-time code, fail
        $result = $this->module->sAddVoucher($voucherCodeData['code']);
        $this->assertInternalType('array', $result);
        $this->assertArrayHasKey('sErrorFlag', $result);
        $this->assertArrayHasKey('sErrorMessages', $result);
        $this->assertTrue($result['sErrorFlag']);
        $this->assertContains(
            $this->snippetManager->getNamespace('frontend/basket/internalMessages')->get(
                'VoucherFailureOnlyOnes',
                'Only one voucher can be processed in order'
            ),
            $result['sErrorMessages']
        );

        // Housekeeping
        $this->db->delete(
            's_order_basket',
            array('sessionID = ?' => $this->session->get('sessionId'))
        );
        $this->db->delete(
            's_emarketing_vouchers',
            array('vouchercode = ?' => 'testOne')
        );
        $this->db->delete(
            's_emarketing_voucher_codes',
            array('code = ?' => $voucherCodeData['code'])
        );
        $this->deleteDummyCustomer($customer);
    }

    /**
     * @covers sBasket::sAddVoucher
     */
    public function testsAddVoucherWithSubShopVoucher()
    {
        $oldTaxValue = $this->module->sSYSTEM->sUSERGROUPDATA["tax"];
        $this->module->sSYSTEM->sUSERGROUPDATA["tax"] = null;

        $tax = $this->db->fetchRow('SELECT * FROM s_core_tax WHERE tax <> 19 LIMIT 1');

        $voucherData = array(
            'vouchercode' => 'testTwo',
            'description' => 'testTwo description',
            'numberofunits' => 1,
            'numorder' => 1,
            'value' => 10,
            'minimumcharge' => 10,
            'ordercode' => uniqid(rand()),
            'modus' => 0,
            'subshopID' => 3,
            'taxconfig' => $tax['id'],
        );
        $this->db->insert(
            's_emarketing_vouchers',
            $voucherData
        );

        $customer = $this->createDummyCustomer();
        $this->session['sUserId'] = $customer->getId();
        $this->module->sSYSTEM->sSESSION_ID = uniqid(rand());
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
            array(
                'price' => $voucherData["minimumcharge"]+1,
                'quantity' => 1,
                'sessionID' => $this->session->get('sessionId'),
                'ordernumber' => $randomArticle['ordernumber'],
                'articleID' => $randomArticle['articleID'],
            )
        );

        // Change current subshop id, test and expect success
        Shopware()->Container()->get('shopware_storefront.context_service')->getShopContext()->getShop()->setId(3);

        $previousAmount = $this->module->sGetAmount();
        // Test with one-time code, success
        $this->assertTrue($this->module->sAddVoucher($voucherData['vouchercode']));
        $this->assertLessThan($previousAmount, $this->module->sGetAmount());

        // Test the voucher values with custom tax from voucher
        $discount = $this->db->fetchRow(
            'SELECT * FROM s_order_basket WHERE modus = 2 and sessionID = ?',
            array($this->module->sSYSTEM->sSESSION_ID)
        );
        $this->assertEquals($voucherData['value']*-1, $discount['price']);
        $this->assertEquals($tax['tax'], $discount['tax_rate']);

        // Test again with the same one-time code, fail
        $result = $this->module->sAddVoucher($voucherData['vouchercode']);
        $this->assertInternalType('array', $result);
        $this->assertArrayHasKey('sErrorFlag', $result);
        $this->assertArrayHasKey('sErrorMessages', $result);
        $this->assertTrue($result['sErrorFlag']);
        $this->assertContains(
            $this->snippetManager->getNamespace('frontend/basket/internalMessages')->get(
                'VoucherFailureOnlyOnes',
                'Only one voucher can be processed in order'
            ),
            $result['sErrorMessages']
        );

        // Housekeeping
        $this->db->delete(
            's_order_basket',
            array('sessionID = ?' => $this->session->get('sessionId'))
        );
        $this->db->delete(
            's_emarketing_vouchers',
            array('vouchercode = ?' => $voucherData['vouchercode'])
        );
        $this->deleteDummyCustomer($customer);
        $this->module->sSYSTEM->sUSERGROUPDATA["tax"] = $oldTaxValue;
    }

    /**
     * @covers sBasket::sAddVoucher
     */
    public function testsAddVoucherWithMultipleVouchers()
    {
        $voucherOneData = array(
            'vouchercode' => 'testOne',
            'description' => 'testOne description',
            'numberofunits' => 1,
            'numorder' => 1,
            'value' => 10,
            'minimumcharge' => 10,
            'ordercode' => uniqid(rand()),
            'modus' => 0,
            'subshopID' => 3
        );
        $this->db->insert(
            's_emarketing_vouchers',
            $voucherOneData
        );

        $voucherTwoData = array(
            'vouchercode' => 'testTwo',
            'description' => 'testTwo description',
            'numberofunits' => 1,
            'numorder' => 1,
            'value' => 10,
            'minimumcharge' => 10,
            'ordercode' => uniqid(rand()),
            'modus' => 0,
            'subshopID' => 3
        );
        $this->db->insert(
            's_emarketing_vouchers',
            $voucherTwoData
        );

        $customer = $this->createDummyCustomer();
        $this->session['sUserId'] = $customer->getId();
        $this->module->sSYSTEM->sSESSION_ID = uniqid(rand());
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
            array(
                'price' => $voucherOneData["minimumcharge"]+1,
                'quantity' => 1,
                'sessionID' => $this->session->get('sessionId'),
                'ordernumber' => $randomArticle['ordernumber'],
                'articleID' => $randomArticle['articleID'],
            )
        );

        // Change current subshop, test and expect success
        Shopware()->Container()->get('shopware_storefront.context_service')->getShopContext()->getShop()->setId(3);

        $previousAmount = $this->module->sGetAmount();
        // Test with one-time code, success
        $this->assertTrue($this->module->sAddVoucher($voucherOneData['vouchercode']));
        $this->assertLessThan($previousAmount, $this->module->sGetAmount());

        // Test again with the same one-time code, fail
        $result = $this->module->sAddVoucher($voucherTwoData['vouchercode']);
        $this->assertInternalType('array', $result);
        $this->assertArrayHasKey('sErrorFlag', $result);
        $this->assertArrayHasKey('sErrorMessages', $result);
        $this->assertTrue($result['sErrorFlag']);
        $this->assertContains(
            $this->snippetManager->getNamespace('frontend/basket/internalMessages')->get(
                'VoucherFailureOnlyOnes',
                'Only one voucher can be processed in order'
            ),
            $result['sErrorMessages']
        );

        // Housekeeping
        $this->db->delete(
            's_order_basket',
            array('sessionID = ?' => $this->session->get('sessionId'))
        );
        $this->db->delete(
            's_emarketing_vouchers',
            array('vouchercode = ?' => $voucherOneData['vouchercode'])
        );
        $this->db->delete(
            's_emarketing_vouchers',
            array('vouchercode = ?' => $voucherTwoData['vouchercode'])
        );
        $this->deleteDummyCustomer($customer);
    }

    /**
     * @covers sBasket::sAddVoucher
     */
    public function testsAddVoucherWithCustomerGroup()
    {
        $randomCustomerGroup = $this->db->fetchAll(
            'SELECT * FROM s_core_customergroups
             LIMIT 2'
        );
        $voucherData = array(
            'vouchercode' => 'testTwo',
            'description' => 'testTwo description',
            'numberofunits' => 1,
            'numorder' => 1,
            'value' => 10,
            'minimumcharge' => 10,
            'ordercode' => uniqid(rand()),
            'modus' => 0,
            'customergroup' => $randomCustomerGroup[0]['id']
        );
        // Try with valid voucher code, empty basket
        $this->db->insert(
            's_emarketing_vouchers',
            $voucherData
        );

        $customer = $this->createDummyCustomer();
        $this->db->update(
            's_user',
            array('customergroup' => $randomCustomerGroup[1]['groupkey']),
            array('id = ?' => $customer->getId())
        );
        $this->module->sSYSTEM->sUSERGROUPDATA['id'] = $randomCustomerGroup[1]['id'];
        $this->session['sUserId'] = $customer->getId();
        $this->module->sSYSTEM->sSESSION_ID = uniqid(rand());
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
            array(
                'price' => $voucherData["minimumcharge"]+1,
                'quantity' => 1,
                'sessionID' => $this->session->get('sessionId'),
                'ordernumber' => $randomArticle['ordernumber'],
                'articleID' => $randomArticle['articleID'],
            )
        );

        // Test again with the same one-time code, fail
        $result = $this->module->sAddVoucher($voucherData['vouchercode']);
        $this->assertInternalType('array', $result);
        $this->assertArrayHasKey('sErrorFlag', $result);
        $this->assertArrayHasKey('sErrorMessages', $result);
        $this->assertTrue($result['sErrorFlag']);
        $this->assertContains(
            $this->snippetManager->getNamespace('frontend/basket/internalMessages')->get(
                'VoucherFailureCustomerGroup',
                'This voucher is not available for your customer group'
            ),
            $result['sErrorMessages']
        );

        // Change the user's customer group
        $this->db->update(
            's_user',
            array('customergroup' => $randomCustomerGroup[0]['groupkey']),
            array('id = ?' => $customer->getId())
        );
        $this->module->sSYSTEM->sUSERGROUPDATA['id'] = $randomCustomerGroup[0]['id'];

        $previousAmount = $this->module->sGetAmount();
        // Test with one-time code, success
        $this->assertTrue($this->module->sAddVoucher($voucherData['vouchercode']));
        $this->assertLessThan($previousAmount, $this->module->sGetAmount());

        // Housekeeping
        $this->db->delete(
            's_order_basket',
            array('sessionID = ?' => $this->session->get('sessionId'))
        );
        $this->db->delete(
            's_emarketing_vouchers',
            array('vouchercode = ?' => $voucherData['vouchercode'])
        );
        $this->deleteDummyCustomer($customer);
    }

    /**
     * @covers sBasket::sAddVoucher
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
        $voucherData = array(
            'vouchercode' => 'testOne',
            'description' => 'testOne description',
            'numberofunits' => 1,
            'numorder' => 1,
            'value' => 10,
            'minimumcharge' => 10,
            'ordercode' => uniqid(rand()),
            'modus' => 0,
            'restrictarticles' => $randomArticles[0]['ordernumber']
        );
        // Try with valid voucher code, empty basket
        $this->db->insert(
            's_emarketing_vouchers',
            $voucherData
        );

        $customer = $this->createDummyCustomer();
        $this->session['sUserId'] = $customer->getId();
        $this->module->sSYSTEM->sSESSION_ID = uniqid(rand());
        $this->session->offsetSet('sessionId', $this->module->sSYSTEM->sSESSION_ID);

        // Add one article to the basket with enough value to use discount
        $this->db->insert(
            's_order_basket',
            array(
                'price' => $voucherData["minimumcharge"]+1,
                'quantity' => 1,
                'sessionID' => $this->session->get('sessionId'),
                'ordernumber' => $randomArticles[1]['ordernumber'],
                'articleID' => $randomArticles[1]['articleID'],
            )
        );

        // Test again  code, fail
        $result = $this->module->sAddVoucher($voucherData['vouchercode']);
        $this->assertInternalType('array', $result);
        $this->assertArrayHasKey('sErrorFlag', $result);
        $this->assertArrayHasKey('sErrorMessages', $result);
        $this->assertTrue($result['sErrorFlag']);
        $this->assertContains(
            $this->snippetManager->getNamespace('frontend/basket/internalMessages')->get(
                'VoucherFailureProducts',
                'This voucher is only available in combination with certain products'
            ),
            $result['sErrorMessages']
        );

        $this->db->insert(
            's_order_basket',
            array(
                'price' => $voucherData["minimumcharge"]+1,
                'quantity' => 1,
                'sessionID' => $this->session->get('sessionId'),
                'ordernumber' => $randomArticles[0]['ordernumber'],
                'articleID' => $randomArticles[0]['articleID'],
            )
        );

        $previousAmount = $this->module->sGetAmount();
        // Test with one-time code, success
        $this->assertTrue($this->module->sAddVoucher($voucherData['vouchercode']));
        $this->assertLessThan($previousAmount, $this->module->sGetAmount());

        // Housekeeping
        $this->db->delete(
            's_order_basket',
            array('sessionID = ?' => $this->session->get('sessionId'))
        );
        $this->db->delete(
            's_emarketing_vouchers',
            array('vouchercode = ?' => $voucherData['vouchercode'])
        );
        $this->deleteDummyCustomer($customer);
    }

    /**
     * @covers sBasket::sAddVoucher
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
            array($randomArticleOne['supplierID'])
        );

        $voucherData = array(
            'vouchercode' => 'testOne',
            'description' => 'testOne description',
            'numberofunits' => 1,
            'numorder' => 1,
            'value' => 10,
            'minimumcharge' => 10,
            'ordercode' => uniqid('ordercode', true),
            'modus' => 0,
            'bindtosupplier' => $randomArticleOne['supplierID']
        );
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
            array(
                'price' => $voucherData["minimumcharge"]+1,
                'quantity' => 1,
                'sessionID' => $this->session->get('sessionId'),
                'ordernumber' => $randomArticleTwo['ordernumber'],
                'articleID' => $randomArticleTwo['articleID'],
            )
        );

        $supplierOne = $this->db->fetchOne(
            'SELECT name FROM s_articles_supplier WHERE id = ?',
            array($randomArticleOne['supplierID'])
        );
        $result = $this->module->sAddVoucher($voucherData['vouchercode']);
        $this->assertInternalType('array', $result);
        $this->assertArrayHasKey('sErrorFlag', $result);
        $this->assertArrayHasKey('sErrorMessages', $result);
        $this->assertTrue($result['sErrorFlag']);
        $this->assertContains(
            str_replace(
                "{sSupplier}",
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
            array(
                'price' => $voucherData["minimumcharge"]+1,
                'quantity' => 1,
                'sessionID' => $this->session->get('sessionId'),
                'ordernumber' => $randomArticleOne['ordernumber'],
                'articleID' => $randomArticleOne['articleID'],
            )
        );

        $previousAmount = $this->module->sGetAmount();
        // Test with one-time code, success
        $this->assertTrue($this->module->sAddVoucher($voucherData['vouchercode']));
        $this->assertLessThan($previousAmount, $this->module->sGetAmount());

        // Housekeeping
        $this->db->delete(
            's_order_basket',
            array('sessionID = ?' => $this->session->get('sessionId'))
        );
        $this->db->delete(
            's_emarketing_vouchers',
            array('vouchercode = ?' => $voucherData['vouchercode'])
        );
        $this->deleteDummyCustomer($customer);
    }

    /**
     * @covers sBasket::sGetBasketIds
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

        $this->module->sSYSTEM->sSESSION_ID = uniqid(rand());
        $this->session->offsetSet('sessionId', $this->module->sSYSTEM->sSESSION_ID);

        // Test with empty basket, empty
        $this->assertNull($this->module->sGetBasketIds());

        // Add the first article to the basket, test we get the article id
        $this->db->insert(
            's_order_basket',
            array(
                'price' => 1,
                'quantity' => 1,
                'sessionID' => $this->session->get('sessionId'),
                'ordernumber' => $randomArticles[0]['ordernumber'],
                'articleID' => $randomArticles[0]['articleID'],
            )
        );
        $this->assertEquals(
            array($randomArticles[0]['articleID']),
            $this->module->sGetBasketIds()
        );

        // Add the first article to the basket again, test we get the same result
        $this->db->insert(
            's_order_basket',
            array(
                'price' => 1,
                'quantity' => 1,
                'sessionID' => $this->session->get('sessionId'),
                'ordernumber' => $randomArticles[0]['ordernumber'],
                'articleID' => $randomArticles[0]['articleID'],
            )
        );
        $this->assertEquals(
            array($randomArticles[0]['articleID']),
            $this->module->sGetBasketIds()
        );

        // Add the second article to the basket, test we get the two ids
        $this->db->insert(
            's_order_basket',
            array(
                'price' => 1,
                'quantity' => 1,
                'sessionID' => $this->session->get('sessionId'),
                'ordernumber' => $randomArticles[1]['ordernumber'],
                'articleID' => $randomArticles[1]['articleID'],
            )
        );

        $basketIds = $this->module->sGetBasketIds();
        $this->assertContains(
            $randomArticles[0]['articleID'],
            $basketIds
        );
        $this->assertContains(
            $randomArticles[1]['articleID'],
            $basketIds
        );

        // Housekeeping
        $this->db->delete(
            's_order_basket',
            array('sessionID = ?' => $this->session->get('sessionId'))
        );
    }

    /**
     * @covers sBasket::sCheckMinimumCharge
     */
    public function testsCheckMinimumCharge()
    {
        $oldMinimumOrder = $this->module->sSYSTEM->sUSERGROUPDATA["minimumorder"];
        $oldMinimumOrderSurcharge = $this->module->sSYSTEM->sUSERGROUPDATA["minimumordersurcharge"];

        // Test with minimum order surcharge, always returns false
        $this->module->sSYSTEM->sUSERGROUPDATA["minimumordersurcharge"] = 10;
        $this->assertFalse($this->module->sCheckMinimumCharge());

        $this->module->sSYSTEM->sUSERGROUPDATA["minimumordersurcharge"] = 0;
        $this->module->sSYSTEM->sUSERGROUPDATA["minimumorder"] = 10;

        // Test with empty cart, expect 10
        $this->assertEquals(10, $this->module->sCheckMinimumCharge());

        $this->module->sSYSTEM->sSESSION_ID = uniqid(rand());
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
            array(
                'price' => 2,
                'quantity' => 1,
                'sessionID' => $this->session->get('sessionId'),
                'ordernumber' => $randomArticle['ordernumber'],
                'articleID' => $randomArticle['articleID'],
            )
        );

        // Test with non-empty cart, expect 10
        $this->assertEquals(10, $this->module->sCheckMinimumCharge());

        // Pass the minimum value, expect false
        $this->db->insert(
            's_order_basket',
            array(
                'price' => 20,
                'quantity' => 1,
                'sessionID' => $this->session->get('sessionId'),
                'ordernumber' => $randomArticle['ordernumber'],
                'articleID' => $randomArticle['articleID'],
            )
        );

        $this->assertFalse($this->module->sCheckMinimumCharge());

        // Housekeeping
        $this->module->sSYSTEM->sUSERGROUPDATA["minimumorder"] = $oldMinimumOrder;
        $this->module->sSYSTEM->sUSERGROUPDATA["minimumordersurcharge"] = $oldMinimumOrderSurcharge;
        $this->db->delete(
            's_order_basket',
            array('sessionID = ?' => $this->session->get('sessionId'))
        );
    }

    /**
     * @covers sBasket::sInsertSurcharge
     */
    public function testsInsertSurcharge()
    {
        $oldMinimumOrder = $this->module->sSYSTEM->sUSERGROUPDATA["minimumorder"];
        $oldMinimumOrderSurcharge = $this->module->sSYSTEM->sUSERGROUPDATA["minimumordersurcharge"];

        // Empty basket, expect false
        $this->assertFalse(
            $this->invokeMethod(
                $this->module,
                'sInsertSurcharge',
                array()
            )
        );

        $this->module->sSYSTEM->sSESSION_ID = uniqid(rand());
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
            array(
                'price' => 2,
                'quantity' => 1,
                'sessionID' => $this->session->get('sessionId'),
                'ordernumber' => $randomArticle['ordernumber'],
                'articleID' => $randomArticle['articleID'],
            )
        );

        $this->module->sSYSTEM->sUSERGROUPDATA["minimumordersurcharge"] = 5;
        $this->module->sSYSTEM->sUSERGROUPDATA["minimumorder"] = 10;

        // Check that we have no surcharge
        $this->assertEmpty(
            $this->db->fetchRow(
                'SELECT * FROM s_order_basket WHERE sessionID = ? AND modus=4',
                array($this->module->sSYSTEM->sSESSION_ID)
            )
        );

        // Add surcharge, expect success (null)
        $this->assertNull(
            $this->invokeMethod(
                $this->module,
                'sInsertSurcharge',
                array()
            )
        );

        // Fetch the surcharge row, should have price 5
        $surchargeRow = $this->db->fetchRow(
            'SELECT * FROM s_order_basket WHERE sessionID = ? AND modus=4',
            array($this->module->sSYSTEM->sSESSION_ID)
        );
        $this->assertEquals(5, $surchargeRow['price']);

        // Housekeeping
        $this->module->sSYSTEM->sUSERGROUPDATA["minimumorder"] = $oldMinimumOrder;
        $this->module->sSYSTEM->sUSERGROUPDATA["minimumordersurcharge"] = $oldMinimumOrderSurcharge;
        $this->db->delete(
            's_order_basket',
            array('sessionID = ?' => $this->session->get('sessionId'))
        );
    }

    /**
     * @covers sBasket::sInsertSurchargePercent
     */
    public function testsInsertSurchargePercent()
    {
        // No user and no payment id, expect false
        $this->assertFalse(
            $this->invokeMethod(
                $this->module,
                'sInsertSurchargePercent',
                array()
            )
        );

        $customer = $this->createDummyCustomer();
        $paymentData = array(
            'name' => 'testPaymentMean',
            'description' => 'testPaymentMean',
            'debit_percent' => 5
        );
        $this->db->insert('s_core_paymentmeans', $paymentData);
        $paymentMeanId = $this->db->lastInsertId();
        $this->db->update(
            's_user',
            array('paymentID' => $paymentMeanId),
            array('id = ?' => $customer->getId())
        );
        $this->session['sUserId'] = $customer->getId();
        $this->module->sSYSTEM->sSESSION_ID = uniqid(rand());
        $this->session->offsetSet('sessionId', $this->module->sSYSTEM->sSESSION_ID);

        // Empty basket, expect false
        $this->assertFalse(
            $this->invokeMethod(
                $this->module,
                'sInsertSurchargePercent',
                array()
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
            array(
                'price' => 2,
                'quantity' => 1,
                'sessionID' => $this->session->get('sessionId'),
                'ordernumber' => $randomArticle['ordernumber'],
                'articleID' => $randomArticle['articleID'],
            )
        );

        // Check that we have no surcharge
        $this->assertEmpty(
            $this->db->fetchRow(
                'SELECT * FROM s_order_basket WHERE sessionID = ? AND modus=4',
                array($this->module->sSYSTEM->sSESSION_ID)
            )
        );

        // Add surcharge, expect success (null)
        $this->assertNull(
            $this->invokeMethod(
                $this->module,
                'sInsertSurchargePercent',
                array()
            )
        );

        // Fetch the surcharge row, should have price 5
        $surchargeRow = $this->db->fetchRow(
            'SELECT * FROM s_order_basket WHERE sessionID = ? AND modus = 4',
            array($this->module->sSYSTEM->sSESSION_ID)
        );
        $this->assertEquals(2/ 100 * 5, $surchargeRow['price']);

        // Housekeeping
        $this->deleteDummyCustomer($customer);
        $this->db->delete(
            's_order_basket',
            array('sessionID = ?' => $this->session->get('sessionId'))
        );
        $this->db->delete(
            's_core_paymentmeans',
            array('name = ?' => 'testPaymentMean')
        );
    }

    /**
     * @covers sBasket::sGetBasket
     */
    public function testsGetBasket()
    {
        // Test with empty basket
        $this->assertEquals(array(), $this->module->sGetBasket());

        $this->module->sSYSTEM->sSESSION_ID = uniqid(rand());
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
            array(
                'price' => 2,
                'quantity' => 1,
                'sessionID' => $this->session->get('sessionId'),
                'ordernumber' => $randomArticle['ordernumber'],
                'articleID' => $randomArticle['articleID'],
            )
        );

        $keys = array(
            'content',
            'Amount',
            'AmountNet',
            'Quantity',
            'AmountNumeric',
            'AmountNetNumeric',
            'AmountWithTax',
            'AmountWithTaxNumeric'
        );

        $contentKeys = array(
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
        );

        $result = $this->module->sGetBasket();
        $this->assertEquals($keys, array_keys($result));
        $this->assertGreaterThanOrEqual(1, count($result['content']));
        foreach ($contentKeys as $key) {
            $this->assertArrayHasKey($key, $result['content'][0]);
        }

        $this->assertGreaterThanOrEqual(1, count($result['content']));
        $this->assertGreaterThanOrEqual(2, $result['Amount']);
        $this->assertGreaterThanOrEqual(2, $result['AmountNet']);
        $this->assertGreaterThanOrEqual(2, $result['AmountNumeric']);
        $this->assertGreaterThanOrEqual(2, $result['AmountNetNumeric']);
        $this->assertEquals(1, $result['Quantity']);
    }

    /**
     * @covers sBasket::sAddNote
     */
    public function testsAddNote()
    {
        $_COOKIE['sUniqueID'] = md5(uniqid('sAddNote', true));

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

        $this->assertEquals(0, $this->db->fetchOne(
            'SELECT COUNT(DISTINCT id) FROM s_order_notes WHERE sUniqueID = ? AND ordernumber = ?',
            array($this->module->sSYSTEM->_COOKIE["sUniqueID"], $randomArticle['ordernumber'])
        ));

        $this->assertTrue($this->module->sAddNote(
            $randomArticle['articleID'],
            $randomArticle['name'],
            $randomArticle['ordernumber']
        ));

        $this->assertEquals(1, $this->db->fetchOne(
            'SELECT COUNT(DISTINCT id) FROM s_order_notes WHERE sUniqueID = ? AND ordernumber = ?',
            array($this->module->sSYSTEM->_COOKIE["sUniqueID"], $randomArticle['ordernumber'])
        ));

        $this->assertTrue($this->module->sAddNote(
            $randomArticle['articleID'],
            $randomArticle['name'],
            $randomArticle['ordernumber']
        ));

        return array($randomArticle, $_COOKIE['sUniqueID']);
    }

    /**
     * @covers sBasket::sGetNotes
     * @depends testsAddNote
     */
    public function testsGetNotes($input)
    {
        list($randomArticle, $cookieId) = $input;

        // Test with no id in cookie
        $this->assertEquals(array(), $this->module->sGetNotes());
        $_COOKIE["sUniqueID"] = $cookieId;

        $result = $this->module->sGetNotes();
        $this->assertEquals($randomArticle['articleID'], $result[0]['articleID']);

        return array($randomArticle, $cookieId);
    }

    /**
     * @covers sBasket::sCountNotes
     * @depends testsGetNotes
     */
    public function testsCountNotes($input)
    {
        list($randomArticleOne, $cookieId) = $input;

        // Test with no id in cookie
        $_COOKIE["sUniqueID"] = $cookieId;
        $this->assertEquals(1, $this->module->sCountNotes());

        // Add another article to the basket
        $randomArticleTwo = $this->db->fetchRow(
            'SELECT detail.articleID, article.name, detail.ordernumber
            FROM s_articles_details detail
            INNER JOIN s_articles article
              ON article.id = detail.articleID
            WHERE detail.active = 1
            AND detail.id <> ?
            LIMIT 1',
            array($randomArticleOne['id'])
        );

        $this->assertTrue($this->module->sAddNote(
            $randomArticleTwo['articleID'],
            $randomArticleTwo['name'],
            $randomArticleTwo['ordernumber']
        ));

        $this->assertEquals(2, $this->module->sCountNotes());

        return array(array($randomArticleOne, $randomArticleTwo), $cookieId);
    }

    /**
     * @covers sBasket::sDeleteNote
     * @depends testsCountNotes
     */
    public function testsDeleteNote($input)
    {
        list($randomArticles, $cookieId) = $input;
        $_COOKIE["sUniqueID"] = $cookieId;

        // Null argument, return null
        $this->assertFalse($this->module->sDeleteNote(null));

        // Get random article that's not in the basket
        $randomNotPresentArticleId = $this->db->fetchOne(
            'SELECT detail.id FROM s_articles_details detail
            INNER JOIN s_articles article
              ON article.id = detail.articleID
            WHERE detail.active = 1
            AND detail.id NOT IN (?)
            LIMIT 1',
            array(array_column($randomArticles, 'id'))
        );

        // Check that we currently have 2 articles
        $this->assertEquals(2, $this->module->sCountNotes());

        // Get true even if article is not in the wishlist
        $this->assertTrue($this->module->sDeleteNote($randomNotPresentArticleId));

        // Check that we still have 2 articles
        $this->assertEquals(2, $this->module->sCountNotes());

        $noteIds = $this->db->fetchCol(
            'SELECT id FROM s_order_notes detail
            WHERE sUniqueID = ?',
            array($this->module->sSYSTEM->_COOKIE["sUniqueID"])
        );

        // Get true even if article is not in the wishlist
        $this->assertTrue($this->module->sDeleteNote($noteIds[0]));

        // Check that we now have 1 article
        $this->assertEquals(1, $this->module->sCountNotes());

        // Get true even if article is not in the wishlist
        $this->assertTrue($this->module->sDeleteNote($noteIds[1]));

        // Check that we now have an empty wishlist
        $this->assertEquals(0, $this->module->sCountNotes());
    }

    /**
     * @covers sBasket::sUpdateArticle
     */
    public function testsUpdateArticle()
    {
        // Null args, false result
        $this->assertFalse($this->module->sUpdateArticle(null, null));

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
            array(
                'price' => 0.01,
                'quantity' => 1,
                'sessionID' => $this->session->get('sessionId'),
                'ordernumber' => $randomArticle['ordernumber'],
                'articleID' => $randomArticle['articleID'],
            )
        );
        $basketId = $this->db->lastInsertId();

        // Store previous amount
        $previousAmount = $this->module->sGetAmount();
        $this->assertEquals(array('totalAmount' => 0.01), $previousAmount);

        // Update the article, prices are recalculated
        $this->assertNull($this->module->sUpdateArticle($basketId, 1));
        $oneAmount = $this->module->sGetAmount();
        $this->assertGreaterThan($previousAmount['totalAmount'], $oneAmount['totalAmount']);

        // Update from 1 to 2, we should get a more expensive cart
        $this->assertNull($this->module->sUpdateArticle($basketId, 2));
        $twoAmount = $this->module->sGetAmount();
        $this->assertGreaterThanOrEqual($oneAmount['totalAmount'], $twoAmount['totalAmount']);
        $this->assertLessThanOrEqual(2*$oneAmount['totalAmount'], $twoAmount['totalAmount']);

        // Housekeeping
        $this->db->delete(
            's_order_basket',
            array('sessionID = ?' => $this->session->get('sessionId'))
        );
    }

    /**
     * @covers sBasket::sCheckForESD
     */
    public function testsCheckForESD()
    {
        // No session, expect false
        $this->assertFalse($this->module->sCheckForESD());

        $this->module->sSYSTEM->sSESSION_ID = uniqid(rand());
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

        $this->assertGreaterThan(0, $this->module->sAddArticle($randomNoESDArticle['ordernumber'], 1));

        $this->assertFalse($this->module->sCheckForESD());

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
            array('active' => 1),
            array('id = ?' => $randomESDArticle['id'])
        );
        $this->db->update(
            's_articles',
            array('active' => 1),
            array('id = ?' => $randomESDArticle['articleID'])
        );
        $this->module->sAddArticle($randomESDArticle['ordernumber'], 1);

        $this->assertTrue($this->module->sCheckForESD());

        // Housekeeping
        $this->db->delete(
            's_order_basket',
            array('sessionID = ?' => $this->session->get('sessionId'))
        );
        $this->db->update(
            's_articles_details',
            array('active' => 0),
            array('id = ?' => $randomESDArticle['id'])
        );
        $this->db->update(
            's_articles',
            array('active' => 0),
            array('id = ?' => $randomESDArticle['articleID'])
        );
    }

    /**
     * @covers sBasket::sDeleteBasket
     */
    public function testsDeleteBasket()
    {
        // No session, expect false
        $this->assertFalse($this->module->sDeleteBasket());

        $this->module->sSYSTEM->sSESSION_ID = uniqid(rand());
        $this->session->offsetSet('sessionId', $this->module->sSYSTEM->sSESSION_ID);

        $this->assertNull($this->module->sDeleteBasket());

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
            AND (article.laststock = 0 OR detail.instock > 0)
            LIMIT 1'
        );

        $this->module->sAddArticle($randomArticle['ordernumber'], 1);

        $this->assertNotEquals(0, $this->module->sCountBasket());

        $this->module->sDeleteBasket();

        $this->assertEquals(0, $this->module->sCountBasket());
    }

    /**
     * @covers sBasket::sDeleteArticle
     */
    public function testsDeleteArticle()
    {
        // No id, expect null
        $this->assertNull($this->module->sDeleteArticle(null));

        // Random id, expect null
        $this->assertNull($this->module->sDeleteArticle(9999999));

        $this->module->sSYSTEM->sSESSION_ID = uniqid(rand());
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
            AND (article.laststock = 0 OR detail.instock > 0)
            LIMIT 1'
        );
        $idOne = $this->module->sAddArticle($randomArticle['ordernumber'], 1);
        $this->assertEquals(1, $this->module->sCountBasket());

        $this->module->sDeleteArticle($idOne);
        $this->assertEquals(0, $this->module->sCountBasket());
    }

    /**
     * @covers sBasket::sAddArticle
     */
    public function testsAddArticle()
    {
        // No id, expect false
        $this->assertFalse($this->module->sAddArticle(null));

        $this->module->sSYSTEM->sSESSION_ID = uniqid(rand());
        $this->session->offsetSet('sessionId', $this->module->sSYSTEM->sSESSION_ID);

        // Get random article with stock controll and add it to the basket
        $randomArticleOne = $this->db->fetchRow(
            'SELECT detail.* FROM s_articles_details detail
            INNER JOIN s_articles article
              ON article.id = detail.articleID
            LEFT JOIN s_articles_avoid_customergroups avoid
              ON avoid.articleID = article.id
            WHERE detail.active = 1
            AND laststock = 1
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
        $this->assertEquals(1, $basket['Quantity']);
        $this->assertEquals(1, $basket['content'][0]['quantity']);


        // Adding article with quantity adds correctly, finds stacks
        $this->module->sAddArticle($randomArticleOne['ordernumber'], 2);
        $basket = $this->module->sGetBasket();
        $this->assertEquals(1, $basket['Quantity']);
        $this->assertEquals(3, $basket['content'][0]['quantity']);

        // Start over
        $this->module->sDeleteBasket();

        // Adding article with quantity over stock, check that we have the available stock
        $this->module->sAddArticle($randomArticleOne['ordernumber'], $randomArticleOne['instock']+200);
        $basket = $this->module->sGetBasket();
        $this->assertEquals(1, $basket['Quantity']);
        $this->assertEquals(min($randomArticleOne['instock'], 100), $basket['content'][0]['quantity']);

        // Start over
        $this->module->sDeleteBasket();

        // Get random article and add it to the basket
        $randomArticleTwo = $this->db->fetchRow(
            'SELECT detail.* FROM s_articles_details detail
            INNER JOIN s_articles article
              ON article.id = detail.articleID
            WHERE detail.active = 1
            AND laststock = 0
            AND instock > 20
            AND instock < 70
            AND article.id NOT IN (
              SELECT articleID
              FROM s_articles_avoid_customergroups
              WHERE customergroupID = 1
            )
            LIMIT 1'
        );

        // Adding article with quantity over stock, check that we have the desired quantity
        $this->module->sAddArticle($randomArticleTwo['ordernumber'], $randomArticleTwo['instock']+20);
        $basket = $this->module->sGetBasket();
        $this->assertEquals(1, $basket['Quantity']);
        $this->assertEquals(min($randomArticleTwo['instock']+20, 100), $basket['content'][0]['quantity']);

        // Housekeeping
        $this->db->delete(
            's_order_basket',
            array('sessionID = ?' => $this->session->get('sessionId'))
        );
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
        $lastLogin = $date->format(DateTime::ISO8601);

        $birthday = DateTime::createFromFormat('Y-m-d', '1986-12-20')->format(DateTime::ISO8601);

        $testData = array(
            "password" => "fooobar",
            "email"    => uniqid(rand()) . 'test@foobar.com',

            "lastlogin"  => $lastLogin,

            "salutation" => "mr",
            "firstname" => "Max",
            "lastname"  => "Mustermann",
            "birthday"  => $birthday,

            "billing" => array(
                'salutation' => 'mr',
                "firstname" => "Max",
                "lastname"  => "Mustermann",
                'street' => 'Musterstr. 123',
                'city' => 'Musterhausen',
                "attribute" => array(
                    'text1' => 'Freitext1',
                    'text2' => 'Freitext2',
                ),
                "zipcode"   => '12345',
                "country" => '2'
            ),

            "shipping" => array(
                "salutation" => "mr",
                "company"    => "Widgets Inc.",
                "firstname"  => "Max",
                "lastname"   => "Mustermann",
                "street"     => "Merkel Strasse, 10",
                'city'       => 'Musterhausen',
                'zipcode'    => '12345',
                "country"  => '3',
                "attribute"  => array(
                    'text1'  => 'Freitext1',
                    'text2'  => 'Freitext2',
                ),
            ),

            "debit" => array(
                "account"       => "Fake Account",
                "bankCode"      => "55555555",
                "bankName"      => "Fake Bank",
                "accountHolder" => "Max Mustermann",
            ),
        );

        $customerResource = new \Shopware\Components\Api\Resource\Customer();
        $customerResource->setManager(Shopware()->Models());

        return $customerResource->create($testData);
    }

    /**
     * Deletes all dummy customer entity
     */
    private function deleteDummyCustomer(\Shopware\Models\Customer\Customer $customer)
    {
        $billingId = $this->db->fetchOne('SELECT id FROM s_user_billingaddress WHERE userID = ?', array($customer->getId()));
        $shippingId = $this->db->fetchOne('SELECT id FROM s_user_shippingaddress WHERE userID = ?', array($customer->getId()));

        if ($billingId) {
            $this->db->delete('s_user_billingaddress_attributes', 'billingID = '.$billingId);
            $this->db->delete('s_user_billingaddress', 'id = '.$billingId);
        }
        if ($shippingId) {
            $this->db->delete('s_user_shippingaddress_attributes', 'shippingID = '.$shippingId);
            $this->db->delete('s_user_shippingaddress', 'id = '.$shippingId);
        }
        $this->db->delete('s_core_payment_data', 'user_id = '.$customer->getId());
        $this->db->delete('s_user_attributes', 'userID = '.$customer->getId());
        $this->db->delete('s_user', 'id = '.$customer->getId());
    }

    /**
     * Call protected/private method of a class.
     *
     * @param object &$object Instantiated object that we will run method on.
     * @param string $methodName Method name to call
     * @param array $parameters Array of parameters to pass into method.
     *
     * @return mixed Method return.
     */
    private function invokeMethod(&$object, $methodName, array $parameters = array())
    {
        $reflection = new \ReflectionClass(get_class($object));
        $method = $reflection->getMethod($methodName);
        $method->setAccessible(true);

        return $method->invokeArgs($object, $parameters);
    }
}
