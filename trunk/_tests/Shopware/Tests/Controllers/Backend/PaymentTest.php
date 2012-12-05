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
 * @author     Patrick_Stahl
 * @author     $Author$
 */

class Shopware_Tests_Controllers_Backend_PaymentTest extends Enlight_Components_Test_Controller_TestCase
{

    private $testDataCreate = array(
        "name" => "New payment",
        "description" => "New payment",
        "source" => 1,
        "template" => "",
        "class" => "",
        "table" => "",
        "hide" => 0,
        "additionaldescription" => "",
        "debitPercent" => 0,
        "surcharge" => 0,
        "surchargeString" => "",
        "position" => 0,
        "active" => 0,
        "esdActive" => 0,
        "embedIFrame" => "",
        "hideProspect" => ""
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
     * Tests the getPaymentsAction()
     * to test if reading the payments is working
     */
    public function testGetPayments()
    {
        /** @var Enlight_Controller_Response_ResponseTestCase */
        $this->dispatch('backend/payment/getPayments');
        $this->assertTrue($this->View()->success);

        $jsonBody = $this->View()->getAssign();

        $this->assertArrayHasKey('data', $jsonBody);
        $this->assertArrayHasKey('success', $jsonBody);
    }

    /**
     * Tests the getCountriesAction()
     * to test if reading the countries is working
     */
    public function testGetCountries()
    {
        /** @var Enlight_Controller_Response_ResponseTestCase */
        $this->dispatch('backend/payment/getCountries');
        $this->assertTrue($this->View()->success);

        $jsonBody = $this->View()->getAssign();

        $this->assertArrayHasKey('data', $jsonBody);
        $this->assertArrayHasKey('success', $jsonBody);
    }

    /**
     * Function to test creating a new payment
     * @return mixed
     */
    public function testCreatePayments()
    {
        Shopware()->Db()->exec('DELETE FROM s_core_paymentmeans WHERE name = "New payment"');

        $this->Request()->setMethod('POST')->setPost($this->testDataCreate);
        $this->dispatch('backend/payment/createPayments');

        $this->assertTrue($this->View()->success);
        $jsonBody = $this->View()->getAssign();

        $this->assertArrayHasKey('data', $jsonBody);
        $this->assertArrayHasKey('success', $jsonBody);

        return $jsonBody['data'];
    }

    /**
     * Function to test updating a payment
     * @param $data Contains the data of the created payment
     * @depends testCreatePayments
     */
    public function testUpdatePayments($data)
    {
        $this->Request()->setMethod('POST')->setPost(array('id'=>$data['id'], 'name'=>'Neue Zahlungsart'));

        /** @var Enlight_Controller_Response_ResponseTestCase */
        $this->dispatch('backend/payment/updatePayments');
        $this->assertTrue($this->View()->success);

        $jsonBody = $this->View()->getAssign();

        $this->assertArrayHasKey('data', $jsonBody);
        $this->assertArrayHasKey('success', $jsonBody);
    }

    /**
     * Function to test deleting a payment
     * @param $data Contains the data of the created payment
     * @depends testCreatePayments
     */
    public function testDeletePayment($data)
    {
        $this->Request()->setMethod('POST')->setPost(array('id' => $data['id']));

        /** @var Enlight_Controller_Response_ResponseTestCase */
        $this->dispatch('backend/payment/deletePayment');
        $this->assertTrue($this->View()->success);

        $jsonBody = $this->View()->getAssign();

        $this->assertArrayHasKey('success', $jsonBody);
    }
}
