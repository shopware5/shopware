<?php
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

namespace Shopware\Tests\Functional\Controllers\Backend;

use Enlight_Components_Test_Controller_TestCase;
use Exception;

class SupplierTest extends Enlight_Components_Test_Controller_TestCase
{
    /**
     * Supplier dummy data
     *
     * @var array
     */
    private $supplierData = [
        'name' => '__supplierTest',
        'link' => 'www.example.com',
        'description' => 'Test Supplier added by <a href="http://www.phpunit.de">unit test.</a>',
        'image' => 'media/image/testImage.jpg',
    ];

    /**
     * Standard set up for every test - just disable auth
     */
    public function setUp(): void
    {
        parent::setUp();

        // Disable auth and acl
        Shopware()->Plugins()->Backend()->Auth()->setNoAuth();
        Shopware()->Plugins()->Backend()->Auth()->setNoAcl();
    }

    /**
     * Test Method to test
     *
     * a) can this action be dispatched
     * b) is the answer encapsulated in a JSON header
     */
    public function testGetSuppliers()
    {
        Shopware()->Plugins()->Backend()->Auth()->setNoAuth();
        /* @var \Enlight_Controller_Response_ResponseTestCase */
        $this->dispatch('backend/supplier/getSuppliers');
        static::assertTrue($this->View()->success);

        $jsonBody = $this->View()->getAssign();

        static::assertArrayHasKey('total', $jsonBody);
        static::assertArrayHasKey('data', $jsonBody);
        static::assertArrayHasKey('success', $jsonBody);
    }

    /**
     * Method to test: adding a supplier to the db
     * This method has to be called before the delete test
     *
     * @return array
     */
    public function testAddSupplier()
    {
        $this->Request()->setMethod('POST')->setPost($this->supplierData);
        $this->dispatch('backend/supplier/createSupplier');
        static::assertTrue($this->View()->success);

        $jsonBody = $this->View()->getAssign();

        static::assertArrayHasKey('data', $jsonBody);
        static::assertArrayHasKey('success', $jsonBody);

        return $jsonBody['data'];
    }

    /**
     * @depends testAddSupplier
     *
     * @param array $lastSupplier
     *
     * @return array
     */
    public function testUpdateSupplier($lastSupplier)
    {
        foreach ($lastSupplier as $key => $value) {
            if (!\is_null($value)) {
                $supplier[$key] = $value;
            }
        }
        $supplier['name'] = '___testSupplier_UPDATE';

        $this->Request()->setMethod('POST')->setPost($supplier);
        $this->dispatch('backend/supplier/updateSupplier');
        static::assertTrue($this->View()->success);

        $jsonBody = $this->View()->getAssign();

        static::assertArrayHasKey('data', $jsonBody);
        static::assertArrayHasKey('success', $jsonBody);

        return $jsonBody['data'];
    }

    /**
     * Tests if the supplier can be removed from the database
     * The lastId is the id from the last add test
     *
     * @depends testUpdateSupplier
     */
    public function testDeleteSupplier(array $lastSupplier)
    {
        $this->Request()->setMethod('POST')->setPost(['id' => $lastSupplier['id']]);
        $this->dispatch('backend/supplier/deleteSupplier');
        static::assertTrue($this->View()->success);
    }

    /**
     * Test if getSuppliers will also assign true when no suppliers have been found
     *
     * @throws Exception
     */
    public function testGetZeroSuppliers(): void
    {
        Shopware()->Plugins()->Backend()->Auth()->setNoAuth();
        $this->Request()->setMethod('GET');
        $filter = json_encode([
                    'property' => 'name',
                    'value' => 'thismanufacturerdoesnotexist',
                    'operator' => null,
                    'expression' => null,
                ]);
        $query_params = urlencode(sprintf('[%s]', $filter));
        $this->dispatch('backend/supplier/getSuppliers?filter=' . $query_params);
        static::assertTrue($this->View()->getAssign('success'));

        $jsonBody = $this->View()->getAssign();
        static::assertArrayHasKey('total', $jsonBody);
        static::assertArrayHasKey('data', $jsonBody);
        static::assertArrayHasKey('success', $jsonBody);
    }
}
