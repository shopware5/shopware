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

namespace Shopware\Tests\Functional\Controllers\Backend;

use DateTime;
use DateTimeInterface;
use DateTimeZone;
use Doctrine\DBAL\Connection;
use Enlight_Components_Test_Controller_TestCase as ControllerTestCase;
use Exception;
use Shopware\Components\Model\ModelManager;
use Shopware\Models\Order\Detail;
use Shopware\Models\Order\Order;
use Shopware\Models\Shop\Locale;
use Shopware\Models\Shop\Shop;
use Shopware\Models\Tax\Tax;
use Shopware\Tests\Functional\Traits\ContainerTrait;
use Shopware\Tests\Functional\Traits\DatabaseTransactionBehaviour;
use Shopware_Plugins_Backend_Auth_Bootstrap as AuthPlugin;
use Symfony\Component\HttpFoundation\Request;

class OrderTest extends ControllerTestCase
{
    use ContainerTrait;
    use DatabaseTransactionBehaviour;

    private const TAX_RULE_ID = 9999;
    private const ORDER_DETAIL_ID = 6789;
    private const ORDER_ID = 12345;
    private const CUSTOMER_ID = 9999;
    private const NEW_TAX_ID = 1234;

    private Connection $connection;

    private AuthPlugin $authPlugin;

    private ModelManager $modelManager;

    protected function setUp(): void
    {
        $this->connection = $this->getContainer()->get(Connection::class);
        $this->authPlugin = $this->getContainer()->get('plugins')->Backend()->Auth();
        $this->modelManager = $this->getContainer()->get(ModelManager::class);
    }

    /**
     * Test to delete the position
     *
     * @ticket SW-6513
     */
    public function testDeletePosition(): void
    {
        // Insert test data
        $sql = "
              INSERT IGNORE INTO `s_order` (`id`, `ordernumber`, `userID`, `invoice_amount`, `invoice_amount_net`, `invoice_shipping`, `invoice_shipping_net`, `ordertime`, `status`, `cleared`, `paymentID`, `transactionID`, `comment`, `customercomment`, `internalcomment`, `net`, `taxfree`, `partnerID`, `temporaryID`, `referer`, `cleareddate`, `trackingcode`, `language`, `dispatchID`, `currency`, `currencyFactor`, `subshopID`, `remote_addr`) VALUES
              (:orderId, '29996', 1, 126.82, 106.57, 3.9, 3.28, '2013-07-10 08:17:20', 0, 17, 5, '', '', '', '', 0, 0, '', '', '', NULL, '', '1', 9, 'EUR', 1, 1, '172.16.10.71');

              INSERT IGNORE INTO `s_order_details` (`id`, `orderID`, `ordernumber`, `articleID`, `articleordernumber`, `price`, `quantity`, `name`, `status`, `shipped`, `shippedgroup`, `releasedate`, `modus`, `esdarticle`, `taxID`, `tax_rate`, `config`) VALUES
              (15315352, :orderId, '20003', 178, 'SW10178', 19.95, 1, 'Strandtuch Ibiza', 0, 0, 0, NULL, 0, 0, 1, 19, ''),
              (15315353, :orderId, '20003', 177, 'SW10177', 34.99, 1, 'Strandtuch Stripes für Kinder', 0, 0, 0, NULL, 0, 0, 1, 19, ''),
              (15315354, :orderId, '20003', 173, 'SW10173', 39.99, 1, 'Strandkleid Flower Power', 0, 0, 0, NULL, 0, 0, 1, 19, ''),
              (15315355, :orderId, '20003', 160, 'SW10160.1', 29.99, 1, 'Sommer Sandale Ocean Blue 36', 0, 0, 0, NULL, 0, 0, 1, 19, ''),
              (15315356, :orderId, '20003', 0, 'SHIPPINGDISCOUNT', -2, 1, 'Warenkorbrabatt', 0, 0, 0, NULL, 4, 0, 0, 19, '');
        ";
        $this->connection->executeStatement($sql, ['orderId' => '15315351']);

        static::assertSame(126.82, $this->getInvoiceAmount());
        $this->authPlugin->setNoAuth();

        // Delete the order position
        $this->Request()
                ->setMethod(Request::METHOD_POST)
                ->setPost('id', '15315352')
                ->setPost('orderID', '15315351');
        $this->dispatch('backend/Order/deletePosition');
        static::assertSame(106.87, $this->getInvoiceAmount());
    }

