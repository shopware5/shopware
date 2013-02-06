<?php
/**
 * Shopware 4.0
 * Copyright © 2012 shopware AG
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

/**
 * @category  Shopware
 * @package   Shopware\Tests
 * @copyright Copyright (c) 2012, shopware AG (http://www.shopware.de)
 */
class Shopware_Tests_Controllers_Frontend_CustomTest extends Enlight_Components_Test_Controller_TestCase
{
    /**
     * Returns the test dataset
     *
     * @return PHPUnit_Extensions_Database_DataSet_IDataSet
     */
    protected function getDataSet()
    {
        return $this->createXMLDataSet(Shopware()->TestPath('DataSets_Cms').'Static.xml');
    }

    /**
     * Test case method
     */
    public function testIndex()
    {
        //TODO - Activate after Smarty-Update
        return;

        $this->dispatch('/custom?sCustom=2');

        $this->assertNotNull($this->View()->sContent, 'Content');
        $this->assertNotNull($this->View()->sCustomPage, 'CustomPage');
        $this->assertNotNull($this->View()->sBreadcrumb, 'Breadcrumb');

        $this->assertEquals(200, $this->Response()->getHttpResponseCode());
    }

    /**
     * Test case method
     */
    public function testRedirect()
    {
        //TODO - Activate after Smarty-Update
        return;

        $this->dispatch('/custom?sCustom=1');

        $this->assertEquals(301, $this->Response()->getHttpResponseCode());

        $this->assertArrayHasKey(1, $this->Response()->getHeaders());
    }

    /**
     * Test case method
     *
     * @ticket 4912
     */
    public function testTemplate()
    {
        //TODO - Activate after Smarty-Update
        return;

        $this->dispatch('/custom?sCustom=3');

        $this->assertNotNull($this->View()->sContent, 'Content');
        $this->assertNotNull($this->View()->sCustomPage, 'CustomPage');
        $this->assertNotNull($this->View()->sBreadcrumb, 'Breadcrumb');

        $this->assertEquals('Hello world !!!', $this->View()->sContent);
        $this->assertEquals($this->Response()->getHttpResponseCode(), 200);
    }

    /**
     * Test case method
     */
    public function testTemplateNotFound()
    {
        //TODO - Activate after Smarty-Update
        return;

        $this->dispatch('/custom?sCustom=2');

        $this->assertNotNull($this->View()->sContent, 'Content');
        $this->assertNotNull($this->View()->sCustomPage, 'CustomPage');
        $this->assertNotNull($this->View()->sBreadcrumb, 'Breadcrumb');

        $this->assertNull($this->View()->sContainerRight);
        $this->assertEquals($this->Response()->getHttpResponseCode(), 200);
    }

    /**
     * Test case method
     */
    public function testAjax()
    {
        //TODO - Activate after Smarty-Update
        return;

        $this->Request()->setHeader('X-REQUESTED-WITH', 'XMLHttpRequest');

        $this->dispatch('/custom?sCustom=2');

        $this->assertNotNull($this->View()->sContent, 'Content');
        $this->assertNotNull($this->View()->sCustomPage, 'CustomPage');
        $this->assertNotNull($this->View()->sBreadcrumb, 'Breadcrumb');

        $this->assertEquals(200, $this->Response()->getHttpResponseCode());
    }
}
