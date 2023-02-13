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

use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityNotFoundException;
use Enlight_Components_Test_Controller_TestCase;
use Enlight_Controller_Request_RequestTestCase;
use Enlight_Template_Manager;
use Enlight_View_Default;
use Exception;
use Generator;
use Shopware\Components\Model\Exception\ModelNotFoundException;
use Shopware\Controllers\Backend\OrderProductSearch;
use Shopware\Tests\Functional\Traits\ContainerTrait;
use Shopware\Tests\Functional\Traits\DatabaseTransactionBehaviour;
use Shopware_Plugins_Backend_Auth_Bootstrap as AuthPlugin;

/**
 * @phpstan-type GetProductVariantsExpectedResults array<array{total: int, id: string, name: string, active: string, taxID: string, tax: float, ordernumber: string, articleId: string, inStock: string, supplierName: string, supplierId: string, additionalText: string, price: float}>
 */
class OrderProductSearchTest extends Enlight_Components_Test_Controller_TestCase
{
    use ContainerTrait;
    use DatabaseTransactionBehaviour;

    private const ORDER_ID_CUSTOMER_GROUP_MERCHANT = 15;
    private const ORDER_ID_CUSTOMER_GROUP_DEFAULT = 57;
    private const ORDER_ID_NOT_EXIST = 999;
    private const CUSTOMER_GROUP_ID_MERCHANT = 2;
    private const CUSTOMER_ID_NOT_EXIST = 777;
    private const FINLAND_COUNTRY_ID = 8;
    private const DENMARK_COUNTRY_ID = 7;
    private const AREA_ID_EUROPE = 3;
    private const TAX_ID_7_PERCENT = 4;
    private const TAX_ID_19_PERCENT = 1;
    private const SEARCH_PARAMS_FILTER_PROPERTY = 'free';

    private AuthPlugin $authPlugin;

    private Connection $connection;

    /**
     * Standard set up for every test - just disable auth
     */
    public function setUp(): void
    {
        parent::setUp();

        $this->authPlugin = $this->getContainer()->get('plugins')->Backend()->Auth();
        $this->authPlugin->setNoAuth();
        $this->connection = $this->getContainer()->get(Connection::class);
    }

    public function tearDown(): void
    {
        parent::tearDown();

        $this->authPlugin->setNoAuth(false);
    }

    /**
     * @dataProvider provideSearchDataAndReturnValues
     *
     * @param array<string, mixed> $searchParams
     *
     * @phpstan-param GetProductVariantsExpectedResults $expectedResults
     *
     * @throws Exception
     */
    public function testGetProductVariantsActionConfirmReturnValues(array $searchParams, bool $hasResults, array $expectedResults = []): void
    {
        $params = $this->getSearchParams($searchParams);

        $request = new Enlight_Controller_Request_RequestTestCase();
        $request->setParams($params);

        $controller = $this->getController();
        $controller->setRequest($request);
        $controller->getProductVariantsAction();
        $results = $controller->View()->getAssign();

        static::assertIsArray($results);
        static::assertIsArray($results['data']);
        static::assertIsBool($results['success']);
        static::assertIsInt($results['total']);

        if (!$hasResults) {
            static::assertEmpty($results['data']);
            static::assertSame(0, $results['total']);

            return;
        }

        static::assertLessThanOrEqual($params['limit'], \count($results['data']));
        static::assertArrayHasKey('id', $results['data'][0]);
        static::assertArrayHasKey('name', $results['data'][0]);
        static::assertArrayHasKey('active', $results['data'][0]);
        static::assertArrayHasKey('ordernumber', $results['data'][0]);
        static::assertArrayHasKey('articleId', $results['data'][0]);
        static::assertArrayHasKey('inStock', $results['data'][0]);
        static::assertArrayHasKey('supplierName', $results['data'][0]);
        static::assertArrayHasKey('supplierId', $results['data'][0]);
        static::assertArrayHasKey('additionalText', $results['data'][0]);
        static::assertArrayHasKey('price', $results['data'][0]);
        static::assertIsFloat($results['data'][0]['price']);
        static::assertIsFloat($results['data'][0]['tax']);
        static::assertArrayHasKey('taxId', $results['data'][0]);

        if (!empty($expectedResults)) {
            static::assertSame($expectedResults['total'], $results['total']);
            unset($expectedResults['total']);
            foreach ($expectedResults as $key => $value) {
                static::assertSame($value, $results['data'][0][$key], 'test key: ' . $key);
            }
        }
    }

