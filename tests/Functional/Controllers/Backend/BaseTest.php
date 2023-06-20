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

namespace Shopware\Tests\Functional\Controllers\Backend;

use Enlight_Components_Test_Controller_TestCase;
use Enlight_Controller_Request_RequestTestCase;
use Enlight_Template_Manager;
use Enlight_View_Default;
use Shopware\Tests\Functional\Traits\ContainerTrait;
use Shopware\Tests\Functional\Traits\DatabaseTransactionBehaviour;
use Shopware_Controllers_Backend_Base;
use Shopware_Plugins_Backend_Auth_Bootstrap as AuthPlugin;

class BaseTest extends Enlight_Components_Test_Controller_TestCase
{
    use ContainerTrait;
    use DatabaseTransactionBehaviour;

    private AuthPlugin $authPlugin;

    /**
     * Standard set up for every test - just disable auth
     */
    public function setUp(): void
    {
        parent::setUp();

        $this->authPlugin = $this->getContainer()->get('plugins')->Backend()->Auth();
        $this->authPlugin->setNoAuth();
    }

    public function tearDown(): void
    {
        parent::tearDown();

        $this->authPlugin->setNoAuth(false);
    }

    /**
     * @dataProvider provideSearchString
     *
     * @param array<array{total: int, id: string, name: string, description: string, active: string, ordernumber: string, articleId: string, inStock: string, supplierName: string, supplierId: string, additionalText: string, price: float}> $expectedResults
     */
    public function testGetVariantsActionConfirmReturnValues(string $searchTerm, bool $hasResults, array $expectedResults = []): void
    {
        $params = [
            'articles' => 'true',
            'variants' => 'true',
            'configurator' => 'true',
            'page' => 1,
            'start' => 0,
            'limit' => 10,
            'filter' => [[
                    'property' => 'free',
                    'value' => '%' . $searchTerm . '%',
                    'operator' => null,
                    'expression' => null,
            ]],
        ];

        $request = new Enlight_Controller_Request_RequestTestCase();
        $request->setParams($params);

        $controller = $this->createController();
        $controller->setRequest($request);
        $controller->getVariantsAction();
        $jsonBody = $controller->View()->getAssign();

        static::assertIsArray($jsonBody);
        static::assertIsArray($jsonBody['data']);
        static::assertIsBool($jsonBody['success']);
        static::assertIsInt($jsonBody['total']);

        if (!$hasResults) {
            static::assertEmpty($jsonBody['data']);
            static::assertEquals(0, $jsonBody['total']);

            return;
        }

        static::assertLessThanOrEqual($params['limit'], \count($jsonBody['data']));
        static::assertArrayHasKey('id', $jsonBody['data'][0]);
        static::assertIsString($jsonBody['data'][0]['id']);
        static::assertArrayHasKey('name', $jsonBody['data'][0]);
        static::assertIsString($jsonBody['data'][0]['name']);
        static::assertArrayHasKey('description', $jsonBody['data'][0]);
        static::assertIsString($jsonBody['data'][0]['description']);
        static::assertArrayHasKey('active', $jsonBody['data'][0]);
        static::assertIsString($jsonBody['data'][0]['active']);
        static::assertArrayHasKey('ordernumber', $jsonBody['data'][0]);
        static::assertIsString($jsonBody['data'][0]['ordernumber']);
        static::assertArrayHasKey('articleId', $jsonBody['data'][0]);
        static::assertIsString($jsonBody['data'][0]['articleId']);
        static::assertArrayHasKey('inStock', $jsonBody['data'][0]);
        static::assertIsString($jsonBody['data'][0]['inStock']);
        static::assertArrayHasKey('supplierName', $jsonBody['data'][0]);
        static::assertIsString($jsonBody['data'][0]['supplierName']);
        static::assertArrayHasKey('supplierId', $jsonBody['data'][0]);
        static::assertIsString($jsonBody['data'][0]['supplierId']);
        static::assertArrayHasKey('additionalText', $jsonBody['data'][0]);
        static::assertIsString($jsonBody['data'][0]['additionalText']);
        static::assertArrayHasKey('price', $jsonBody['data'][0]);
        static::assertIsFloat($jsonBody['data'][0]['price']);

        if (!empty($expectedResults)) {
            static::assertEquals($expectedResults['total'], $jsonBody['total']);
            static::assertEquals((int) $expectedResults['inStock'], (int) $jsonBody['data'][0]['inStock']);
            unset($expectedResults['inStock'], $expectedResults['total']);
            foreach ($expectedResults as $key => $value) {
                static::assertEquals($value, $jsonBody['data'][0][$key]);
            }
        }
    }