    /**
     * Batch create documents for an order. Checks that the number of shops is not changed
     *
     * @ticket SW-7670
     */
    public function testBatchProcessOrderDocument(): void
    {
        // Insert test data
        $orderId = $this->connection->fetchOne('SELECT id FROM s_order WHERE ordernumber = 20001');

        $postData = $this->getPostData();
        $initialShopCount = $this->connection->fetchOne('SELECT count(distinct id) FROM s_core_shops');
        $documents = $this->connection->fetchAllAssociative(
            'SELECT * FROM `s_order_documents` WHERE `orderID` = :orderID',
            ['orderID' => $orderId]
        );

        static::assertCount(0, $documents);

        $this->Request()
            ->setMethod(Request::METHOD_POST)
            ->setPost($postData);

        $response = $this->dispatch('backend/Order/batchProcess')->getBody();
        static::assertIsString($response);
        $data = json_decode($response, true);
        static::assertArrayHasKey('success', $data);
        static::assertTrue($data['success']);

        $finalShopCount = $this->connection->fetchOne('SELECT count(distinct id) FROM s_core_shops');
        static::assertSame($initialShopCount, $finalShopCount);

        $documents = $this->connection->fetchAllAssociative(
            'SELECT * FROM `s_order_documents` WHERE `orderID` = :orderID',
            ['orderID' => $orderId]
        );

        static::assertCount(1, $documents);
    }

    /**
     * Tests whether an order cannot be overwritten by a save request that bases on outdated data. (The order in the
     * database is newer than that one the request body is based on.)
     */
    public function testSaveOrderOverwriteProtection(): void
    {
        // Prepare data for the test
        $orderId = 16548453;
        $this->connection->executeStatement(
            "INSERT IGNORE INTO `s_order` (`id`, `ordernumber`, `userID`, `invoice_amount`, `invoice_amount_net`, `invoice_shipping`, `invoice_shipping_net`, `ordertime`, `status`, `cleared`, `paymentID`, `transactionID`, `comment`, `customercomment`, `internalcomment`, `net`, `taxfree`, `partnerID`, `temporaryID`, `referer`, `cleareddate`, `trackingcode`, `language`, `dispatchID`, `currency`, `currencyFactor`, `subshopID`, `remote_addr`, `changed`) VALUES
            (:orderId, '29996', 1, 126.82, 106.57, 3.9, 3.28, '2013-07-10 08:17:20', 0, 17, 5, '', '', '', '', 0, 0, '', '', '', NULL, '', '1', 9, 'EUR', 1, 1, '172.16.10.71', NOW());

            INSERT IGNORE INTO `s_order_details` (`id`, `orderID`, `ordernumber`, `articleID`, `articleordernumber`, `price`, `quantity`, `name`, `status`, `shipped`, `shippedgroup`, `releasedate`, `modus`, `esdarticle`, `taxID`, `tax_rate`, `config`) VALUES
            (16548454, :orderId, '20003', 178, 'SW10178', 19.95, 1, 'Strandtuch Ibiza', 0, 0, 0, NULL, 0, 0, 1, 19, '')",
            ['orderId' => $orderId]
        );

        // Prepare post data for request
        $order = $this->modelManager->find(Order::class, $orderId);
        static::assertInstanceOf(Order::class, $order);
        $changeDate = $order->getChanged();
        $postData = [
            'id' => 16548453,
            'changed' => $changeDate->format('c'),
            'invoice_amount' => 100,
            'billing' => [
                0 => [],
            ],
            'shipping' => [
                0 => [],
            ],
        ];

        // Try to change the entity with the correct timestamp. This should work
        $this->Request()->setMethod(Request::METHOD_POST)->setPost($postData);
        $this->dispatch('backend/Order/save');
        static::assertTrue($this->View()->getAssign('success'));

        // Now use an outdated timestamp. The controller should detect this and fail.
        $postData['changed'] = '2008-08-07 18:11:31';
        $this->Request()->setMethod(Request::METHOD_POST)->setPost($postData);
        $this->dispatch('backend/Order/batchProcess');
        static::assertFalse($this->View()->getAssign('success'));
    }

