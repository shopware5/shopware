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
use Doctrine\DBAL\Connection;
use Enlight_Components_Test_Controller_TestCase as ControllerTestCase;
use Shopware\Components\ContainerAwareEventManager;
use Shopware\Components\Model\ModelManager;
use Shopware\Components\ShopRegistrationServiceInterface;
use Shopware\Models\Analytics\Repository;
use Shopware\Models\Shop\Shop;
use Shopware\Tests\Functional\Traits\ContainerTrait;
use Shopware\Tests\Functional\Traits\DatabaseTransactionBehaviour;

class AnalyticsTest extends ControllerTestCase
{
    use DatabaseTransactionBehaviour;
    use ContainerTrait;

    private Connection $connection;

    private Repository $repository;

    private int $userId;

    private string $customerNumber;

    private ?int $productId = null;

    private ?int $categoryId = null;

    private string $orderNumber;

    private ?int $productVariantId = null;

    public function setUp(): void
    {
        parent::setUp();

        // disable auth and acl
        $this->getContainer()->get('plugins')->Backend()->Auth()->setNoAuth();
        $this->getContainer()->get('plugins')->Backend()->Auth()->setNoAcl();

        $this->connection = $this->getContainer()->get(Connection::class);
        $this->repository = new Repository($this->connection, $this->getContainer()->get(ContainerAwareEventManager::class));

        $this->orderNumber = uniqid('SW');
        $this->productId = 0;
        $this->userId = 0;
    }

    public function tearDown(): void
    {
        $this->removeDemoData();
        parent::tearDown();
    }

    public function testGetVisitorImpressions(): void
    {
        $this->createVisitors();

        $result = $this->repository->getVisitorImpressions(
            0,
            25,
            new DateTime('2013-01-01'),
            new DateTime('2014-01-01'),
            [
                [
                    'property' => 'datum',
                    'direction' => 'ASC',
                ],
            ],
            [1]
        );

        static::assertEquals(
            [
                [
                    'datum' => '2013-06-01',
                    'desktopImpressions' => 300,
                    'tabletImpressions' => 0,
                    'mobileImpressions' => 0,
                    'totalImpressions' => 300,
                    'desktopVisits' => 10,
                    'tabletVisits' => 0,
                    'mobileVisits' => 0,
                    'totalVisits' => 10,
                    'desktopImpressions1' => 300,
                    'tabletImpressions1' => 0,
                    'mobileImpressions1' => 0,
                    'totalImpressions1' => 300,
                    'desktopVisits1' => 10,
                    'tabletVisits1' => 0,
                    'mobileVisits1' => 0,
                    'totalVisits1' => 10,
                ],
                [
                    'datum' => '2013-06-15',
                    'desktopImpressions' => 500,
                    'tabletImpressions' => 0,
                    'mobileImpressions' => 0,
                    'totalImpressions' => 500,
                    'desktopVisits' => 20,
                    'tabletVisits' => 0,
                    'mobileVisits' => 0,
                    'totalVisits' => 20,
                    'desktopImpressions1' => 500,
                    'tabletImpressions1' => 0,
                    'mobileImpressions1' => 0,
                    'totalImpressions1' => 500,
                    'desktopVisits1' => 20,
                    'tabletVisits1' => 0,
                    'mobileVisits1' => 0,
                    'totalVisits1' => 20,
                ],
            ],
            $result->getData()
        );
    }

    public function testGetVisitorAsCSV(): void
    {
        $this->createVisitors();

        $this->Request()->setParams([
            'fromDate' => '2013-01-01',
            'toDate' => '2014-01-01',
            'selectedShops' => '1',
            'format' => 'csv',
        ]);

        $this->dispatch('backend/analytics/getVisitors');

        static::assertSame('text/csv; charset=utf-8', $this->Response()->getHeader('Content-Type'));
        static::assertSame('attachment;filename=visitors.csv', $this->Response()->getHeader('content-disposition'));
    }

    public function testGetOrdersOfCustomers(): void
    {
        $this->createCustomer();
        $this->createOrders();

        $result = $this->repository->getOrdersOfCustomers(
            new DateTime('2013-01-01'),
            new DateTime('2014-01-01')
        );

        static::assertEquals(
            $result->getData(),
            [
                [
                    'orderTime' => '2013-06-01',
                    'isNewCustomerOrder' => 1,
                    'salutation' => 'mr',
                    'userId' => $this->userId,
                ],
            ]
        );
    }

