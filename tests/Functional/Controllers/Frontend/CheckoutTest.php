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

namespace Shopware\Tests\Functional\Controllers\Frontend;

use Doctrine\DBAL\Connection;
use Enlight_Components_Test_Plugin_TestCase;
use Enlight_Controller_Request_RequestTestCase;
use Enlight_Controller_Response_ResponseTestCase;
use Enlight_Template_Manager;
use Enlight_View_Default;
use LogicException;
use sAdmin;
use sBasket;
use Shopware\Bundle\CartBundle\CheckoutKey;
use Shopware\Bundle\OrderBundle\Service\CalculationServiceInterface;
use Shopware\Components\Model\ModelManager;
use Shopware\Models\Customer\Group as CustomerGroup;
use Shopware\Models\Order\Order;
use Shopware\Models\Shop\Shop;
use Shopware\Tests\Functional\Helper\Utils;
use Shopware\Tests\Functional\Traits\ContainerTrait;
use Shopware\Tests\Functional\Traits\CustomerLoginTrait;
use Shopware\Tests\Functional\Traits\DatabaseTransactionBehaviour;
use Shopware_Components_Translation;
use Shopware_Controllers_Frontend_Checkout;
use Symfony\Component\HttpFoundation\Request;

class CheckoutTest extends Enlight_Components_Test_Plugin_TestCase
{
    use ContainerTrait;
    use CustomerLoginTrait;
    use DatabaseTransactionBehaviour;

    private const PRODUCT_NUMBER = 'SW10239';
    private const USER_AGENT = 'Mozilla/5.0 (Android; Tablet; rv:14.0) Gecko/14.0 Firefox/14.0';
    private const ENGLISH_SHOP_ID = 2;
    private const COUNTRY_SETTINGS_TRANSLATION_KEY = 'config_countries';
    private const PAYMENT_ID_INVOICE = 4;
    private const ERROR_MESSAGE_LAST_STOCK = 'Leider können wir den von Ihnen gewünschten Artikel nicht mehr in ausreichender Stückzahl liefern.';

    private Connection $connection;

    private sAdmin $adminModule;

    private sBasket $basketModule;

    protected function setUp(): void
    {
        $this->connection = $this->getContainer()->get(Connection::class);
        $this->adminModule = $this->getContainer()->get('modules')->Admin();
        $this->basketModule = $this->getContainer()->get('modules')->Basket();
        parent::setUp();
    }

    /**
     * Reads the user agent black list and test if the bot can add a product
     *
     * @ticket SW-6411
     */
    public function testBotAddBasketProduct(): void
    {
        $botBlackList = ['digout4u', 'fast-webcrawler', 'googlebot', 'ia_archiver', 'w3m2', 'frooglebot'];
        foreach ($botBlackList as $userAgent) {
            $sessionId = $this->addBasketProduct($userAgent);
            static::assertNotEmpty($sessionId);
            $basketId = $this->connection->fetchOne(
                'SELECT id FROM s_order_basket WHERE sessionID = ?',
                [$sessionId]
            );
            static::assertEmpty($basketId);
        }

        $this->getContainer()->get('modules')->Basket()->sDeleteBasket();
    }

    /**
     * Test if a normal user can add a product
     *
     * @ticket SW-6411
     */
    public function testAddBasketProduct(): void
    {
        $sessionId = $this->addBasketProduct(self::USER_AGENT);
        static::assertNotEmpty($sessionId);
        $basketId = $this->connection->fetchOne(
            'SELECT id FROM s_order_basket WHERE sessionID = ?',
            [$sessionId]
        );
        static::assertNotEmpty($basketId);

        $this->getContainer()->get('modules')->Basket()->sDeleteBasket();
    }

    /**
     * Tests that price calculations of the basket do not differ from the price calculation in the Order
     * for customer group
     */
    public function testCheckoutForNetOrders(): void
    {
        $this->runCheckoutTest(true);
    }

    /**
     * Tests that price calculations of the basket do not differ from the price calculation in the Order
     * for customer group
     */
    public function testCheckoutForGrossOrders(): void
    {
        $this->runCheckoutTest(false);
    }