    public function testSavingOrderWithDifferentTimeZone(): void
    {
        $this->dispatch('/backend/order/getList');
        $data = $this->View()->getAssign('data')[0];

        $this->reset();
        $this->authPlugin = $this->getContainer()->get('plugins')->Backend()->Auth();
        $this->authPlugin->setNoAuth();

        $orderTime = $data['orderTime'];
        $oldDate = clone $orderTime;
        $orderTime->setTimezone(new DateTimeZone('US/Alaska'));

        $data['orderTime'] = new DateTime($orderTime->format(DateTime::ATOM));
        if (isset($data['changed'])) {
            $data['changed'] = $data['changed']->format('Y-m-d H:i:s');
        }

        $data['billing'] = [$data['billing']];
        $data['shipping'] = [$data['shipping']];
        $data['languageSubShop'] = $this->modelManager->find(Shop::class, $data['languageSubShop']['id']);

        $this->Request()->setParams($data);

        $this->dispatch('/backend/order/save');

        static::assertTrue($this->View()->getAssign('success'));
        static::assertSame($oldDate->getTimestamp(), $this->View()->getAssign('data')['orderTime']->getTimestamp());
    }

    public function testSavePositionActionWithTaxRule(): void
    {
        $this->prepareTestSavePositionActionWithTaxRule();

        $order = $this->modelManager->find(Order::class, self::ORDER_ID);
        static::assertInstanceOf(Order::class, $order);

        $this->Request()->setMethod(Request::METHOD_POST);
        $this->Request()->setPost([
            'id' => 0,
            'orderId' => self::ORDER_ID,
            'mode' => 0,
            'articleId' => 9,
            'articleDetailId' => null,
            'articleNumber' => 'SW10009',
            'articleName' => 'Special Finish Lagerkorn X.O. 32%',
            'quantity' => 1,
            'statusId' => 0,
            'statusDescription' => '',
            'price' => 28.99,
            'taxId' => 1,
            'taxRate' => 0,
            'taxDescription' => '',
            'inStock' => 0,
            'total' => 28.99,
            'changed' => $order->getChanged()->format(DateTimeInterface::ISO8601),
        ]);

        $this->authPlugin->setNoAuth();
        $this->dispatch('/backend/order/savePosition');

        $order = $this->modelManager->find(Order::class, self::ORDER_ID);
        static::assertInstanceOf(Order::class, $order);

        $expectedTaxRate = (float) $this->connection->executeQuery('SELECT tax FROM s_core_tax_rules WHERE id = :id', ['id' => self::TAX_RULE_ID])->fetchOne();

        $data = $this->View()->getAssign('data');
        static::assertTrue($this->View()->getAssign('success'), (string) $this->View()->getAssign('message'));

        static::assertSame($expectedTaxRate, $data['taxRate']);
    }

    public function testSavePositionActionWithDifferentTax(): void
    {
        $this->prepareTestSavePositionActionWithDifferentTax();

        $order = $this->modelManager->find(Order::class, self::ORDER_ID);
        static::assertInstanceOf(Order::class, $order);

        $this->Request()->setMethod(Request::METHOD_POST);
        $this->Request()->setPost([
            'id' => self::ORDER_DETAIL_ID,
            'orderId' => self::ORDER_ID,
            'mode' => 0,
            'articleId' => 141,
            'articleDetailId' => null,
            'articleNumber' => 'SW10141',
            'articleName' => 'Fahrerhandschuh aus Peccary Leder',
            'quantity' => 1,
            'statusId' => 0,
            'statusDescription' => '',
            'price' => 70.58,
            'taxId' => self::NEW_TAX_ID,
            'taxRate' => 0,
            'taxDescription' => '',
            'inStock' => 0,
            'total' => 70.58,
            'changed' => $order->getChanged()->format(DateTimeInterface::ISO8601),
        ]);

        $this->authPlugin->setNoAuth();
        $this->dispatch('/backend/order/savePosition');

        $order = $this->modelManager->find(Order::class, self::ORDER_ID);
        static::assertInstanceOf(Order::class, $order);
        $firstDetail = $order->getDetails()->first();
        static::assertInstanceOf(Detail::class, $firstDetail);
        static::assertSame(self::ORDER_DETAIL_ID, $firstDetail->getId());
        $tax = $firstDetail->getTax();
        static::assertInstanceOf(Tax::class, $tax);

        static::assertSame(self::NEW_TAX_ID, $tax->getId());
    }