    /**
     * @return Generator<array{searchParams: array{searchString: string, orderId: int}, hasResults: bool, 3?:GetProductVariantsExpectedResults}>
     */
    public function provideSearchDataAndReturnValues(): Generator
    {
        yield 'searchTerm: orderNumber explicit - customer group: "H"' => [
            'searchParams' => [
                'searchString' => 'SW10178',
                'orderId' => self::ORDER_ID_CUSTOMER_GROUP_MERCHANT,
                'filterProperty' => self::SEARCH_PARAMS_FILTER_PROPERTY,
            ],
            'hasResults' => true,
            [
                'total' => 1,
                'id' => '407',
                'name' => 'Strandtuch "Ibiza"',
                'active' => '1',
                'taxId' => '1',
                'tax' => 19.0,
                'ordernumber' => 'SW10178',
                'articleId' => '178',
                'inStock' => '84',
                'supplierName' => 'Beachdreams Clothes',
                'supplierId' => '12',
                'additionalText' => '',
                'price' => 19.95,
            ],
        ];
        yield 'searchTerm: productName - customer group: "H"' => [
            'searchParams' => [
                'searchString' => 'schoko',
                'orderId' => self::ORDER_ID_CUSTOMER_GROUP_MERCHANT,
                'filterProperty' => self::SEARCH_PARAMS_FILTER_PROPERTY,
            ],
            'hasResults' => true,
            [
                'total' => 1,
                'id' => '46',
                'name' => 'Schokoleim',
                'active' => '1',
                'taxId' => '4',
                'tax' => 7.0,
                'ordernumber' => 'SW10039',
                'articleId' => '40',
                'inStock' => '50',
                'supplierName' => 'The Deli Garage',
                'supplierId' => '4',
                'additionalText' => '',
                'price' => 8.98,
            ],
        ];
        yield 'searchTerm: supplier with products not mapped to a category - customer group: "EK"' => [
            'searchParams' => [
                'searchString' => 'trusted',
                'orderId' => self::ORDER_ID_CUSTOMER_GROUP_DEFAULT,
                'filterProperty' => self::SEARCH_PARAMS_FILTER_PROPERTY,
            ],
            'hasResults' => true,
            [
                'total' => 10,
                'id' => '804',
                'name' => 'Käuferschutz',
                'active' => '1',
                'taxId' => '1',
                'tax' => 19.0,
                'ordernumber' => 'TS100629_500_30_GBP',
                'articleId' => '249',
                'inStock' => '0',
                'supplierName' => 'Trusted Shops',
                'supplierId' => '17',
                'additionalText' => null,
                'price' => 0.98,
            ],
        ];
        yield 'searchTerm: productName - customer group: "EK" - variants' => [
            'searchParams' => [
                'searchString' => 'flip',
                'orderId' => self::ORDER_ID_CUSTOMER_GROUP_DEFAULT,
                'filterProperty' => self::SEARCH_PARAMS_FILTER_PROPERTY,
            ],
            'hasResults' => true,
            [
                'total' => 10,
                'id' => '322',
                'name' => 'Flip Flops, in mehreren Farben verfügbar blau / 39/40',
                'active' => '1',
                'taxId' => '1',
                'tax' => 19.0,
                'ordernumber' => 'SW10153.1',
                'articleId' => '153',
                'inStock' => '67',
                'supplierName' => 'Beachdreams Clothes',
                'supplierId' => '12',
                'additionalText' => 'blau / 39/40',
                'price' => 6.99,
            ],
        ];
        yield 'param $filter[\'property\'] is not \'free\' shall return 10 products without filter' => [
            'searchParams' => [
                'searchString' => 'ibiza',
                'orderId' => self::ORDER_ID_CUSTOMER_GROUP_DEFAULT,
                'filterProperty' => '',
            ],
            'hasResults' => true,
            [
                'total' => 10,
                'id' => '3',
                'name' => 'Münsterländer Aperitif 16%',
                'active' => '1',
                'taxId' => '1',
                'tax' => 19.0,
                'ordernumber' => 'SW10003',
                'articleId' => '3',
                'inStock' => '25',
                'supplierName' => 'Feinbrennerei Sasse',
                'supplierId' => '2',
                'additionalText' => '',
                'price' => 14.95,
            ],
        ];
        yield 'searchTerm not exists' => [
            'searchParams' => [
                'searchString' => 'lorem',
                'orderId' => self::ORDER_ID_CUSTOMER_GROUP_DEFAULT,
                'filterProperty' => self::SEARCH_PARAMS_FILTER_PROPERTY,
            ],
            'hasResults' => false,
        ];
    }