    /**
     * Tests that the addArticle-Action returns HTML
     */
    public function testAddToBasketReturnsHtml(): void
    {
        $this->reset();
        $this->Request()->setMethod('POST');
        $this->Request()->setHeader('User-Agent', self::USER_AGENT);
        $this->Request()->setParam('sQuantity', 5);
        $this->Request()->setParam('sAdd', self::PRODUCT_NUMBER);
        $this->Request()->setParam('isXHR', 1);

        $responseText = $this->dispatch('/checkout/addArticle', true)->getBody();
        static::assertIsString($responseText);
        static::assertStringContainsString('<div class="modal--checkout-add-article">', $responseText);

        $this->getContainer()->get('modules')->Basket()->sDeleteBasket();
    }

    public function testAddProductActionErrorMessages(): void
    {
        $this->connection->executeQuery(
            'UPDATE s_articles_details, s_articles SET s_articles_details.laststock = 1, s_articles.laststock = 1 WHERE s_articles_details.ordernumber = :ordernumber AND s_articles.id = s_articles_details.articleID',
            [':ordernumber' => self::PRODUCT_NUMBER]
        );

        $controller = $this->createController();
        $request = new Enlight_Controller_Request_RequestTestCase();
        $response = new Enlight_Controller_Response_ResponseTestCase();

        $front = $this->getContainer()->get('front');
        $front->setRequest($request);
        $front->setResponse($response);

        $request->setMethod(Request::METHOD_POST);
        $request->setPost([
            'sAdd' => self::PRODUCT_NUMBER,
        ]);

        $controller->setFront($front);
        $controller->setRequest($request);
        $controller->setResponse($response);
        $controller->init();
        $controller->addArticleAction();

        static::assertTrue($controller->Response()->isRedirect());
        static::assertSame(302, $controller->Response()->getHttpResponseCode());
        static::assertStringEndsWith('/index/cart', $response->getHeader('Location'));
        static::assertSame(self::ERROR_MESSAGE_LAST_STOCK, $this->getContainer()->get('session')->get('sErrorMessages'));
        static::assertSame(self::ERROR_MESSAGE_LAST_STOCK, $controller->View()->getAssign('sBasketInfo'));
    }

    /**
     * Tests that products can't add to basket over HTTP-GET
     */
    public function testAddBasketOverGetFails(): void
    {
        $this->expectException(LogicException::class);

        $this->reset();
        $this->Request()->setHeader('User-Agent', self::USER_AGENT);
        $this->Request()->setParam('sQuantity', 5);
        $this->Request()->setMethod(Request::METHOD_GET);
        $this->dispatch('/checkout/addArticle/sAdd/' . self::PRODUCT_NUMBER);

        $this->getContainer()->get('modules')->Basket()->sDeleteBasket();
    }

    public function testRequestPaymentWithoutAGB(): void
    {
        // Login
        $this->loginCustomer();

        // Add product to basket
        $this->addBasketProduct(self::USER_AGENT, 5);

        // Confirm checkout
        $this->reset();
        $this->Request()->setMethod('POST');
        $this->Request()->setHeader('User-Agent', self::USER_AGENT);
        $this->dispatch('/checkout/confirm');

        // Finish checkout
        $this->reset();
        $this->Request()->setMethod('POST');
        $this->Request()->setHeader('User-Agent', self::USER_AGENT);
        $this->dispatch('/checkout/payment');

        static::assertTrue($this->View()->getAssign('sAGBError'));

        // Got redirected back
        static::assertSame('confirm', $this->Request()->getActionName());

        // Logout frontend user
        $this->getContainer()->get('modules')->Admin()->logout();
    }

    public function testRequestPaymentWithoutServiceAgreement(): void
    {
        // Login
        $this->loginCustomer();

        // Add product to basket
        $this->addBasketProduct(self::USER_AGENT, 5);

        $this->connection->beginTransaction();
        $this->setConfig('serviceAttrField', 'attr1');
        $this->connection->executeStatement('UPDATE s_articles_attributes SET attr1 = 1');

        // Confirm checkout
        $this->reset();
        $this->Request()->setMethod('POST');
        $this->Request()->setHeader('User-Agent', self::USER_AGENT);
        $this->dispatch('/checkout/confirm');

        // Finish checkout
        $this->reset();
        $this->Request()->setMethod('POST');
        $this->Request()->setHeader('User-Agent', self::USER_AGENT);
        $this->Request()->setPost('sAGB', 'on');
        $this->dispatch('/checkout/payment');

        $this->setConfig('serviceAttrField', null);
        $this->connection->rollBack();

        static::assertFalse($this->View()->getAssign('sAGBError'));

        // Got redirected back
        static::assertSame('confirm', $this->Request()->getActionName());

        // Logout frontend user
        $this->getContainer()->get('modules')->Admin()->logout();
    }