    /**
     * Test the list of orders the corresponding translations
     */
    public function testTranslationOfOrderList(): void
    {
        // Set up
        $this->getContainer()->reset('translation');

        // Login
        $this->Request()->setMethod(Request::METHOD_POST);
        $this->Request()->setPost(
            [
                'username' => 'demo',
                'password' => 'demo',
            ]
        );
        $this->dispatch('backend/Login/login');

        $getParams = [
            '_dc' => '1234567890',
            'page' => '1',
            'start' => '0',
            'limit' => '20',
        ];

        $this->reset();

        // Check for English translations
        $user = $this->getContainer()->get('auth')->getIdentity();
        $user->locale = $this->modelManager->getRepository(
            Locale::class
        )->find(2);

        $this->Request()->setMethod(Request::METHOD_GET);
        $getString = http_build_query($getParams);
        $response = $this->dispatch('backend/Order/getList?' . $getString)->getBody();
        static::assertIsString($response);

        // Remove 'new Date(...)' strings from json
        $responseStr = preg_replace('/(new Date\([-0-9]+\))/', '"$1"', $response);
        static::assertIsString($responseStr);

        $responseJSON = json_decode($responseStr, true);
        static::assertTrue($responseJSON['success']);

        foreach ($responseJSON['data'] as $dataElement) {
            switch ($dataElement['payment']['id']) {
                case 2:
                    static::assertSame(
                        'Debit',
                        $dataElement['payment']['description']
                    );
                    break;
                case 3:
                    static::assertSame(
                        'Cash on delivery',
                        $dataElement['payment']['description']
                    );
                    break;
                case 4:
                    static::assertSame(
                        'Invoice',
                        $dataElement['payment']['description']
                    );
                    break;
                case 5:
                    static::assertSame(
                        'Paid in advance',
                        $dataElement['payment']['description']
                    );
                    break;
                case 6:
                    static::assertSame(
                        'SEPA',
                        $dataElement['payment']['description']
                    );
                    break;
            }
        }
    }

    /**
     * Tests whether the order controller returns all data stores with their translations
     */
    public function testTranslationOfStores(): void
    {
        // login
        $this->Request()->setMethod(Request::METHOD_POST);
        $this->Request()->setPost(
            [
                'username' => 'demo',
                'password' => 'demo',
            ]
        );
        $this->dispatch('backend/Login/login');

        $getParams = [
            '_dc' => '1234567890',
            'orderId' => '1',
        ];

        $this->reset();

        // Check for English translations
        $user = $this->getContainer()->get('auth')->getIdentity();
        $user->locale = $this->modelManager->getRepository(
            Locale::class
        )->find(2);

        $this->Request()->setMethod(Request::METHOD_GET);
        $getString = http_build_query($getParams);
        $response = $this->dispatch('backend/Order/loadStores?' . $getString)->getBody();
        static::assertIsString($response);

        $responseJSON = json_decode($response, true);
        static::assertTrue($responseJSON['success']);
        $data = $responseJSON['data'];

        // Test for fallback value
        $this->assertElementWithKeyValuePairExists('SEPA', 'description', 6, $data['payments']);

        // Test for translation
        $this->assertElementWithKeyValuePairExists('Invoice', 'description', 4, $data['payments']);

        $this->assertElementWithKeyValuePairExists('Standard delivery', 'name', 9, $data['dispatches']);
        $this->assertElementWithKeyValuePairExists('Express Delivery', 'name', 14, $data['dispatches']);
        $this->assertElementWithKeyValuePairExists('Standard international delivery', 'name', 16, $data['dispatches']);

        $this->assertElementWithKeyValuePairExists('Invoice', 'name', 1, $data['documentTypes']);
        $this->assertElementWithKeyValuePairExists('Credit', 'name', 3, $data['documentTypes']);
    }