    public function testGetReferrerRevenue(): void
    {
        $this->createCustomer();
        $this->createOrders();

        $shop = $this->getContainer()->get(ModelManager::class)->getRepository(Shop::class)->getActiveDefault();
        $this->getContainer()->get(ShopRegistrationServiceInterface::class)->registerShop($shop);

        $result = $this->repository->getReferrerRevenue(
            $shop,
            new DateTime('2013-01-01'),
            new DateTime('2014-01-01')
        );

        static::assertEquals(
            $result->getData(),
            [
                [
                    'turnover' => 1000.00,
                    'userID' => $this->userId,
                    'referrer' => 'https://www.google.de/',
                    'firstLogin' => '2013-06-01',
                    'orderTime' => '2013-06-01',
                ],
            ]
        );
    }

    public function testGetPartnerRevenue(): void
    {
        $this->createCustomer();
        $this->createOrders();

        $result = $this->repository->getPartnerRevenue(
            0,
            25,
            new DateTime('2013-01-01'),
            new DateTime('2014-01-01')
        );

        static::assertEquals(
            [
                [
                    'turnover' => 1000,
                    'partner' => null,
                    'trackingCode' => 'PHPUNIT_PARTNER',
                    'partnerId' => null,
                ],
            ],
            $result->getData()
        );
    }

    public function testGetProductSales(): void
    {
        $this->createCustomer();
        $this->createProduct();
        $this->createOrders();

        $result = $this->repository->getProductSales(
            0,
            25,
            new DateTime('2013-01-01'),
            new DateTime('2014-01-01')
        );

        static::assertEquals(
            $result->getData(),
            [
                [
                    'sales' => 1,
                    'name' => 'PHPUNIT ARTICLE',
                    'ordernumber' => $this->orderNumber,
                ],
            ]
        );
    }

    public function testGetProductImpressions(): void
    {
        $this->createProduct();
        $this->createImpressions();

        $result = $this->repository->getProductImpressions(
            0,
            25,
            new DateTime('2013-01-01'),
            new DateTime('2014-01-01'),
            [
                [
                    'property' => 'articleId',
                    'direction' => 'ASC',
                ],
            ],
            [1]
        );

        static::assertEquals(
            $result->getData(),
            [
                [
                    'articleId' => $this->productId,
                    'articleName' => 'PHPUNIT ARTICLE',
                    'totalImpressions' => 10,
                    'totalImpressions1' => 10,
                    'desktopImpressions' => 10,
                    'tabletImpressions' => 0,
                    'mobileImpressions' => 0,
                ],
            ]
        );
    }

    public function testGetAgeOfCustomers(): void
    {
        $this->createCustomer();

        $result = $this->repository->getAgeOfCustomers(
            new DateTime('2013-01-01'),
            new DateTime('2014-01-01'),
            [1]
        );

        static::assertEquals(
            [
                [
                    'firstLogin' => '2013-06-01',
                    'birthday' => '1990-01-01',
                    'birthday1' => '1990-01-01',
                ],
            ],
            $result->getData()
        );
    }

    public function testGetAmountPerHour(): void
    {
        $this->createCustomer();
        $this->createOrders();

        $result = $this->repository->getAmountPerHour(
            new DateTime('2013-01-01'),
            new DateTime('2014-01-01'),
            [1]
        );

        static::assertEquals(
            [
                [
                    'orderCount' => 1,
                    'orderCount1' => 1,
                    'turnover' => 1000,
                    'turnover1' => 1000,
                    'displayDate' => 'Saturday',
                    'date' => '1970-01-01 10:00:00',
                ],
            ],
            $result->getData()
        );
    }

    public function testGetAmountPerWeekday(): void
    {
        $this->createCustomer();
        $this->createOrders();

        $result = $this->repository->getAmountPerWeekday(
            new DateTime('2013-01-01'),
            new DateTime('2014-01-01')
        );

        static::assertEquals(
            [
                [
                    'orderCount' => 1,
                    'turnover' => 1000,
                    'displayDate' => 'Saturday',
                    'date' => '2013-06-01',
                ],
            ],
            $result->getData()
        );
    }

    public function testGetAmountPerCalendarWeek(): void
    {
        $this->createCustomer();
        $this->createOrders();

        $result = $this->repository->getAmountPerCalendarWeek(
            new DateTime('2013-01-01'),
            new DateTime('2014-01-01')
        );

        static::assertEquals(
            [
                [
                    'orderCount' => 1,
                    'turnover' => 1000,
                    'displayDate' => 'Saturday',
                    'date' => '2013-05-30',
                ],
            ],
            $result->getData()
        );
    }

