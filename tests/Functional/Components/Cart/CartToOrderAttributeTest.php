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

namespace Shopware\Tests\Functional\Components\Cart;

use Doctrine\DBAL\Connection;
use Enlight_Controller_Request_RequestHttp;
use Enlight_Controller_Response_ResponseTestCase;
use Enlight_Event_EventArgs;
use Enlight_Template_Manager;
use Enlight_View_Default;
use PHPUnit\Framework\TestCase;
use Shopware\Bundle\AttributeBundle\Service\CrudService;
use Shopware\Bundle\AttributeBundle\Service\TypeMappingInterface;
use Shopware\Components\Model\ModelManager;
use Shopware\Models\Order\Order;
use Shopware\Tests\Functional\Traits\ContainerTrait;
use Shopware\Tests\Functional\Traits\CustomerLoginTrait;
use Shopware_Controllers_Frontend_Checkout;
use Symfony\Component\HttpFoundation\Request;

class CartToOrderAttributeTest extends TestCase
{
    use ContainerTrait;
    use CustomerLoginTrait;

    private const TEST_ATTRIBUTE_NAME = 'test_attr';
    private const TEST_ATTRIBUTE_VALUE = 'test';
    private const CUSTOMER_ID = 1;

    private CrudService $attributeService;

    private Connection $connection;

    private ModelManager $modelManager;

    protected function setUp(): void
    {
        $this->attributeService = $this->getContainer()->get('shopware_attribute.crud_service');
        $this->connection = $this->getContainer()->get(Connection::class);
        $this->modelManager = $this->getContainer()->get(ModelManager::class);

        $this->prepareAttributeTables();
        $this->createListenerToFillCartAttribute();
    }

    protected function tearDown(): void
    {
        $this->removeAttributeColumn();
    }

    public function testSyncCartAttributeToOrderDetailAttribute(): void
    {
        $this->preparePaymentMethod();
        $controller = $this->createController();

        $addProductRequest = new Enlight_Controller_Request_RequestHttp();
        $addProductRequest->setMethod(Request::METHOD_POST);
        $addProductRequest->setPost([
            'sAdd' => 'SW10064',
            'sQuantity' => 1,
        ]);
        $controller->setRequest($addProductRequest);
        $controller->Front()->setRequest($addProductRequest);
        $controller->addArticleAction();

        $confirmRequest = new Enlight_Controller_Request_RequestHttp();
        $controller->setRequest($confirmRequest);
        $controller->Front()->setRequest($confirmRequest);
        $controller->confirmAction();

        $cartPositionsConfirmPage = $controller->View()->getAssign('sBasket')['content'];
        static::assertCount(3, $cartPositionsConfirmPage);
        static::assertSame('SW10064', $cartPositionsConfirmPage[0]['ordernumber']);
        static::assertSame('SHIPPINGDISCOUNT', $cartPositionsConfirmPage[1]['ordernumber']);
        static::assertSame('sw-payment', $cartPositionsConfirmPage[2]['ordernumber']);

        $controller->View()->loadTemplate('frontend/checkout/finish.tpl');
        $finishRequest = new Enlight_Controller_Request_RequestHttp([], [
            'sAGB' => true,
        ]);
        $controller->setRequest($finishRequest);
        $controller->Front()->setRequest($finishRequest);
        $controller->finishAction();

        $order = $this->connection->executeQuery(
            'SELECT id, ordernumber
             FROM s_order
             ORDER BY id DESC
             LIMIT 1'
        )->fetchAssociative();
        static::assertIsArray($order);
        static::assertGreaterThan(0, $order['ordernumber']);

        $orderDetailAttributes = $this->connection->executeQuery(
            'SELECT detailAttributes.*
             FROM s_order AS `order`
             INNER JOIN s_order_details details on `order`.id = details.orderID
             INNER JOIN s_order_details_attributes detailAttributes on details.id = detailAttributes.detailID
             WHERE `order`.id = :orderId',
            ['orderId' => $order['id']]
        )->fetchAllAssociative();

        foreach ($orderDetailAttributes as $orderDetailAttribute) {
            static::assertSame(self::TEST_ATTRIBUTE_VALUE, $orderDetailAttribute['test_attr']);
        }

        $this->restorePaymentMethod();
        $orderObject = $this->modelManager->find(Order::class, $order['id']);
        static::assertInstanceOf(Order::class, $orderObject);
        $this->modelManager->remove($orderObject);
        $this->modelManager->flush($orderObject);
    }

    private function prepareAttributeTables(): void
    {
        $this->attributeService->update(
            's_order_basket_attributes',
            self::TEST_ATTRIBUTE_NAME,
            TypeMappingInterface::TYPE_STRING,
            [],
            null,
            true
        );
        $this->modelManager->generateAttributeModels([
            's_order_basket_attributes',
            's_order_details_attributes',
        ]);
    }

    private function createListenerToFillCartAttribute(): void
    {
        $this->getContainer()->get('events')->addListener('Shopware_Modules_Basket_GetBasket_FilterResult',
            function (Enlight_Event_EventArgs $eventArgs) {
                foreach ($eventArgs->getReturn()['content'] as $lineItem) {
                    $this->connection->update(
                        's_order_basket_attributes',
                        [self::TEST_ATTRIBUTE_NAME => self::TEST_ATTRIBUTE_VALUE],
                        ['basketID' => (int) $lineItem['id']],
                    );
                }
            });
    }

    private function createController(): Shopware_Controllers_Frontend_Checkout
    {
        $controller = new Shopware_Controllers_Frontend_Checkout();
        $controller->setContainer($this->getContainer());
        $controller->setResponse(new Enlight_Controller_Response_ResponseTestCase());
        $request = new Enlight_Controller_Request_RequestHttp();
        $controller->setRequest($request);
        $controller->setView(new Enlight_View_Default(new Enlight_Template_Manager()));

        $controller->Front()->setRequest($request);
        $this->getContainer()->get('request_stack')->push($request);
        $this->loginCustomer(null, self::CUSTOMER_ID);

        $controller->init();
        $controller->preDispatch();

        return $controller;
    }

    private function removeAttributeColumn(): void
    {
        $this->attributeService->delete('s_order_basket_attributes', self::TEST_ATTRIBUTE_NAME, true);
        $this->modelManager->generateAttributeModels([
            's_order_basket_attributes',
            's_order_details_attributes',
        ]);
    }

    private function preparePaymentMethod(): void
    {
        $this->connection->update('s_core_paymentmeans', ['debit_percent' => 10], ['name' => 'prepayment']);
    }

    private function restorePaymentMethod(): void
    {
        $this->connection->update('s_core_paymentmeans', ['debit_percent' => 0], ['name' => 'prepayment']);
    }
}