    public function testFilterOrdersBySupplier(): void
    {
        $sql = "
            INSERT IGNORE INTO `s_articles_supplier` (`id`, `name`, `img`, `link`, `changed`) VALUES (:supplierId, 'TestSupplier', '', '', '2019-12-09 10:42:10');

            INSERT INTO `s_articles` (`id`, `supplierID`, `name`, `datum`, `taxID`, `changetime`, `pricegroupID`, `pricegroupActive`, `filtergroupID`, `laststock`, `crossbundlelook`, `notification`, `template`, `mode`) VALUES
            (:productId, :supplierId, 'SwagTest', '2020-03-20', '1', '2020-03-20 10:42:10', NULL, '0', NULL, '0', '0', '0', '', '0');

            INSERT IGNORE INTO `s_order` (`id`, `ordernumber`, `userID`, `invoice_amount`, `invoice_amount_net`, `invoice_shipping`, `invoice_shipping_net`, `ordertime`, `status`, `cleared`, `paymentID`, `transactionID`, `comment`, `customercomment`, `internalcomment`, `net`, `taxfree`, `partnerID`, `temporaryID`, `referer`, `cleareddate`, `trackingcode`, `language`, `dispatchID`, `currency`, `currencyFactor`, `subshopID`, `remote_addr`) VALUES
            (:orderId, '29996', 1, 126.82, 106.57, 3.9, 3.28, '2013-07-10 08:17:20', 0, 17, 5, '', '', '', '', 0, 0, '', '', '', NULL, '', '1', 9, 'EUR', 1, 1, '172.16.10.71');

            INSERT IGNORE INTO `s_order_details` (`id`, `orderID`, `ordernumber`, `articleID`, `articleordernumber`, `price`, `quantity`, `name`, `status`, `shipped`, `shippedgroup`, `releasedate`, `modus`, `esdarticle`, `taxID`, `tax_rate`, `config`) VALUES
            (15315352, :orderId, '20003', :productId, 'SW10178', 19.95, 1, 'Strandtuch Ibiza', 0, 0, 0, '0000-00-00', 0, 0, 1, 19, ''),
            (15315353, :orderId, '20003', 177, 'SW10177', 34.99, 1, 'Strandtuch Stripes für Kinder', 0, 0, 0, '0000-00-00', 0, 0, 1, 19, ''),
            (15315354, :orderId, '20003', 173, 'SW10173', 39.99, 1, 'Strandkleid Flower Power', 0, 0, 0, '0000-00-00', 0, 0, 1, 19, ''),
            (15315355, :orderId, '20003', 160, 'SW10160.1', 29.99, 1, 'Sommer Sandale Ocean Blue 36', 0, 0, 0, '0000-00-00', 0, 0, 1, 19, ''),
            (15315356, :orderId, '20003', 0, 'SHIPPINGDISCOUNT', -2, 1, 'Warenkorbrabatt', 0, 0, 0, '0000-00-00', 4, 0, 0, 19, '');
        ";

        $supplierId = '81729';
        $productId = 91829002;
        $orderId = 15315351;
        $this->connection->executeStatement($sql, ['orderId' => $orderId, 'productId' => $productId, 'supplierId' => $supplierId]);

        $this->authPlugin->setNoAuth();
        // prevent breaking date formatting
        $this->getContainer()->get('front')->Plugins()->Json()->setFormatDateTime(false);

        $filter = [[
            'property' => 'article.supplierId',
            'operator' => null,
            'expression' => null,
            'value' => $supplierId,
        ]];
        $this->Request()
            ->setMethod(Request::METHOD_GET)
            ->setPost('filter', json_encode($filter));
        $response = $this->dispatch('backend/Order/getList')->getBody();
        static::assertIsString($response);
        $data = json_decode($response, true);

        static::assertIsArray($data);
        static::assertTrue($data['success']);
        static::assertSame(1, $data['total']);
        static::assertCount($data['total'], $data['data']);

        $order = $data['data'][0];
        static::assertSame($orderId, $order['id']);
        static::assertCount(5, $order['details']);

        $orderProductIds = array_column($order['details'], 'articleId');
        static::assertContains($productId, $orderProductIds);
    }