    public function testGetAmountPerMonth(): void
    {
        $this->createCustomer();
        $this->createOrders();

        $result = $this->repository->getAmountPerMonth(
            new DateTime('2013-01-01'),
            new DateTime('2014-01-01')
        );

        static::assertEquals(
            [
                [
                    'orderCount' => 1,
                    'turnover' => 1000,
                    'displayDate' => 'Saturday',
                    'date' => '2013-06-04',
                ],
            ],
            $result->getData()
        );
    }

    public function testGetCustomerGroupAmount(): void
    {
        $this->createCustomer();
        $this->createOrders();

        $result = $this->repository->getCustomerGroupAmount(
            new DateTime('2013-01-01'),
            new DateTime('2014-01-01')
        );

        static::assertEquals(
            [
                [
                    'orderCount' => 1,
                    'turnover' => 1000,
                    'displayDate' => 'Saturday',
                    'customerGroup' => 'Shopkunden',
                ],
            ],
            $result->getData()
        );
    }

    public function testGetAmountPerCountry(): void
    {
        $this->createCustomer();
        $this->createOrders();

        $result = $this->repository->getAmountPerCountry(
            new DateTime('2013-01-01'),
            new DateTime('2014-01-01')
        );

        static::assertEquals(
            [
                [
                    'orderCount' => 1,
                    'turnover' => 1000,
                    'displayDate' => 'Saturday',
                    'name' => 'Deutschland',
                ],
            ],
            $result->getData()
        );
    }

    public function testGetAmountPerShipping(): void
    {
        $this->createCustomer();
        $this->createOrders();

        $result = $this->repository->getAmountPerShipping(
            new DateTime('2013-01-01'),
            new DateTime('2014-01-01')
        );

        static::assertEquals(
            [
                [
                    'orderCount' => 1,
                    'turnover' => 1000,
                    'displayDate' => 'Saturday',
                    'name' => 'Standard Versand',
                ],
            ],
            $result->getData()
        );
    }

    public function testGetAmountPerPayment(): void
    {
        $this->createCustomer();
        $this->createOrders();

        $result = $this->repository->getAmountPerPayment(
            new DateTime('2013-01-01'),
            new DateTime('2014-01-01')
        );

        static::assertEquals(
            [
                [
                    'orderCount' => 1,
                    'turnover' => 1000,
                    'displayDate' => 'Saturday',
                    'name' => 'Lastschrift',
                ],
            ],
            $result->getData()
        );
    }

    public function testGetSearchTerms(): void
    {
        $this->createSearchTerms();

        $result = $this->repository->getSearchTerms(
            0,
            25,
            new DateTime('2013-01-01'),
            new DateTime('2014-01-01'),
            [
                [
                    'property' => 'countRequests',
                    'direction' => 'ASC',
                ],
            ]
        );

        static::assertEquals(
            [
                [
                    'countRequests' => 1,
                    'searchterm' => 'phpunit search term',
                    'countResults' => 10,
                    'shop' => null,
                ],
            ],
            $result->getData()
        );
    }

    public function testGetDailyVisitors(): void
    {
        $this->createVisitors();

        $result = $this->repository->getDailyVisitors(
            new DateTime('2013-01-01'),
            new DateTime('2014-01-01')
        );

        static::assertEquals(
            [
                '2013-06-15' => [
                    [
                        'clicks' => 500,
                        'visits' => 20,
                    ],
                ],
                '2013-06-01' => [
                    [
                        'clicks' => 300,
                        'visits' => 10,
                    ],
                ],
            ],
            $result->getData()
        );
    }

    public function testGetDailyShopVisitors(): void
    {
        $this->createVisitors();

        $result = $this->repository->getDailyShopVisitors(
            new DateTime('2013-01-01'),
            new DateTime('2014-01-01'),
            [1]
        );

        static::assertEquals(
            [
                '2013-06-15' => [
                    [
                        'clicks' => 500,
                        'visits' => 20,
                        'visits1' => 20,
                    ],
                ],
                '2013-06-01' => [
                    [
                        'clicks' => 300,
                        'visits' => 10,
                        'visits1' => 10,
                    ],
                ],
            ],
            $result->getData()
        );
    }