    public function testCartActionWithEmptyBasket(): void
    {
        $this->dispatch('/checkout/cart');

        $cart = $this->View()->getAssign('sBasket');

        static::assertSame(0.0, $cart[CheckoutKey::AMOUNT]);
    }

    public function testCartActionWithEnglishTranslationAndWithTranslatedCountrySettings(): void
    {
        $this->Request()->setCookie('shop', self::ENGLISH_SHOP_ID);

        $this->getContainer()->get(Shopware_Components_Translation::class)->write(
            self::ENGLISH_SHOP_ID,
            self::COUNTRY_SETTINGS_TRANSLATION_KEY,
            1,
            [
                self::ENGLISH_SHOP_ID => [
                    'allow_shipping' => '1',
                ],
            ]
        );

        $this->dispatch('/checkout/cart');

        $cart = $this->View()->getAssign('sBasket');

        static::assertSame(0.0, $cart[CheckoutKey::AMOUNT]);

        $this->Request()->clearCookies();
        $this->connection->delete('s_core_translations', ['objecttype' => self::COUNTRY_SETTINGS_TRANSLATION_KEY]);
    }

    public function testRedirectShippingPaymentPageOnEmptyBasket(): void
    {
        $this->loginCustomer();
        $this->getContainer()->get('modules')->Basket()->sDeleteBasket();

        $this->Request()->setMethod('GET');
        $this->Request()->setHeader('User-Agent', self::USER_AGENT);
        $response = $this->dispatch('/checkout/shippingPayment');

        $locationHeader = array_filter($response->getHeaders(), static function (array $header) {
            return stripos($header['name'], 'location') === 0;
        });

        static::assertTrue($response->isRedirect());
        static::assertEquals(302, $response->getHttpResponseCode());
        static::assertCount(2, $locationHeader); // Known bug due to Symfony migration
        $locationHeader = array_pop($locationHeader);
        static::assertStringContainsString('/checkout/cart', $locationHeader['value']);
    }

    public function testCorrectRenderingOfErrorSnippet(): void
    {
        $view = new Enlight_View_Default($this->getContainer()->get('template'));
        $view->assign('sInvalidCartItems', ['foo', 'test']);
        $template = $view->fetch('frontend/checkout/error_messages.tpl');
        static::assertStringContainsString('Folgende Produkte sind nicht mehr verfügbar', $template);
        static::assertStringContainsString('<li>foo</li><li>test</li>', $template);
    }

    public function testIsSetFlagPaymentBlocked(): void
    {
        $this->loginCustomer();

        static::assertTrue($this->adminModule->sUpdatePayment(self::PAYMENT_ID_INVOICE));

        $this->connection->insert(
            's_core_rulesets',
            [
                'paymentID' => 4,
                'rule1' => 'ORDERVALUEMORE',
                'value1' => 100,
            ]
        );

        $this->basketModule->sAddArticle('SW10067');
        static::assertGreaterThanOrEqual(100, $this->basketModule->sGetAmount()['totalAmount']);

        $basket = $this->getContainer()->get('modules')->Basket()->sGetBasketData();
        $request = new Enlight_Controller_Request_RequestTestCase();
        $request->setControllerName('checkout');
        $front = $this->getContainer()->get('front');
        $front->setRequest($request);
        $userData = $this->getContainer()->get('modules')->Admin()->sGetUserData();
        static::assertIsArray($userData);
        static::assertTrue($this->getContainer()->get('modules')->Admin()->sManageRisks(self::PAYMENT_ID_INVOICE, $basket, $userData));

        $response = new Enlight_Controller_Response_ResponseTestCase();
        $controller = $this->getContainer()->get('shopware_controllers_frontend_checkout');
        $controller->setContainer($this->getContainer());
        $front->setRequest($request);
        $front->setResponse($response);

        $controller->setFront($front);
        $controller->setRequest($request);
        $controller->setResponse($response);
        $controller->setView(new Enlight_View_Default(new Enlight_Template_Manager()));

        $controller->init();
        $controller->ajaxCartAction();

        static::assertTrue($controller->View()->getAssign('paymentBlocked'));

        $this->logOutCustomer();
    }