    /**
     * Tests a data array for an entry with a specified key value pair
     *
     * @param array<array<string, mixed>> $data
     *
     * @throws Exception
     */
    private function assertElementWithKeyValuePairExists(string $expected, string $dataKey, int $id, array $data): void
    {
        foreach ($data as $entry) {
            if ($entry['id'] === $id) {
                static::assertSame($expected, $entry[$dataKey]);

                return;
            }
        }

        throw new Exception('Entry not found');
    }

    /**
     * Helper method to return the order amount
     */
    private function getInvoiceAmount(): float
    {
        $sql = 'SELECT invoice_amount FROM s_order WHERE id = ?';

        return (float) $this->connection->fetchOne($sql, ['15315351']);
    }

    /**
     * @return array<string, mixed>
     */
    private function getPostData(): array
    {
        return [
            'module' => 'backend',
            'controller' => 'Order',
            'action' => 'batchProcess',
            'targetField' => 'orders',
            '_dc' => '1391161752595',
            'docType' => '1',
            'mode' => '',
            'forceTaxCheck' => '1',
            'displayDate' => '2014-01-31T10:49:12',
            'deliveryDate' => '2014-01-31T10:49:12',
            'autoSend' => 'true',
            'id' => 15,
            'number' => '20001',
            'customerId' => 2,
            'invoiceAmountNet' => 839.13,
            'invoiceShippingNet' => 0,
            'status' => 0,
            'cleared' => 17,
            'paymentId' => 4,
            'transactionId' => '',
            'comment' => '',
            'customerComment' => '',
            'internalComment' => '',
            'net' => 1,
            'taxFree' => 0,
            'partnerId' => '',
            'temporaryId' => '',
            'referer' => '',
            'clearedDate' => '',
            'trackingCode' => '',
            'languageIso' => '1',
            'dispatchId' => 9,
            'currency' => 'EUR',
            'currencyFactor' => 1,
            'shopId' => 1,
            'remoteAddress' => '217.86.205.141',
            'invoiceAmount' => 998.56,
            'invoiceShipping' => 0,
            'orderTime' => '2012-08-30T16:15:54',
            'invoiceShippingEuro' => 0,
            'invoiceAmountEuro' => 998.56,
            'remoteAddressConverted' => '217.86.205.xxx',
            'customer' => [
                0 => [
                    'id' => 2,
                    'groupKey' => 'H',
                    'email' => 'mustermann@b2b.de',
                    'active' => true,
                    'accountMode' => 0,
                    'confirmationKey' => '',
                    'paymentId' => 4,
                    'firstLogin' => '2012-08-30T00:00:00',
                    'lastLogin' => '2012-08-30T11:43:17',
                    'newsletter' => 0,
                    'validation' => 0,
                    'languageId' => 0,
                    'shopId' => 1,
                    'priceGroupId' => 0,
                    'internalComment' => '',
                    'failedLogins' => 0,
                    'referer' => '',
                ],
            ],
            'shop' => [
                0 => [
                    'id' => 1,
                    'default' => true,
                    'localeId' => 0,
                    'categoryId' => 3,
                    'name' => 'Deutsch',
                ],
            ],
            'dispatch' => [
                0 => [
                    'id' => 9,
                    'name' => 'Standard Versand',
                    'type' => 0,
                    'comment' => '',
                    'active' => null,
                    'position' => 1,
                ],
            ],
            'paymentStatus' => [
                0 => [
                    'id' => 17,
                    'description' => 'Open',
                ],
            ],
            'orderStatus' => [
                0 => [
                    'id' => 0,
                    'description' => 'Open',
                ],
            ],
            'locale' => [
                0 => [
                    'id' => 1,
                    'language' => 'Deutsch',
                    'territory' => 'Deutschland',
                    'locale' => 'de_DE',
                    'name' => 'Deutsch (Deutschland)',
                ],
            ],
            'attribute' => [
                0 => [
                    'id' => 1,
                    'orderId' => 15,
                    'attribute1' => '',
                    'attribute2' => '',
                    'attribute3' => '',
                    'attribute4' => '',
                    'attribute5' => '',
                    'attribute6' => '',
                ],
            ],
            'billing' => [
                0 => [
                    'id' => 1,
                    'salutation' => 'company',
                    'company' => 'B2B',
                    'department' => 'Einkauf',
                    'firstName' => 'Händler',
                    'lastName' => 'Kundengruppe-Netto',
                    'street' => 'Musterweg 1',
                    'zipCode' => '00000',
                    'city' => 'Musterstadt',
                    'countryId' => 2,
                    'number' => '',
                    'phone' => '012345 / 6789',
                    'vatId' => '',
                    'orderId' => 15,
                    'shopware.apps.order.model.order' => [],
                ],
            ],
            'shipping' => [
                0 => [
                    'id' => 1,
                    'salutation' => 'company',
                    'company' => 'B2B',
                    'department' => 'Einkauf',
                    'firstName' => 'Händler',
                    'lastName' => 'Kundengruppe-Netto',
                    'street' => 'Musterweg 1',
                    'zipCode' => '00000',
                    'city' => 'Musterstadt',
                    'countryId' => 2,
                    'orderId' => 15,
                    'shopware.apps.order.model.order' => [],
                ],
            ],
            'debit' => [
                0 => [
                    'id' => 3,
                    'customerId' => 2,
                    'account' => '',
                    'bankCode' => '',
                    'bankName' => '',
                    'accountHolder' => '',
                ],
            ],
            'payment' => [
                0 => [
                    'id' => 4,
                    'name' => 'invoice',
                    'position' => 3,
                    'active' => null,
                    'description' => 'Invoice',
                    'shopware.apps.order.model.order' => [],
                ],
            ],
            'paymentInstances' => [],
            'documents' => [
                0 => [
                    'id' => 1,
                    'date' => '2014-01-31T00:00:00',
                    'typeId' => 1,
                    'customerId' => 2,
                    'orderId' => 15,
                    'amount' => 998.56,
                    'documentId' => 20001,
                    'hash' => '02e8b8abfca501b3f9df6791750d04bd',
                    'typeName' => '',
                    'type' => [
                        0 => [
                            'id' => 1,
                            'template' => 'index.tpl',
                            'numbers' => 'doc_0',
                            'left' => 25,
                            'right' => 10,
                            'top' => 20,
                            'bottom' => 20,
                            'pageBreak' => 10,
                            'name' => 'Invoice',
                        ],
                    ],
                    'attributes' => [],
                ],
            ],
            'details' => [
                0 => [
                    'id' => 42,
                    'orderId' => 15,
                    'mode' => 0,
                    'articleId' => 197,
                    'articleNumber' => 'SW10196',
                    'articleName' => 'ESD Download Artikel',
                    'quantity' => 1,
                    'statusId' => 0,
                    'statusDescription' => '',
                    'price' => 836.134,
                    'taxId' => 1,
                    'taxRate' => 19,
                    'taxDescription' => '',
                    'inStock' => 1,
                    'total' => 836.134,
                    'attribute' => [
                        0 => [
                            'id' => 1,
                            'orderDetailId' => 42,
                            'attribute1' => '',
                            'attribute2' => '',
                            'attribute3' => '',
                            'attribute4' => '',
                            'attribute5' => '',
                            'attribute6' => '',
                        ],
                    ],
                ],
                1 => [
                    'id' => 43,
                    'orderId' => 15,
                    'mode' => 4,
                    'articleId' => 0,
                    'articleNumber' => 'SHIPPINGDISCOUNT',
                    'articleName' => 'Warenkorbrabatt',
                    'quantity' => 1,
                    'statusId' => 0,
                    'statusDescription' => '',
                    'price' => -2,
                    'taxId' => 0,
                    'taxRate' => 19,
                    'taxDescription' => '',
                    'inStock' => 0,
                    'total' => -2,
                    'attribute' => [
                        0 => [
                            'id' => 2,
                            'orderDetailId' => 43,
                            'attribute1' => '',
                            'attribute2' => '',
                            'attribute3' => '',
                            'attribute4' => '',
                            'attribute5' => '',
                            'attribute6' => '',
                        ],
                    ],
                ],
                2 => [
                    'id' => 44,
                    'orderId' => 15,
                    'mode' => 4,
                    'articleId' => 0,
                    'articleNumber' => 'sw-payment-absolute',
                    'articleName' => 'Zuschlag für Zahlungsart',
                    'quantity' => 1,
                    'statusId' => 0,
                    'statusDescription' => '',
                    'price' => 5,
                    'taxId' => 0,
                    'taxRate' => 19,
                    'taxDescription' => '',
                    'inStock' => 0,
                    'total' => 5,
                    'attribute' => [
                        0 => [
                            'id' => 3,
                            'orderDetailId' => 44,
                            'attribute1' => '',
                            'attribute2' => '',
                            'attribute3' => '',
                            'attribute4' => '',
                            'attribute5' => '',
                            'attribute6' => '',
                        ],
                    ],
                ],
            ],
            'mail' => [],
            'billingAttribute' => [
                0 => [
                    'id' => 1,
                    'orderBillingId' => 1,
                    'text1' => null,
                    'text2' => null,
                    'text3' => null,
                    'text4' => null,
                    'text5' => null,
                    'text6' => null,
                ],
            ],
            'shippingAttribute' => [
                0 => [
                    'id' => 1,
                    'orderShippingId' => 1,
                    'text1' => null,
                    'text2' => null,
                    'text3' => null,
                    'text4' => null,
                    'text5' => null,
                    'text6' => null,
                ],
            ],
        ];
    }