    public function testGetDailyShopOrders(): void
    {
        $this->createCustomer();
        $this->createOrders();

        $result = $this->repository->getDailyShopOrders(
            new DateTime('2013-01-01'),
            new DateTime('2014-01-01'),
            [1]
        );

        static::assertEquals(
            [
                '2013-06-15' => [
                    [
                        'orderCount' => 0,
                        'orderCount1' => 0,
                        'cancelledOrders' => 1,
                        'cancelledOrders1' => 1,
                    ],
                ],
                '2013-06-01' => [
                    [
                        'orderCount' => 1,
                        'orderCount1' => 1,
                        'cancelledOrders' => 0,
                        'cancelledOrders1' => 0,
                    ],
                ],
            ],
            $result->getData()
        );
    }

    public function testGetDailyRegistrations(): void
    {
        $this->createCustomer();

        $result = $this->repository->getDailyRegistrations(
            new DateTime('2013-01-01'),
            new DateTime('2014-01-01')
        );

        static::assertEquals(
            [
                '2013-06-01' => [
                    [
                        'registrations' => 1,
                        'customers' => 0,
                    ],
                ],
            ],
            $result->getData()
        );
    }

    public function testGetDailyTurnover(): void
    {
        $this->createCustomer();
        $this->createOrders();

        $result = $this->repository->getDailyTurnover(
            new DateTime('2013-01-01'),
            new DateTime('2014-01-01')
        );

        static::assertEquals(
            [
                '2013-06-01' => [
                    [
                        'orderCount' => 1,
                        'turnover' => 1000,
                    ],
                ],
            ],
            $result->getData()
        );
    }

    public function testGetProductAmountPerManufacturer(): void
    {
        $this->createCustomer();
        $this->createProduct();
        $this->createOrders();

        $result = $this->repository->getProductAmountPerManufacturer(
            0,
            25,
            new DateTime('2013-01-01'),
            new DateTime('2014-01-01')
        );

        static::assertEquals(
            [
                [
                    'orderCount' => 1,
                    'turnover' => 1000,
                    'name' => 'shopware AG',
                ],
            ],
            $result->getData()
        );
    }

    public function testGetVisitedReferrer(): void
    {
        $this->createReferrer();

        $result = $this->repository->getVisitedReferrer(
            0,
            25,
            new DateTime('2013-01-01'),
            new DateTime('2014-01-01')
        );

        static::assertEquals(
            [
                [
                    'count' => 1,
                    'referrer' => 'https://www.google.de/?q=phpunit',
                ],
            ],
            $result->getData()
        );
    }

    public function testGetReferrerUrls(): void
    {
        $this->createReferrer();

        $result = $this->repository->getReferrerUrls(
            'phpunit',
            0,
            25
        );

        static::assertEquals(
            [
                [
                    'count' => 1,
                    'referrer' => 'https://www.google.de/?q=phpunit',
                ],
            ],
            $result->getData()
        );
    }

    public function testGetReferrerSearchTerms(): void
    {
        $this->createReferrer();

        $data = $this->repository->getReferrerSearchTerms('phpunit')->getData();

        static::assertEquals(
            [
                [
                    'count' => 1,
                    'referrer' => 'https://www.google.de/?q=phpunit',
                ],
            ],
            $data
        );

        static::assertEquals(
            'phpunit',
            $this->getSearchTermFromReferrerUrl($data[0]['referrer'])
        );
    }

    public function testGetProductAmountPerCategory(): void
    {
        $this->createProduct();
        $this->createCategory();
        $this->createCustomer();
        $this->createOrders();

        $result = $this->repository->getProductAmountPerCategory(
            1,
            new DateTime('2013-01-01'),
            new DateTime('2014-01-01')
        );

        static::assertEquals(
            [
                [
                    'orderCount' => 1,
                    'turnover' => 1000,
                    'name' => 'phpunit category',
                    'node' => '',
                ],
            ],
            $result->getData()
        );
    }

    public function testOrderCurrencyFactor(): void
    {
        $this->createCustomer();
        $this->createOrders();

        $result = $this->repository->getAmountPerHour(
            new DateTime('2014-01-01'),
            new DateTime('2014-02-02')
        );

        static::assertEquals(
            [
                [
                    'orderCount' => 1,
                    'turnover' => 250,
                    'displayDate' => 'Saturday',
                    'date' => '1970-01-01 10:00:00',
                ],
            ],
            $result->getData()
        );
    }