    /**
     * @dataProvider provideDataForDifferentProductPricesForCustomerGroups
     *
     * @param array<string, mixed>        $searchParams
     * @param array<string, float>        $expectedValues
     * @param array<array<string, mixed>> $sqlParamsTaxRule
     * @param array<string, mixed>        $sqlParamsProduct
     * @param array<string, mixed>        $sqlParamsShippingAddress
     *
     * @throws Exception
     * @throws \Doctrine\DBAL\Driver\Exception
     */
    public function testCustomerGroupPricesAndTaxRules(
        array $searchParams,
        array $expectedValues,
        array $sqlParamsTaxRule,
        array $sqlParamsProduct,
        array $sqlParamsShippingAddress
    ): void {
        if (!empty($sqlParamsTaxRule)) {
            $this->createTaxRules($sqlParamsTaxRule);
        }

        if (!empty($sqlParamsProduct)) {
            $this->createCustomerGroupSpecificProductPrice($sqlParamsProduct);
        }

        if (!empty($sqlParamsShippingAddress)) {
            $this->changeShippingAddress($sqlParamsShippingAddress);
        }

        $params = $this->getSearchParams($searchParams);

        $request = new Enlight_Controller_Request_RequestTestCase();
        $request->setParams($params);

        $controller = $this->getController();
        $controller->setRequest($request);
        $controller->getProductVariantsAction();
        $results = $controller->View()->getAssign();

        static::assertIsArray($results);
        static::assertIsArray($results['data']);
        static::assertTrue($results['success']);
        static::assertSame($expectedValues['price'], $results['data'][0]['price']);
        static::assertSame($expectedValues['tax'], $results['data'][0]['tax']);
    }

    /**
     * @return Generator<array{searchParams: array<string, mixed>, expectedValues: array<string, float>, sqlParamsTaxRule: array<array<string, mixed>>, sqlParamsProduct: array<string, mixed>, sqlParamsShippingAddress: array<string, mixed>}>
     */
    public function provideDataForDifferentProductPricesForCustomerGroups(): Generator
    {
        yield 'customer group: "H" with a specific product-price' => [
            'searchParams' => [
                'searchString' => 'stuhl',
                'orderId' => self::ORDER_ID_CUSTOMER_GROUP_MERCHANT,
                'filterProperty' => self::SEARCH_PARAMS_FILTER_PROPERTY,
            ],
            'expectedValues' => [
                'price' => 119.00,
                'tax' => 19.00,
            ],
            'sqlParamsTaxRule' => [],
            'sqlParamsProduct' => [
                'productId' => 74,
                'customerGroup' => 'H',
                'productDetailsId' => 137,
            ],
            'sqlParamsShippingAddress' => [],
        ];
        yield 'customer group: "H", shipping address doesn\'t match with a tax-rule and with no specific product-price should use default-price' => [
            'searchParams' => [
                'searchString' => 'stuhl',
                'orderId' => self::ORDER_ID_CUSTOMER_GROUP_MERCHANT,
                'filterProperty' => self::SEARCH_PARAMS_FILTER_PROPERTY,
            ],
            'expectedValues' => [
                'price' => 74.99,
                'tax' => 19.00,
            ],
            'sqlParamsTaxRule' => [
                [
                    'areaID' => self::AREA_ID_EUROPE,
                    'countryID' => self::DENMARK_COUNTRY_ID,
                    'stateID' => null,
                    'groupID' => self::TAX_ID_19_PERCENT,
                    'customer_groupID' => self::CUSTOMER_GROUP_ID_MERCHANT,
                    'tax' => 10.00,
                    'name' => 'Denmark',
                ],
            ],
            'sqlParamsProduct' => [],
            'sqlParamsShippingAddress' => [],
        ];
        yield 'customer group: "H", shipping address match with tax-rule + custom product price' => [
            'searchParams' => [
                'filterProperty' => self::SEARCH_PARAMS_FILTER_PROPERTY,
                'searchString' => 'Schokoleim',
                'orderId' => self::ORDER_ID_CUSTOMER_GROUP_MERCHANT,
            ],
            'expectedValues' => [
                'price' => 200.00,
                'tax' => 100.00,
            ],
            'sqlParamsTaxRule' => [
                [
                    'areaID' => self::AREA_ID_EUROPE,
                    'countryID' => self::DENMARK_COUNTRY_ID,
                    'stateID' => null,
                    'groupID' => self::TAX_ID_7_PERCENT,
                    'customer_groupID' => self::CUSTOMER_GROUP_ID_MERCHANT,
                    'tax' => 10.00,
                    'name' => 'Denmark',
                ],
                [
                    'areaID' => self::AREA_ID_EUROPE,
                    'countryID' => self::FINLAND_COUNTRY_ID,
                    'stateID' => null,
                    'groupID' => self::TAX_ID_7_PERCENT,
                    'customer_groupID' => self::CUSTOMER_GROUP_ID_MERCHANT,
                    'tax' => 100.00,
                    'name' => 'Finland',
                ],
            ],
            'sqlParamsProduct' => [
                'productId' => 40,
                'customerGroup' => 'H',
                'productDetailsId' => 46,
            ],
            'sqlParamsShippingAddress' => [
                'countryID' => self::FINLAND_COUNTRY_ID,
                'stateID' => null,
                'orderID' => self::ORDER_ID_CUSTOMER_GROUP_MERCHANT,
            ],
        ];
    }