    private function prepareTestSavePositionActionWithTaxRule(): void
    {
        $taxRuleSQL = file_get_contents(__DIR__ . '/_fixtures/order/tax_rules.sql');
        static::assertIsString($taxRuleSQL);
        $this->connection->executeStatement($taxRuleSQL, ['taxRuleId' => self::TAX_RULE_ID]);

        $customerSql = file_get_contents(__DIR__ . '/_fixtures/order/customer.sql');
        static::assertIsString($customerSql);
        $this->connection->executeStatement($customerSql, ['customerId' => self::CUSTOMER_ID]);

        $orderSql = file_get_contents(__DIR__ . '/_fixtures/order/order.sql');
        static::assertIsString($orderSql);
        $this->connection->executeStatement($orderSql, [
            'orderDetailId' => self::ORDER_DETAIL_ID,
            'orderId' => self::ORDER_ID,
            'customerId' => self::CUSTOMER_ID,
        ]);
    }

    private function prepareTestSavePositionActionWithDifferentTax(): void
    {
        $taxRuleSQL = file_get_contents(__DIR__ . '/_fixtures/order/new_tax.sql');
        static::assertIsString($taxRuleSQL);
        $this->connection->executeStatement($taxRuleSQL, ['taxRuleId' => self::NEW_TAX_ID]);

        $customerSql = file_get_contents(__DIR__ . '/_fixtures/order/customer.sql');
        static::assertIsString($customerSql);
        $this->connection->executeStatement($customerSql, ['customerId' => self::CUSTOMER_ID]);

        $orderSql = file_get_contents(__DIR__ . '/_fixtures/order/order.sql');
        static::assertIsString($orderSql);
        $this->connection->executeStatement($orderSql, [
            'orderDetailId' => self::ORDER_DETAIL_ID,
            'orderId' => self::ORDER_ID,
            'customerId' => self::CUSTOMER_ID,
        ]);
    }
}