    private function createCustomer(): void
    {
        $this->customerNumber = uniqid((string) rand());

        $this->connection->insert(
            's_user',
            [
                'password' => '098f6bcd4621d373cade4e832627b4f6', // md5('test')
                'encoder' => 'md5',
                'email' => uniqid('test') . '@test.com',
                'active' => '1',
                'firstlogin' => '2013-06-01',
                'lastlogin' => '2013-07-01',
                'subshopID' => '1',
                'customergroup' => 'EK',
                'salutation' => 'mr',
                'firstname' => '',
                'lastname' => '',
                'birthday' => '1990-01-01',
            ]
        );
        $this->userId = (int) $this->connection->lastInsertId();

        $this->connection->insert('s_user_addresses', [
            'user_id' => $this->userId,
            'company' => 'PHPUNIT',
            'salutation' => 'mr',
            'firstname' => '',
            'lastname' => '',
            'zipcode' => '',
            'city' => '',
            'country_id' => 2,
            'state_id' => 3,
        ]);
        $addressId = $this->connection->lastInsertId();

        $this->connection->update('s_user', [
            'default_billing_address_id' => $addressId,
            'default_shipping_address_id' => $addressId,
        ], [
            'id' => $this->userId,
        ]);
    }

    private function createProduct(): void
    {
        $this->connection->insert(
            's_articles',
            [
                'supplierID' => 1,
                'name' => 'PHPUNIT ARTICLE',
                'datum' => '2013-06-01',
                'active' => 1,
                'taxID' => 1,
                'main_detail_id' => 9999,
            ]
        );
        $this->productId = (int) $this->connection->lastInsertId();

        $this->connection->insert(
            's_articles_details',
            [
                'articleID' => $this->productId,
                'ordernumber' => $this->orderNumber,
                'kind' => 1,
                'active' => 1,
                'instock' => 1,
            ]
        );
        $this->productVariantId = (int) $this->connection->lastInsertId();

        $this->connection->update(
            's_articles',
            ['main_detail_id' => $this->productVariantId],
            ['id' => $this->productId]
        );
    }

    private function createCategory(): void
    {
        $this->connection->insert(
            's_categories',
            [
                'description' => 'phpunit category',
                'parent' => 1,
                'active' => 1,
            ]
        );
        $this->categoryId = (int) $this->connection->lastInsertId();

        $this->connection->insert(
            's_articles_categories_ro',
            [
                'articleID' => $this->productId,
                'categoryID' => $this->categoryId,
            ]
        );
    }

    private function createOrders(): void
    {
        $orderIds = [];

        $orders = [
            [
                'userID' => $this->userId,
                'invoice_amount' => '1000',
                'invoice_amount_net' => '840',
                'ordertime' => '2013-06-01 10:11:12',
                'status' => 0,
                'partnerID' => 'PHPUNIT_PARTNER',
                'referer' => 'https://www.google.de/',
                'subshopID' => 1,
                'currencyFactor' => 1,
                'dispatchID' => 9,
                'language' => 1,
                'paymentID' => 2,
            ],
            [
                'userID' => $this->userId,
                'invoice_amount' => '500',
                'invoice_amount_net' => '420',
                'ordertime' => '2013-06-15 10:11:12',
                'status' => '-1',
                'subshopID' => 1,
                'currencyFactor' => 1,
                'dispatchID' => 9,
                'language' => 1,
                'paymentID' => 2,
            ],
            [
                'userID' => $this->userId,
                'invoice_amount' => '500',
                'invoice_amount_net' => '420',
                'ordertime' => '2014-02-01 10:11:12',
                'status' => 0,
                'subshopID' => 1,
                'currencyFactor' => 2,
                'dispatchID' => 9,
                'language' => 1,
                'paymentID' => 2,
            ],
        ];

        foreach ($orders as $order) {
            $this->connection->insert('s_order', $order);
            $orderIds[] = $this->connection->lastInsertId();
        }

        $orderDetails = [
            [
                'orderID' => $orderIds[0],
                'articleID' => $this->productId,
                'articleordernumber' => $this->orderNumber,
                'price' => 1000,
                'quantity' => 1,
                'modus' => 0,
                'taxID' => 1,
                'tax_rate' => 19,
            ],
            [
                'orderID' => $orderIds[1],
                'articleID' => $this->productId,
                'articleordernumber' => $this->orderNumber,
                'price' => 1000,
                'quantity' => 1,
                'modus' => 0,
                'taxID' => 1,
                'tax_rate' => 19,
            ],
        ];
        foreach ($orderDetails as $detail) {
            $this->connection->insert('s_order_details', $detail);
        }

        $userBillingAddress = [
            'company' => 'PHPUNIT',
            'salutation' => 'mr',
            'countryID' => 2,
            'stateID' => 3,
        ];

        $orderBillingAddresses = [
            [
                'userID' => $this->userId,
                'orderID' => $orderIds[0],
                'company' => $userBillingAddress['company'],
                'salutation' => $userBillingAddress['salutation'],
                'customernumber' => $this->customerNumber,
                'countryID' => $userBillingAddress['countryID'],
                'stateID' => $userBillingAddress['stateID'],
            ],
            [
                'userID' => $this->userId,
                'orderID' => $orderIds[1],
                'company' => $userBillingAddress['company'],
                'salutation' => $userBillingAddress['salutation'],
                'customernumber' => $this->customerNumber,
                'countryID' => $userBillingAddress['countryID'],
                'stateID' => $userBillingAddress['stateID'],
            ],
            [
                'userID' => $this->userId,
                'orderID' => $orderIds[2],
                'company' => $userBillingAddress['company'],
                'salutation' => $userBillingAddress['salutation'],
                'customernumber' => $this->customerNumber,
                'countryID' => $userBillingAddress['countryID'],
                'stateID' => $userBillingAddress['stateID'],
            ],
        ];
        foreach ($orderBillingAddresses as $address) {
            $this->connection->insert('s_order_billingaddress', $address);
        }
    }

