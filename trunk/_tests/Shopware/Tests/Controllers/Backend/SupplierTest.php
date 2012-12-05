<?php
/**
 * Shopware
 *
 * LICENSE
 *
 * Available through the world-wide-web at this URL:
 * http://shopware.de/license
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@shopware.de so we can send you a copy immediately.
 *
 * @category   Shopware
 * @package    Shopware_Tests
 * @subpackage Controllers
 * @copyright  Copyright (c) 2012, shopware AG (http://www.shopware.de)
 * @license    http://shopware.de/license
 * @version    $Id$
 * @author     Heiner Lohaus
 * @author     $Author$
 */

/**
 * Test case
 *
 * @category   Shopware
 * @package    Shopware_Tests
 * @subpackage Controllers
 * @copyright  Copyright (c) 2012, shopware AG (http://www.shopware.de)
 * @license    http://shopware.de/license
 * @group Supplier
 * @group Shopware_Tests
 * @group Controllers
 */
class Shopware_Tests_Controllers_Backend_SupplierTest extends Enlight_Components_Test_Controller_TestCase
{
    /**
     * Supplier dummy data
     *
     * @var array
     */
    private $supplierData = array(
        'name' => '__supplierTest',
        'link' => 'www.example.com',
        'description' => 'Test Supplier added by <a href="http://www.phpunit.de">unit test.</a>',
        'image' => 'media/image/testImage.jpg'
    );

    /**
     * Standard set up for every test - just disable auth
     */
    public function setUp()
    {
        parent::setUp();

        // disable auth and acl
        Shopware()->Plugins()->Backend()->Auth()->setNoAuth();
        Shopware()->Plugins()->Backend()->Auth()->setNoAcl();
    }

    /**
     * Test Method to test
     *
     * a) can this action be dispatched
     * b) is the answer encapsulated in a JSON header
     *
     */
    public function testGetSuppliers()
    {
        Shopware()->Plugins()->Backend()->Auth()->setNoAuth();
        /** @var Enlight_Controller_Response_ResponseTestCase */
        $response = $this->dispatch('backend/supplier/getSuppliers');
        $this->assertTrue($this->View()->success);

        $jsonBody = $this->View()->getAssign();

        $this->assertArrayHasKey('total', $jsonBody);
        $this->assertArrayHasKey('data', $jsonBody);
        $this->assertArrayHasKey('success', $jsonBody);
    }

    /**
     * Method to test: adding a supplier to the db
     * This method has to be called before the delete test
     *
     * @return Array
     */
    public function testAddSupplier()
    {
        $this->Request()->setMethod('POST')->setPost($this->supplierData);
        $response = $this->dispatch('backend/supplier/createSupplier');
        $this->assertTrue($this->View()->success);

        $jsonBody = $this->View()->getAssign();

        $this->assertArrayHasKey('data', $jsonBody);
        $this->assertArrayHasKey('success', $jsonBody);
        return $jsonBody['data'];
    }

    /**
     * @depends testAddSupplier
     * @param $lastSupplier
     * @return array
     */
    public function testUpdateSupplier($lastSupplier)
    {        
        foreach($lastSupplier as $key=>$value) {
            if(!is_null($value)) {
                $supplier[$key] = $value;
            }
        }
        $supplier['name'] = '___testSupplier_UPDATE';
        
        $this->Request()->setMethod('POST')->setPost($supplier);
        $response = $this->dispatch('backend/supplier/updateSupplier');
        $this->assertTrue($this->View()->success);

        $jsonBody = $this->View()->getAssign();

        $this->assertArrayHasKey('data', $jsonBody);
        $this->assertArrayHasKey('success', $jsonBody);

        return $jsonBody['data'];
    }

    /**
     * Tests if the supplier can be removed from the database
     * The lastId is the id from the last add test
     *
     * @depends testUpdateSupplier
     * @param array $lastSupplier
     */
    public function testDeleteSupplier(array $lastSupplier)
    {
        $this->Request()->setMethod('POST')->setPost( array('id'=>$lastSupplier['id']) );
        $response = $this->dispatch('backend/supplier/deleteSupplier');
        $this->assertTrue($this->View()->success);
    }
}