    /**
     * @dataProvider provideSearchParamsThatThrowsExceptions
     *
     * @param array<string, mixed> $searchParams
     * @param array<string, mixed> $exception
     *
     * @throws Exception
     */
    public function testGetProductVariantsActionThrowsExceptions(array $searchParams, array $exception): void
    {
        $params = $this->getSearchParams($searchParams);

        $request = new Enlight_Controller_Request_RequestTestCase();
        $request->setParams($params);

        $controller = $this->getController();
        $controller->setRequest($request);
        $this->expectException($exception['type']);
        $this->expectExceptionMessage($exception['message']);
        $controller->getProductVariantsAction();
    }

    /**
     * @return Generator<array{searchParams: array<string, mixed>, exception: array<string, mixed>}>
     */
    public function provideSearchParamsThatThrowsExceptions(): Generator
    {
        yield 'missing orderId' => [
            'searchParams' => [
                'searchString' => 'stuhl',
                'filterProperty' => self::SEARCH_PARAMS_FILTER_PROPERTY,
            ],
            'exception' => [
                'type' => 'RuntimeException',
                'message' => 'The parameter orderId is not set',
            ],
        ];
        yield 'orderId not exist' => [
            'searchParams' => [
                'searchString' => 'stuhl',
                'filterProperty' => self::SEARCH_PARAMS_FILTER_PROPERTY,
                'orderId' => self::ORDER_ID_NOT_EXIST,
            ],
            'exception' => [
                'type' => 'RuntimeException',
                'message' => 'Shopware\Models\Order\Order" for id "' . self::ORDER_ID_NOT_EXIST,
            ],
        ];
    }

    /**
     * @throws \Doctrine\DBAL\Exception
     * @throws Exception
     */
    public function testGetProductVariantsActionThrowsExceptionsCustomerNotFound(): void
    {
        $params = require __DIR__ . '/_assets/getProductVariantsParams.php';
        static::assertIsArray($params);
        $params['orderId'] = self::ORDER_ID_CUSTOMER_GROUP_MERCHANT;
        $params['filter']['property'] = self::SEARCH_PARAMS_FILTER_PROPERTY;

        $sql = 'UPDATE s_order SET userID = :userId WHERE id = :orderId;';
        $this->connection->executeQuery($sql, [
                'orderId' => self::ORDER_ID_CUSTOMER_GROUP_MERCHANT,
                'userId' => self::CUSTOMER_ID_NOT_EXIST,
            ]
        );

        $request = new Enlight_Controller_Request_RequestTestCase();
        $request->setParams($params);

        $controller = $this->getController();
        $controller->setRequest($request);
        $this->expectException(EntityNotFoundException::class);
        $this->expectExceptionMessage("Entity of type 'Shopware\Models\Customer\Customer' for IDs id(" . self::CUSTOMER_ID_NOT_EXIST . ')');
        $controller->getProductVariantsAction();
    }

    /**
     * @throws \Doctrine\DBAL\Exception
     * @throws Exception
     */
    public function testGetProductVariantsActionThrowsExceptionsShippingNotFound(): void
    {
        $params = require __DIR__ . '/_assets/getProductVariantsParams.php';
        static::assertIsArray($params);
        $params['orderId'] = self::ORDER_ID_CUSTOMER_GROUP_DEFAULT;
        $params['filter']['property'] = self::SEARCH_PARAMS_FILTER_PROPERTY;

        $sql = 'UPDATE s_order_shippingaddress SET orderID = :orderIdNotExist WHERE orderID = :orderId;';
        $this->connection->executeQuery($sql, [
                'orderId' => self::ORDER_ID_CUSTOMER_GROUP_DEFAULT,
                'orderIdNotExist' => self::ORDER_ID_NOT_EXIST,
            ]
        );

        $request = new Enlight_Controller_Request_RequestTestCase();
        $request->setParams($params);

        $controller = $this->getController();
        $controller->setRequest($request);
        $this->expectException(ModelNotFoundException::class);
        $this->expectExceptionMessage('Model of "Shopware\Models\Order\Shipping" for id "' . self::ORDER_ID_CUSTOMER_GROUP_DEFAULT . '"');
        $controller->getProductVariantsAction();
    }