    /**
     * Compares the calculated price from a basket with the calculated price from \Shopware\Bundle\OrderBundle\Service\CalculationService::recalculateOrderTotals()
     * It does so by creating via the frontend controllers, and comparing the amount (net & gross) with the values provided by
     * Order/Order::calculateInvoiceAmount (Which will be called when one changes / saves the order in the backend).
     *
     * Also covers a complete checkout process
     */
    private function runCheckoutTest(bool $net): void
    {
        $tax = $net === true ? 0 : 1;

        // Set net customer group
        $shop = $this->getContainer()->get(ModelManager::class)->getRepository(Shop::class)->find(1);
        static::assertInstanceOf(Shop::class, $shop);
        $previousCustomerGroup = $shop->getCustomerGroup()->getKey();
        $customerGroup = $this->getContainer()->get(ModelManager::class)->getRepository(CustomerGroup::class)->findOneBy(['tax' => $tax]);
        static::assertInstanceOf(CustomerGroup::class, $customerGroup);
        $netCustomerGroup = $customerGroup->getKey();
        static::assertNotEmpty($netCustomerGroup);
        $this->connection->executeStatement(
            'UPDATE s_user SET customergroup = ? WHERE id = 1',
            [$netCustomerGroup]
        );

        // Simulate checkout in frontend

        // Login
        $this->loginCustomer();

        // Add product to basket
        $this->addBasketProduct(self::USER_AGENT, 5);

        // Confirm checkout
        $this->reset();
        $this->Request()->setMethod('POST');
        $this->Request()->setHeader('User-Agent', self::USER_AGENT);
        $this->dispatch('/checkout/confirm');

        // Finish checkout
        $this->reset();
        $this->Request()->setMethod('POST');
        $this->Request()->setHeader('User-Agent', self::USER_AGENT);
        $this->Request()->setParam('sAGB', 'on');
        $this->dispatch('/checkout/finish');

        // Logout frontend user
        $this->getContainer()->get('modules')->Admin()->logout();

        // Revert customer group
        $this->connection->executeStatement(
            'UPDATE s_user SET customergroup = ? WHERE id = 1',
            [$previousCustomerGroup]
        );

        // Fetch created order
        $orderId = $this->connection->fetchOne(
            'SELECT id FROM s_order ORDER BY ID DESC LIMIT 1'
        );
        $order = $this->getContainer()->get(ModelManager::class)->getRepository(Order::class)->find($orderId);
        static::assertInstanceOf(Order::class, $order);

        // Save invoiceAmounts for comparison
        $previousInvoiceAmount = $order->getInvoiceAmount();
        $previousInvoiceAmountNet = $order->getInvoiceAmountNet();

        // Simulate backend order save
        $calculationService = $this->getContainer()->get(CalculationServiceInterface::class);
        $calculationService->recalculateOrderTotals($order);

        // Assert messages
        $message = 'InvoiceAmount' . ($net ? ' (net shop)' : '') . ': ' . $previousInvoiceAmount . ' from sBasket, ' . $order->getInvoiceAmount() . ' from getInvoiceAmount';
        $messageNet = 'InvoiceAmountNet' . ($net ? ' (net shop)' : '') . ': ' . $previousInvoiceAmountNet . ' from sBasket, ' . $order->getInvoiceAmountNet() . ' from getInvoiceAmountNet';

        // Test that sBasket calculation matches calculateInvoiceAmount
        static::assertEqualsWithDelta($order->getInvoiceAmount(), $previousInvoiceAmount, Utils::FORMER_PHPUNIT_FLOAT_EPSILON, $message);
        static::assertEqualsWithDelta($order->getInvoiceAmountNet(), $previousInvoiceAmountNet, Utils::FORMER_PHPUNIT_FLOAT_EPSILON, $messageNet);

        $this->getContainer()->get('modules')->Basket()->sDeleteBasket();
        $this->reset();
    }

    /**
     * Fires the add product request with the given user agent
     *
     * @return string session id
     */
    private function addBasketProduct(string $userAgent, int $quantity = 1): string
    {
        $this->reset();
        $this->Request()->setMethod('POST');
        $this->Request()->setHeader('User-Agent', $userAgent);
        $this->Request()->setParam('sQuantity', $quantity);
        $this->Request()->setParam('sAdd', self::PRODUCT_NUMBER);
        $this->dispatch('/checkout/addArticle', true);

        return $this->getContainer()->get('session')->getId();
    }

    private function createController(): Shopware_Controllers_Frontend_Checkout
    {
        $controller = $this->getContainer()->get('shopware_controllers_frontend_checkout');
        $controller->setView(new Enlight_View_Default(new Enlight_Template_Manager()));

        return $controller;
    }
}