    private function createVisitors(): void
    {
        $visitors = [
            [
                'shopID' => 1,
                'datum' => '2013-06-15',
                'pageimpressions' => 500,
                'uniquevisits' => 20,
            ],
            [
                'shopID' => 1,
                'datum' => '2013-06-01',
                'pageimpressions' => 300,
                'uniquevisits' => 10,
            ],
        ];
        foreach ($visitors as $visitor) {
            $this->connection->insert('s_statistics_visitors', $visitor);
        }
    }

    private function createImpressions(): void
    {
        $this->connection->insert(
            's_statistics_article_impression',
            [
                'articleId' => $this->productId,
                'shopId' => 1,
                'date' => '2013-06-15',
                'impressions' => 10,
            ]
        );
    }

    private function createSearchTerms(): void
    {
        $this->connection->insert(
            's_statistics_search',
            [
                'datum' => '2013-06-15 10:11:12',
                'searchterm' => 'phpunit search term',
                'results' => 10,
            ]
        );
    }

    private function createReferrer(): void
    {
        $this->connection->insert(
            's_statistics_referer',
            [
                'datum' => '2013-06-15',
                'referer' => 'https://www.google.de/?q=phpunit',
            ]
        );
    }

    private function removeDemoData(): void
    {
        if ($this->userId) {
            $this->connection->delete('s_user', ['id' => $this->userId]);
            $this->connection->delete('s_user_addresses', ['user_id' => $this->userId]);
            $this->connection->delete('s_order', ['userID' => $this->userId]);
            $this->connection->delete('s_order_billingaddress', ['userID' => $this->userId]);
        }

        if ($this->productVariantId) {
            $this->connection->delete('s_articles_details', ['id' => $this->productVariantId]);
        }

        if ($this->productId) {
            $this->connection->delete('s_articles', ['id' => $this->productId]);
            $this->connection->delete('s_statistics_article_impression', ['articleId' => $this->productId]);
            $this->connection->delete('s_order_details', ['articleID' => $this->productId]);
        }

        if ($this->categoryId) {
            if ($this->productId) {
                $this->connection->delete('s_articles_categories_ro', ['articleID' => $this->productId]);
            }
            $this->connection->delete('s_categories', ['id' => $this->categoryId]);
        }

        $this->connection->createQueryBuilder()
            ->delete('s_statistics_visitors')
            ->where('shopID = 1')
            ->andWhere('datum = "2013-06-01" OR datum = "2013-06-15"')
            ->execute();
        $this->connection->delete('s_statistics_search', ['searchterm' => 'phpunit search term']);
        $this->connection->delete('s_statistics_referer', ['referer' => 'https://www.google.de/?q=phpunit']);
    }

    private function getSearchTermFromReferrerUrl(string $url): string
    {
        preg_match_all(
            '#[?&]([qp]|query|highlight|encquery|url|field-keywords|as_q|sucheall|satitle|KW)=([^&\$]+)#',
            utf8_encode($url) . '&',
            $matches
        );
        if (empty($matches[0])) {
            return '';
        }

        $ref = html_entity_decode(rawurldecode(strtolower($matches[2][0])));
        $ref = str_replace('+', ' ', $ref);

        $replace = preg_replace('/\s\s+/', ' ', $ref);
        static::assertIsString($replace);

        return trim($replace);
    }
}
