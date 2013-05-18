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
 * @group disable
 * @category  Shopware
 * @package   Shopware\Tests
 * @copyright Copyright (c) 2012, shopware AG (http://www.shopware.de)
 */
class Shopware_RegressionTests_Ticket4799 extends Enlight_Components_Test_Plugin_TestCase
{
    /**
     * Returns the test dataset
     *
     * @return PHPUnit_Extensions_Database_DataSet_IDataSet
     */
    protected function getDataSet()
    {
        return $this->createXMLDataSet(Shopware()->TestPath('DataSets_Checkout').'Finish.xml');
    }

    /**
     * Test case method
     */
    public function testCheckoutFinishLog()
    {
        $this->Request()
            ->setMethod('POST')
            ->setPost('email', 'test@example.com')
            ->setPost('password', 'shopware');
        $this->dispatch('/account/login');
        $this->assertTrue($this->Response()->isRedirect());
        $this->reset();
        $a = include(Shopware()->TestPath('DataSets_Checkout').'Variables.php');
        Shopware()->Session()->sOrderVariables = $a;
        Shopware()->Session()->sUserId = $a["sUserData"]["billingaddress"]["userID"];
        $this->dispatch('/checkout/finish?sUniqueID=bf5505a1180a9e3c39fbf396cb7c53cb');

        $this->assertContains('20001', $this->Response()->getBody());
        $this->assertContains('84d03011369c135f0ba48cdb37c63c12', $this->Response()->getBody());
    }
}