    /**
     * @return array<array{0: string, 1: bool, 2?:array{total: int, id: string, name: string, description: string, active: string, ordernumber: string, articleId: string, inStock: string, supplierName: string, supplierId: string, additionalText: string, price: float}}>
     */
    public function provideSearchString(): array
    {
        return [
            'orderNumber explicit' => ['SW10178', true, [
                'total' => 1,
                'id' => '407',
                'name' => 'Strandtuch "Ibiza"',
                'description' => 'paulatim Praecepio lex Edoceo sis conticinium Furtum Heidelberg casula Toto pes an jugiter pe.',
                'active' => '1',
                'ordernumber' => 'SW10178',
                'articleId' => '178',
                'inStock' => '84',
                'supplierName' => 'Beachdreams Clothes',
                'supplierId' => '12',
                'additionalText' => '',
                'price' => 19.95,
            ]],
            'orderNumber' => ['SW1022', true, [
                'total' => 10,
                'id' => '749',
                'name' => 'Prämienartikel ab 250 Euro Warenkorb Wert',
                'description' => 'Diesen Artikel können die Kunden kostenpflichtig erwerben oder kostenlos als Prämienartikel ab einem Warenkorb Wert von 250 Euro bekommen.',
                'active' => '1',
                'ordernumber' => 'SW10221',
                'articleId' => '211',
                'inStock' => '100',
                'supplierName' => 'Example',
                'supplierId' => '14',
                'additionalText' => '',
                'price' => 50,
            ]],
            'supplierName' => ['Sasse', true, [
                'total' => 10,
                'id' => '3',
                'name' => 'Münsterländer Aperitif 16%',
                'description' => 'ubi ait animadverto poema adicio',
                'active' => '1',
                'ordernumber' => 'SW10003',
                'articleId' => '3',
                'inStock' => '25',
                'supplierName' => 'Feinbrennerei Sasse',
                'supplierId' => '2',
                'additionalText' => '',
                'price' => 14.95,
            ]],
            'productName' => ['sommer', true, [
                'total' => 10,
                'id' => '364',
                'name' => 'Sommer Sandale Ocean Blue 36',
                'description' => 'Scelestus nam Comiter, tepesco ansa per ferox for Expiscor. Ex accuse homo avaritia sudo Gandavum.Sem furca pica.',
                'active' => '1',
                'ordernumber' => 'SW10160.1',
                'articleId' => '160',
                'inStock' => '19',
                'supplierName' => 'Beachdreams Clothes',
                'supplierId' => '12',
                'additionalText' => '36',
                'price' => 29.99,
            ]],
            'productName not exists' => ['lorem', false],
            'productName not mapped to category' => ['Bikini Ocean Blue', true, [
                'total' => 1,
                'id' => '297',
                'name' => 'Bikini Ocean Blue',
                'description' => 'Commodo cum mel voluptarius Pariter modicus opto coepto, maligo spes Resono Curvo escendo adsum per Frutex, ubi ait animadve.',
                'active' => '1',
                'ordernumber' => 'SW10150',
                'articleId' => '150',
                'inStock' => '45',
                'supplierName' => 'Beachdreams Clothes',
                'supplierId' => '12',
                'additionalText' => '',
                'price' => 9.99,
            ]],
        ];
    }

    private function createController(): Shopware_Controllers_Backend_Base
    {
        $controller = $this->getContainer()->get('shopware_controllers_backend_base');
        $controller->setView(new Enlight_View_Default(new Enlight_Template_Manager()));
        $controller->setContainer($this->getContainer());

        return $controller;
    }
}