    /**
     * @param array<array<string, mixed>> $sqlParams
     *
     * @throws \Doctrine\DBAL\Exception
     * @throws \Doctrine\DBAL\Driver\Exception
     */
    private function createTaxRules(array $sqlParams): void
    {
        $taxRules = 'SELECT * FROM s_core_tax_rules';
        static::assertSame(0, (int) $this->connection->executeQuery($taxRules)->rowCount());

        $sql = file_get_contents(__DIR__ . '/_fixtures/order/tax_rules_2.sql');
        static::assertIsString($sql);
        foreach ($sqlParams as $params) {
            $this->connection->executeQuery(
                $sql,
                [
                    'areaID' => $params['areaID'],
                    'countryID' => $params['countryID'],
                    'stateID' => $params['stateID'],
                    'groupID' => $params['groupID'],
                    'customer_groupID' => $params['customer_groupID'],
                    'tax' => $params['tax'],
                    'name' => $params['name'],
                ]
            );
        }

        static::assertCount(\count($sqlParams), $this->connection->executeQuery($taxRules)->fetchAllAssociative());
    }

    /**
     * @param array<string, mixed> $sqlParams
     *
     * @throws \Doctrine\DBAL\Exception
     * @throws \Doctrine\DBAL\Driver\Exception
     */
    private function createCustomerGroupSpecificProductPrice(array $sqlParams): void
    {
        $countProductPricesQuery = 'SELECT count(*) FROM s_articles_prices WHERE articledetailsID = :productDetailsId AND pricegroup = :customerGroup';
        static::assertSame(0, (int) $this->connection->executeQuery(
            $countProductPricesQuery, ['productDetailsId' => $sqlParams['productDetailsId'], 'customerGroup' => $sqlParams['customerGroup']]
        )->fetchOne(), 'A customer-group specific price has already been assigned to this product');

        $sql = file_get_contents(__DIR__ . '/_fixtures/order/customerGroupSpecificPrices.sql');
        static::assertIsString($sql);
        $this->connection->executeStatement($sql, ['customerGroup' => $sqlParams['customerGroup'], 'productId' => $sqlParams['productId'], 'productDetailsId' => $sqlParams['productDetailsId']]);

        static::assertSame(1, (int) $this->connection->executeQuery(
            $countProductPricesQuery, ['productDetailsId' => $sqlParams['productDetailsId'], 'customerGroup' => $sqlParams['customerGroup']]
        )->fetchOne());
    }

    /**
     * @param array<string, mixed> $sqlParams
     *
     * @throws \Doctrine\DBAL\Exception
     */
    private function changeShippingAddress(array $sqlParams): void
    {
        $sql = file_get_contents(__DIR__ . '/_fixtures/order/shipping_address.sql');
        static::assertIsString($sql);
        $this->connection->executeQuery(
            $sql, [
                'countryID' => $sqlParams['countryID'],
                'stateID' => $sqlParams['stateID'],
                'orderID' => $sqlParams['orderID'],
            ]
        );
    }

    /**
     * @param array<string, mixed> $searchParams
     *
     * @return array<string, mixed>
     */
    private function getSearchParams(array $searchParams): array
    {
        $params = [
            'articles' => 'true',
            'variants' => 'true',
            'configurator' => 'true',
            'page' => 1,
            'start' => 0,
            'limit' => 10,
            'filter' => [[
                'property' => $searchParams['filterProperty'],
                'value' => '%' . $searchParams['searchString'] . '%',
                'operator' => null,
                'expression' => null,
            ]],
        ];

        if ($searchParams['orderId']) {
            $params['orderId'] = $searchParams['orderId'];
        }

        return $params;
    }

    private function getController(): OrderProductSearch
    {
        $controller = $this->getContainer()->get(OrderProductSearch::class);
        $controller->setView(new Enlight_View_Default(new Enlight_Template_Manager()));
        $controller->setContainer($this->getContainer());

        return $controller;
    }
}
