<?php
/**
 * Shopware 4
 * Copyright © shopware AG
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

class sAdminTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var sAdmin
     */
    private $module;

    /**
     * @var Shopware_Components_Config
     */
    private $config;

    public function setUp()
    {
        parent::setUp();

        $this->module = Shopware()->Modules()->Admin();
        $this->config = Shopware()->Config();
        $this->module->sSYSTEM->sCONFIG = &$this->config;

        // Create a stub for the Shopware_Components_Snippet_Manager class.
        $stub = $this->getMockBuilder('\Enlight_Components_Snippet_Manager')
            ->setMethods(['get'])
            ->disableOriginalConstructor()
            ->getMock();

        $stub->expects($this->any())
            ->method('get')
            ->will($this->returnArgument(0));

        // Inject the stub, so that tests can be translation independent
        $this->module->snippetObject = $stub;
    }

    /**
     * @covers sAdmin::sValidateVat
     */
    public function testsValidateVat()
    {
        // Test that null sVATCHECKENDABLED causes empty array return
        $this->config->offsetSet('sVATCHECKENDABLED', null);
        $this->assertEmpty($this->module->sValidateVat());
        $this->config->offsetSet('sVATCHECKENDABLED', true);

        // Test that null sVATCHECKREQUIRED causes empty array return
        $this->config->offsetSet('sVATCHECKREQUIRED', false);
        $this->assertEmpty($this->module->sValidateVat());
        $this->config->offsetSet('sVATCHECKREQUIRED', true);

        // Test that no tax id returns matching error
        $result = $this->module->sValidateVat();
        $this->assertCount(1, $result);
        $this->assertContains('VatFailureEmpty', $result);

        // Test that wrong tax id returns matching error
        $this->module->sSYSTEM->_POST['ustid'] = -1;
        $result = $this->module->sValidateVat();
        $this->assertCount(1, $result);
        $this->assertContains('VatFailureInvalid', $result);

        // Test that no country id returns matching error
        $this->module->sSYSTEM->_POST['ustid'] = 'DE123456789';
        $result = $this->module->sValidateVat();
        $this->assertCount(1, $result);
        $this->assertContains('VatFailureErrorField', $result);

        // Test basic validation is ok
        $this->module->sSYSTEM->_POST['country'] = '2';
        $this->assertCount(0, $this->module->sValidateVat());

        // Test that non-matching VAT prefix and country id returns error
        $this->module->sSYSTEM->_POST['country'] = '18';
        $result = $this->module->sValidateVat();
        $this->assertCount(1, $result);
        $this->assertContains('VatFailureErrorField', $result);

        /**
         * Tests using external service
         */

        // Test with invalid shop vat id
        $this->config->offsetSet('sVATCHECKADVANCEDNUMBER', 'DE123456789');
        $result = $this->module->sValidateVat();
        $this->assertCount(1, $result);
        $this->assertContains('VatFailureErrorField', $result);

        // Test that fake data throws an error
        $this->module->sSYSTEM->_POST['company'] = 'TestCompany';
        $this->module->sSYSTEM->_POST['Ort'] = 'TestLand';
        $this->module->sSYSTEM->_POST['PLZ'] = '48100';
        $this->module->sSYSTEM->_POST['Strasse'] = 'TestStreet';
        $this->module->sSYSTEM->_POST['country'] = '18';
        $this->module->sSYSTEM->_POST['ustid'] = 'LU20260743';
        $this->config->offsetSet('sVATCHECKADVANCEDNUMBER', 'DE813028812');
        $result = $this->module->sValidateVat();
        $this->assertCount(4, $result);
        $this->assertContains('VatFailureErrorField', $result);

        // Test that, by default, an unknown error is thrown
        $this->config->offsetSet('sVATCHECKADVANCEDNUMBER', false);
        $result = $this->module->sValidateVat();
        $this->assertCount(1, $result);
        $this->assertContains('VatFailureUnknownError', $result);
    }

    /**
     * @group knownFailing
     * @covers sAdmin::sValidateVat
     * @ticket SW-8169
     */
    public function testsValidateVatWithGermanVatId()
    {
        // Posted number is fake
        // Validation should fail
        $this->module->sSYSTEM->_POST['country'] = '2';
        $this->module->sSYSTEM->_POST['ustid'] = 'DE123456789';
        $this->config->offsetSet('sVATCHECKADVANCEDNUMBER', 'DE813028812');
        $this->assertCount(1, $this->module->sValidateVat());
    }

    /**
     * @group knownFailing
     * @covers sAdmin::sValidateVat
     * @ticket SW-8168
     */
    public function testsValidateVatWithForeignShopVatId()
    {
        // Both vat numbers are valid
        // http://services.amazon.de/service/nutzungsbedingungen.html
        // Validation should return true
        $this->module->sSYSTEM->_POST['country'] = '18';
        $this->module->sSYSTEM->_POST['ustid'] = 'LU19647148';
        $this->config->offsetSet('sVATCHECKADVANCEDNUMBER', 'LU20260743');
        $this->assertCount(0, $this->module->sValidateVat());
    }

    /**
     * @covers sAdmin::sGetPaymentMeanById
     */
    public function testsGetPaymentMeanById()
    {
        // Fetching non-existing payment means returns null
        $this->assertNull($this->module->sGetPaymentMeanById(0));

        // Fetching existing inactive payment means returns the data array
        $sepaData = $this->module->sGetPaymentMeanById(6);
        $this->assertInternalType('array', $sepaData);
        $this->assertArrayHasKey('id', $sepaData);
        $this->assertArrayHasKey('name', $sepaData);
        $this->assertArrayHasKey('description', $sepaData);
        $this->assertArrayHasKey('debit_percent', $sepaData);
        $this->assertArrayHasKey('surcharge', $sepaData);
        $this->assertArrayHasKey('surchargestring', $sepaData);
        $this->assertArrayHasKey('active', $sepaData);
        $this->assertArrayHasKey('esdactive', $sepaData);

        // Fetching existing active payment means returns the data array
        $debitData = $this->module->sGetPaymentMeanById(2);
        $this->assertInternalType('array', $debitData);
        $this->assertArrayHasKey('id', $debitData);
        $this->assertArrayHasKey('name', $debitData);
        $this->assertArrayHasKey('description', $debitData);
        $this->assertArrayHasKey('debit_percent', $debitData);
        $this->assertArrayHasKey('surcharge', $debitData);
        $this->assertArrayHasKey('surchargestring', $debitData);
        $this->assertArrayHasKey('active', $debitData);
        $this->assertArrayHasKey('esdactive', $debitData);

        $customer = $this->createDummyCustomer();
        $userData = array(
            "additional" => array(
                "user" => array(
                    "id" => $customer->getId(),
                    "paymentpreset" => 0
                )
            )
        );

        $this->assertEquals(0, $customer->getPaymentId());

        // Fetching active payment mean doesn't update user
        $this->module->sGetPaymentMeanById(2, $userData);
        $customerPaymentId = Shopware()->Db()->fetchOne(
            "SELECT paymentID FROM s_user WHERE id = ?",
            array($customer->getPaymentId())
        );
        $this->assertEquals(0, $customerPaymentId);

        // Test that payment method can be reset
        $this->module->sGetPaymentMeanById(6, $userData);
        $customerPaymentId = Shopware()->Db()->fetchOne(
            "SELECT paymentID FROM s_user WHERE id = ?",
            array($customer->getId())
        );
        $this->assertEquals(5, $customerPaymentId);

        $this->deleteDummyCustomer();
    }

    /**
     * @covers sAdmin::sGetPaymentMeans
     */
    public function testsGetPaymentMeans()
    {
        $result = $this->module->sGetPaymentMeans();
        foreach ($result as $paymentMean) {
            $this->assertArrayHasKey('id', $paymentMean);
            $this->assertArrayHasKey('name', $paymentMean);
            $this->assertArrayHasKey('description', $paymentMean);
            $this->assertArrayHasKey('debit_percent', $paymentMean);
            $this->assertArrayHasKey('surcharge', $paymentMean);
            $this->assertArrayHasKey('surchargestring', $paymentMean);
            $this->assertArrayHasKey('active', $paymentMean);
            $this->assertArrayHasKey('esdactive', $paymentMean);
            $this->assertContains($paymentMean['id'], array(2, 3, 5));
        }
    }

    /**
     * @covers sAdmin::sInitiatePaymentClass
     */
    public function testsInitiatePaymentClass()
    {
        $payments = Shopware()->Models()->getRepository('Shopware\Models\Payment\Payment')->findAll();

        foreach ($payments as $payment) {
            $paymentClass = $this->module->sInitiatePaymentClass($this->module->sGetPaymentMeanById($payment->getId()));
            if (is_bool($paymentClass)) {
                $this->assertFalse($paymentClass);
            } else {
                $this->assertInstanceOf('ShopwarePlugin\PaymentMethods\Components\BasePaymentMethod', $paymentClass);
                Shopware()->Front()->setRequest(new Enlight_Controller_Request_RequestHttp());

                $validationResult = $paymentClass->validate(Shopware()->Front()->Request());
                $this->assertTrue(is_array($validationResult));
                if(count($validationResult)) {
                    $this->assertArrayHasKey('sErrorFlag', $validationResult);
                    $this->assertArrayHasKey('sErrorMessages', $validationResult);
                }
            }
        }
    }

    /**
     * @covers sAdmin::sValidateStep3
     * @expectedException Enlight_Exception
     * @expectedExceptionMessage sValidateStep3 #00: No payment id
     */
    public function testExceptionInsValidateStep3()
    {
        $this->module->sValidateStep3();
    }

    /**
     * @covers sAdmin::sValidateStep3
     */
    public function testsValidateStep3()
    {
        $this->module->sSYSTEM->_POST = array(
            'sPayment' => 2
        );

        $result = $this->module->sValidateStep3();
        $this->assertArrayHasKey('checkPayment', $result);
        $this->assertArrayHasKey('paymentData', $result);
        $this->assertArrayHasKey('sProcessed', $result);
        $this->assertArrayHasKey('sPaymentObject', $result);

        $this->assertInternalType('array', $result['checkPayment']);
        $this->assertCount(2, $result['checkPayment']);
        $this->assertInternalType('array', $result['paymentData']);
        $this->assertCount(19, $result['paymentData']);
        $this->assertInternalType('boolean', $result['sProcessed']);
        $this->assertTrue($result['sProcessed']);
        $this->assertInternalType('object', $result['sPaymentObject']);
        $this->assertInstanceOf('ShopwarePlugin\PaymentMethods\Components\BasePaymentMethod', $result['sPaymentObject']);
    }

    /**
     * Create dummy customer entity
     *
     * @return \Shopware\Models\Customer\Customer
     */
    private function createDummyCustomer()
    {
        $date = new DateTime();
        $date->modify('-10 days');
        $firstlogin = $date->format(DateTime::ISO8601);

        $date->modify('+2 day');
        $lastlogin = $date->format(DateTime::ISO8601);

        $birthday = DateTime::createFromFormat('Y-m-d', '1986-12-20')->format(DateTime::ISO8601);

        $testData = array(
            "password" => "fooobar",
            "email"    => uniqid() . 'test@foobar.com',

            "firstlogin" => $firstlogin,
            "lastlogin"  => $lastlogin,

            "billing" => array(
                "firstName" => "Max",
                "lastName"  => "Mustermann",
                "birthday"  => $birthday,
                "attribute" => array(
                    'text1' => 'Freitext1',
                    'text2' => 'Freitext2',
                ),
            ),

            "shipping" => array(
                "salutation" => "Mr",
                "company"    => "Widgets Inc.",
                "firstName"  => "Max",
                "lastName"   => "Mustermann",
                "attribute" => array(
                    'text1' => 'Freitext1',
                    'text2' => 'Freitext2',
                ),
            ),

            "debit" => array(
                "account"       => "Fake Account",
                "bankCode"      => "55555555",
                "bankName"      => "Fake Bank",
                "accountHolder" => "Max Mustermann",
            ),
        );

        $customerResource = new \Shopware\Components\Api\Resource\Customer();
        $customerResource->setManager(Shopware()->Models());

        return $customerResource->create($testData);
    }

    /**
     * Deletes all dummy customer entity
     */
    private function deleteDummyCustomer()
    {
        Shopware()->Db()->query("DELETE FROM s_user WHERE email LIKE '%test@foobar.com'");
    }
}
