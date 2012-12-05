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
 * @author     Benjamin Cremer
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
 * @group Form
 * @group Shopware_Tests
 * @group Controllers
 */
class Shopware_Tests_Controllers_Backend_FormTest extends Enlight_Components_Test_Controller_TestCase
{
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

    public function testGetFormsShouldBeSuccessful()
    {
        $this->dispatch('/backend/form/getForms?page=1&start=0&limit=25');

        $this->assertTrue($this->View()->success);
        $this->assertNotEmpty($this->View()->data);
        $this->assertGreaterThan(5, $this->View()->total);
    }

    public function testGetFormsShouldBeFilterAndSortable()
    {
        $queryParams = array(
            'page'  => '1',
            'start' => '0',
            'limit' => 25,
            'sort'  => json_encode(
                array(
                    array(
                        'property'  => 'name',
                        'direction' => 'ASC',
                    )
                )
            ),
            'filter'  => json_encode(
                array(
                    array(
                        'property'  => 'name',
                        'value' => 'def%',
                    )
                )
            )
        );

        $query = http_build_query($queryParams);

        $url = 'backend/form/getForms?';

        $this->dispatch($url . $query);

        $this->assertTrue($this->View()->success);
        $this->assertNotEmpty($this->View()->data);
        $this->assertEquals(2, $this->View()->total);
    }

    public function testGetFormsWithIdShouldReturnSingleForm()
    {
        $this->dispatch('/backend/form/getForms?&id=22');

        $data = $this->View()->data;

        $this->assertTrue($this->View()->success);
        $this->assertNotEmpty($this->View()->data);
        $this->assertNotEmpty($data[0]['fields']);
        $this->assertGreaterThan(5, $data[0]['fields']);
        $this->assertEquals(1, $this->View()->total);
    }

    public function testGetFormsWithInvalidIdShouldReturnFailure()
    {
        $this->dispatch('/backend/form/getForms?&id=99999999');

        $this->assertFalse($this->View()->success);
    }

    public function testGetFieldsShouldReturnFields()
    {
        $this->dispatch('/backend/form/getFields?formId=5');
        $this->assertTrue($this->View()->success);
        $this->assertNotEmpty($this->View()->data);
        $this->assertGreaterThan(2, $this->View()->total);
    }
}