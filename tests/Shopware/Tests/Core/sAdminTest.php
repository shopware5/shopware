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

    /**
     * @var array The session data
     */
    private $session;

    /**
     * @var array The post data
     */
    private $post;

    public function setUp()
    {
        parent::setUp();

        $this->module = Shopware()->Modules()->Admin();
        $this->session = array();
        $this->post = array();
        $this->config = Shopware()->Config();
        $this->module->sSYSTEM->sCONFIG = &$this->config;
        $this->module->sSYSTEM->_SESSION = &$this->session;
        $this->module->sSYSTEM->_POST = &$this->post;

        // Create a stub for the Shopware_Components_Snippet_Manager class.
        $stub = $this->getMockBuilder('\Enlight_Components_Snippet_Manager')
            ->setMethods(array('get'))
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
        $this->post['ustid'] = -1;
        $result = $this->module->sValidateVat();
        $this->assertCount(1, $result);
        $this->assertContains('VatFailureInvalid', $result);

        // Test that no country id returns matching error
        $this->post['ustid'] = 'DE123456789';
        $result = $this->module->sValidateVat();
        $this->assertCount(1, $result);
        $this->assertContains('VatFailureErrorField', $result);

        // Test basic validation is ok
        $this->post['country'] = '2';
        $this->assertCount(0, $this->module->sValidateVat());

        // Test that non-matching VAT prefix and country id returns error
        $this->post['country'] = '18';
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
        $this->post['company'] = 'TestCompany';
        $this->post['Ort'] = 'TestLand';
        $this->post['PLZ'] = '48100';
        $this->post['Strasse'] = 'TestStreet';
        $this->post['country'] = '18';
        $this->post['ustid'] = 'LU20260743';
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
        $this->post['country'] = '2';
        $this->post['ustid'] = 'DE123456789';
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
        $this->post['country'] = '18';
        $this->post['ustid'] = 'LU19647148';
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

        $this->deleteDummyCustomer($customer);
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
        $this->post = array(
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
     * @covers sAdmin::sUpdateBilling
     */
    public function testsUpdateBilling()
    {
        $customer = $this->createDummyCustomer();
        $this->session['sUserId'] = $customer->getId();

        $this->post = array(
            'company' => 'TestCompany',
            'department' => 'TestDepartment',
            'salutation' => 'TestSalutation',
            'firstname' => 'TestFirstName',
            'lastname' => 'TestLastName',
            'street' => 'TestStreet',
            'streetnumber' => 'TestStreetNumber',
            'zipcode' => 'TestZip',
            'city' => 'TestCity',
            'phone' => 'TestPhone',
            'fax' => 'TestFax',
            'country' => '2',
            'stateID' => '1',
            'ustid' => 'TestUstId',
            'birthday' => '21',
            'birthmonth' => '10',
            'birthyear' => '1998',
            'text1' => 'TestText1',
            'text2' => 'TestText2',
            'text3' => 'TestText3',
            'text4' => 'TestText4',
            'text5' => 'TestText5',
            'text6' => 'TestText6'
        );

        $this->assertTrue($this->module->sUpdateBilling());
        $result = Shopware()->Db()->fetchRow('
            SELECT *

            FROM s_user_billingaddress
            LEFT JOIN s_user_billingaddress_attributes
            ON s_user_billingaddress.id = s_user_billingaddress_attributes.billingID

            WHERE s_user_billingaddress.userID = ?
        ', array($customer->getId()));


        // Prepare testData for comparison
        $this->post['countryID'] = $this->post['country'];
        unset($this->post['country']);
        $this->post['birthday'] = mktime(0,0,0, (int) $this->post['birthmonth'], (int) $this->post['birthday'], (int) $this->post['birthyear']);
        $this->post['birthday'] = '1998-10-21';
        unset($this->post['birthmonth']);
        unset($this->post['birthyear']);

        $this->assertArrayHasKey('id', $result);
        foreach ($this->post as $key => $value) {
            $this->assertEquals($value, $result[$key]);
        }

        $this->deleteDummyCustomer($customer);
    }

    /**
     * @covers sAdmin::sUpdateNewsletter
     */
    public function testsUpdateNewsletter()
    {
        $email = uniqid() . 'test@foobar.com';

        // Test insertion
        $this->assertTrue($this->module->sUpdateNewsletter(true, $email));
        $newsletterSubscription = Shopware()->Db()->fetchRow(
            'SELECT * FROM s_campaigns_mailaddresses WHERE email = ?',
            array($email)
        );
        $this->assertNotNull($newsletterSubscription);
        $this->assertEquals(0, $newsletterSubscription['customer']);
        $this->assertEquals(1, $newsletterSubscription['groupID']);

        // Test removal
        $this->assertTrue($this->module->sUpdateNewsletter(false, $email));
        $newsletterSubscription = Shopware()->Db()->fetchRow(
            'SELECT * FROM s_campaigns_mailaddresses WHERE email = ?',
            array($email)
        );
        $this->assertFalse($newsletterSubscription);


        // Retest insertion for customers
        $this->assertTrue($this->module->sUpdateNewsletter(true, $email, true));
        $newsletterSubscription = Shopware()->Db()->fetchRow(
            'SELECT * FROM s_campaigns_mailaddresses WHERE email = ?',
            array($email)
        );
        $this->assertNotNull($newsletterSubscription);
        $this->assertEquals(1, $newsletterSubscription['customer']);
        $this->assertEquals(0, $newsletterSubscription['groupID']);

        // Test removal
        $this->assertTrue($this->module->sUpdateNewsletter(false, $email));
        $newsletterSubscription = Shopware()->Db()->fetchRow(
            'SELECT * FROM s_campaigns_mailaddresses WHERE email = ?',
            array($email)
        );
        $this->assertFalse($newsletterSubscription);
    }

    /**
     * @covers sAdmin::sGetPreviousAddresses
     */
    public function testsGetPreviousAddresses()
    {
        $customer = $this->createDummyCustomer();
        $this->session['sUserId'] = null;

        // Test no user id
        $this->assertFalse($this->module->sGetPreviousAddresses('shipping'));

        $this->session['sUserId'] = $customer->getId();

        // Test empty argument scenario
        $this->assertFalse($this->module->sGetPreviousAddresses(''));

        // Test fetching for new customer with no order (should return empty)
        $this->assertCount(0, $this->module->sGetPreviousAddresses('shipping'));
        $this->assertCount(0, $this->module->sGetPreviousAddresses('billing'));

        $this->deleteDummyCustomer($customer);

        // Test with existing demo customer data
        $this->session['sUserId'] = 1;

        $shippingData = $this->module->sGetPreviousAddresses('shipping');
        $billingData = $this->module->sGetPreviousAddresses('billing');
        $this->assertCount(1, $shippingData);
        $this->assertCount(1, $billingData);

        $shippingDetails = end($shippingData);
        $billingDetails = end($billingData);

        $this->assertArrayHasKey('hash', $shippingDetails);
        $this->assertArrayHasKey('hash', $billingDetails);

        $this->assertEquals($shippingDetails, $this->module->sGetPreviousAddresses('shipping', $shippingDetails['hash']));
        $this->assertEquals($billingDetails, $this->module->sGetPreviousAddresses('billing', $billingDetails['hash']));

        foreach(array($shippingDetails, $billingDetails) as $details) {
            $this->assertInternalType('array', $details);
            $this->assertCount(13, $details);
            $this->assertArrayHasKey('company', $details);
            $this->assertArrayHasKey('department', $details);
            $this->assertArrayHasKey('salutation', $details);
            $this->assertArrayHasKey('firstname', $details);
            $this->assertArrayHasKey('lastname', $details);
            $this->assertArrayHasKey('street', $details);
            $this->assertArrayHasKey('streetnumber', $details);
            $this->assertArrayHasKey('zipcode', $details);
            $this->assertArrayHasKey('city', $details);
            $this->assertArrayHasKey('country', $details);
            $this->assertArrayHasKey('countryID', $details);
            $this->assertArrayHasKey('countryname', $details);

            $this->assertNotEmpty($details['hash']);
            $this->assertEquals('shopware AG', $details['company']);
            $this->assertEquals('', $details['department']);
            $this->assertEquals('mr', $details['salutation']);
            $this->assertEquals('Max', $details['firstname']);
            $this->assertEquals('Mustermann', $details['lastname']);
            $this->assertEquals('Mustermannstraße', $details['street']);
            $this->assertEquals('92', $details['streetnumber']);
            $this->assertEquals('48624', $details['zipcode']);
            $this->assertEquals('Schöppingen', $details['city']);
            $this->assertEquals('2', $details['country']);
            $this->assertEquals('2', $details['countryID']);
            $this->assertEquals('Deutschland', $details['countryname']);
        }
    }

    /**
     * @covers sAdmin::sUpdateShipping
     */
    public function testsUpdateShipping()
    {
        // Test no user id
        $this->assertFalse($this->module->sUpdateShipping());

        $customer = $this->createDummyCustomer();
        $this->session['sUserId'] = $customer->getId();

        // With user id but with no data, operation is successful
        $this->assertTrue($this->module->sUpdateShipping());

        // Setup dummy test data and test with it
        $this->post = array(
            'company' => 'Testcompany',
            'department' => 'Testdepartment',
            'salutation' => 'Testsalutation',
            'firstname' => 'Testfirstname',
            'lastname' => 'Testlastname',
            'street' => 'Teststreet',
            'streetnumber' => 'Teststreetnumber',
            'zipcode' => 'Testzipcode',
            'city' => 'Testcity',
            'country' => '2',
            'stateID' => '4',
            'text1' => 'TestText1',
            'text2' => 'TestText2',
            'text3' => 'TestText3',
            'text4' => 'TestText4',
            'text5' => 'TestText5',
            'text6' => 'TestText6'
        );
        $this->assertTrue($this->module->sUpdateShipping());

        $result = Shopware()->Db()->fetchRow('
            SELECT *

            FROM s_user_shippingaddress
            LEFT JOIN s_user_shippingaddress_attributes
            ON s_user_shippingaddress.id = s_user_shippingaddress_attributes.shippingID

            WHERE s_user_shippingaddress.userID = ?
        ', array($customer->getId()));

        // Prepare testData for comparison
        $this->post['countryID'] = $this->post['country'];
        unset($this->post['country']);

        $this->assertArrayHasKey('id', $result);
        foreach ($this->post as $key => $value) {
            $this->assertEquals($value, $result[$key]);
        }

        $this->deleteDummyCustomer($customer);
    }

    /**
     * @covers sAdmin::sUpdatePayment
     */
    public function testsUpdatePayment()
    {
        // Test no user id
        $this->assertFalse($this->module->sUpdatePayment());

        $customer = $this->createDummyCustomer();
        $this->session['sUserId'] = $customer->getId();

        // Test that operation succeeds even without payment id
        $this->assertTrue($this->module->sUpdatePayment());
        $this->assertEquals(
            0,
            Shopware()->Db()->fetchOne('SELECT paymentID FROM s_user WHERE id = ?', array($customer->getId()))
        );

        // Setup dummy test data and test with it
        $this->post = array(
            'sPayment' => 2
        );
        $this->assertTrue($this->module->sUpdatePayment());
        $this->assertEquals(
            2,
            Shopware()->Db()->fetchOne('SELECT paymentID FROM s_user WHERE id = ?', array($customer->getId()))
        );

        $this->deleteDummyCustomer($customer);
    }

    /**
     * @covers sAdmin::sUpdateAccount
     */
    public function testsUpdateAccount()
    {
        // Test no user id
        $this->assertTrue($this->module->sUpdateAccount());

        $customer = $this->createDummyCustomer();
        $this->session['sUserId'] = $customer->getId();
        $this->post['email'] = uniqid() . 'test@foobar.com';

        $this->assertTrue($this->module->sUpdateAccount());

        // Test that email was updated
        $this->assertNotEquals(
            $customer->getEmail(),
            Shopware()->Db()->fetchOne('SELECT email FROM s_user WHERE id = ?', array($customer->getId()))
        );

        $this->post['password'] = uniqid() . 'password';
        $this->post['passwordConfirmation'] = $this->post['password'];

        $this->assertTrue($this->module->sUpdateAccount());

        // Test that password was updated
        $this->assertNotEquals(
            $customer->getPassword(),
            Shopware()->Db()->fetchOne('SELECT password FROM s_user WHERE id = ?', array($customer->getId()))
        );

        $this->deleteDummyCustomer($customer);
    }

    /**
     * @covers sAdmin::sValidateStep2
     */
    public function testsValidateStep2()
    {
        // Test with no rules, should always validate
        $result = $this->module->sValidateStep2(array());
        $this->assertInternalType('array', $result);
        $this->assertArrayHasKey('sErrorFlag', $result);
        $this->assertArrayHasKey('sErrorMessages', $result);
        $this->assertCount(0, $result['sErrorFlag']);
        $this->assertCount(0, $result['sErrorMessages']);

        $testRuleSet = array(
            'testField1' => array('required' => 1),
            'testField2' => array('required' => 0),
            'testField3' => array('required' => 1),
        );

        // Test failing validation, should have 2 failing fields
        $result = $this->module->sValidateStep2($testRuleSet);
        $this->assertInternalType('array', $result);
        $this->assertArrayHasKey('sErrorFlag', $result);
        $this->assertArrayHasKey('sErrorMessages', $result);
        $this->assertCount(2, $result['sErrorFlag']);
        $this->assertArrayHasKey('testField1', $result['sErrorFlag']);
        $this->assertArrayHasKey('testField3', $result['sErrorFlag']);
        $this->assertCount(1, $result['sErrorMessages']);


        // Setup dummy test data and test with it, see it passes
        $this->post = array(
            'testField1' => 'testValue',
            'testField2' => 'testValue',
            'testField3' => 'testValue',
        );
        $result = $this->module->sValidateStep2($testRuleSet);
        $this->assertInternalType('array', $result);
        $this->assertArrayHasKey('sErrorFlag', $result);
        $this->assertArrayHasKey('sErrorMessages', $result);
        $this->assertCount(0, $result['sErrorFlag']);
        $this->assertCount(0, $result['sErrorMessages']);

        // Test that using vat id will trigger aux function to validate it
        $this->config->offsetSet('sVATCHECKENDABLED', true);
        $testRuleSet['ustid'] = array('required' => 1);
        $this->post['ustid'] = '12345';
        $result = $this->module->sValidateStep2($testRuleSet);
        $this->assertInternalType('array', $result);
        $this->assertArrayHasKey('sErrorFlag', $result);
        $this->assertArrayHasKey('sErrorMessages', $result);
        $this->assertCount(1, $result['sErrorFlag']);
        $this->assertCount(1, $result['sErrorMessages']);
        $this->assertContains('VatFailureInvalid', $result['sErrorFlag']);
        $this->assertContains('VatFailureErrorInfo', $result['sErrorFlag']);
    }

    /**
     * @covers sAdmin::sValidateStep2ShippingAddress
     */
    public function testsValidateStep2ShippingAddress()
    {
        // Test with no rules, should always validate
        $result = $this->module->sValidateStep2ShippingAddress(array());
        $this->assertInternalType('array', $result);
        $this->assertArrayHasKey('sErrorFlag', $result);
        $this->assertArrayHasKey('sErrorMessages', $result);
        $this->assertNull($result['sErrorFlag']);
        $this->assertNull($result['sErrorMessages']);

        $testRuleSet = array(
            'testField1' => array('required' => 1),
            'testField2' => array('required' => 0),
            'testField3' => array('required' => 1),
        );

        // Test failing validation, should have 2 failing fields
        $result = $this->module->sValidateStep2ShippingAddress($testRuleSet);
        $this->assertInternalType('array', $result);
        $this->assertArrayHasKey('sErrorFlag', $result);
        $this->assertArrayHasKey('sErrorMessages', $result);
        $this->assertCount(2, $result['sErrorFlag']);
        $this->assertArrayHasKey('testField1', $result['sErrorFlag']);
        $this->assertArrayHasKey('testField3', $result['sErrorFlag']);
        $this->assertCount(1, $result['sErrorMessages']);


        // Setup dummy test data and test with it, see it passes
        $this->post = array(
            'testField1' => 'testValue',
            'testField2' => 'testValue',
            'testField3' => 'testValue',
        );
        $result = $this->module->sValidateStep2ShippingAddress($testRuleSet);
        $this->assertInternalType('array', $result);
        $this->assertArrayHasKey('sErrorFlag', $result);
        $this->assertArrayHasKey('sErrorMessages', $result);
        $this->assertNull($result['sErrorFlag']);
        $this->assertNull($result['sErrorMessages']);
    }

    /**
     * @covers sAdmin::sValidateStep1
     */
    public function testsValidateStep1WithoutEdit()
    {
        // Test with no data, should fail on password field
        $result = $this->module->sValidateStep1();
        $this->assertInternalType('array', $result);
        $this->assertArrayHasKey('sErrorFlag', $result);
        $this->assertArrayHasKey('sErrorMessages', $result);
        $this->assertCount(1, $result['sErrorMessages']);
        $this->assertContains(
            'Bitte w&auml;hlen Sie ein Passwort welches aus mindestens {config name="MinPassword"} Zeichen besteht.',
            $result['sErrorMessages']
        );
        $this->assertCount(2, $result['sErrorFlag']);
        $this->assertArrayHasKey('password', $result['sErrorFlag']);
        $this->assertArrayHasKey('passwordConfirmation', $result['sErrorFlag']);

        // Test with diverging password, should fail
        $this->post = array(
            'password' => 'password',
            'passwordConfirmation' => 'passwordConfirmation',
        );
        $result = $this->module->sValidateStep1();
        $this->assertInternalType('array', $result);
        $this->assertArrayHasKey('sErrorFlag', $result);
        $this->assertArrayHasKey('sErrorMessages', $result);
        $this->assertCount(1, $result['sErrorMessages']);
        $this->assertContains(
            'Die Passwörter stimmen nicht überein.',
            $result['sErrorMessages']
        );
        $this->assertCount(2, $result['sErrorFlag']);
        $this->assertArrayHasKey('password', $result['sErrorFlag']);
        $this->assertArrayHasKey('passwordConfirmation', $result['sErrorFlag']);

        // Test with matching passwords, should succeed
        $this->post = array(
            'password' => 'password',
            'passwordConfirmation' => 'password',
        );
        $result = $this->module->sValidateStep1();
        $this->assertInternalType('array', $result);
        $this->assertArrayHasKey('sErrorFlag', $result);
        $this->assertArrayHasKey('sErrorMessages', $result);
        $this->assertNull($result['sErrorMessages']);
        $this->assertNull($result['sErrorFlag']);

        // Test with invalid email, should fail
        $this->post['email'] = 'failmail.com';
        $result = $this->module->sValidateStep1();
        $this->assertInternalType('array', $result);
        $this->assertArrayHasKey('sErrorFlag', $result);
        $this->assertArrayHasKey('sErrorMessages', $result);
        $this->assertCount(1, $result['sErrorMessages']);
        $this->assertContains(
            'MailFailure',
            $result['sErrorMessages']
        );
        $this->assertCount(1, $result['sErrorFlag']);
        $this->assertArrayHasKey('email', $result['sErrorFlag']);

        // Test with valid email, should succeed
        $this->post['email'] = 'foo@failmail.com';
        $result = $this->module->sValidateStep1();
        $this->assertInternalType('array', $result);
        $this->assertArrayHasKey('sErrorFlag', $result);
        $this->assertArrayHasKey('sErrorMessages', $result);
        $this->assertNull($result['sErrorMessages']);
        $this->assertNull($result['sErrorFlag']);

        // Test with diverging emailConfirmation and email, should fail
        $this->post['emailConfirmation'] = 'bar@failmail.com';
        $result = $this->module->sValidateStep1();
        $this->assertInternalType('array', $result);
        $this->assertArrayHasKey('sErrorFlag', $result);
        $this->assertArrayHasKey('sErrorMessages', $result);
        $this->assertCount(1, $result['sErrorMessages']);
        $this->assertContains(
            'MailFailureNotEqual',
            $result['sErrorMessages']
        );
        $this->assertCount(1, $result['sErrorFlag']);
        $this->assertArrayHasKey('emailConfirmation', $result['sErrorFlag']);

        // Test with valid email, should succeed
        $this->post['emailConfirmation'] = 'foo@failmail.com';
        $result = $this->module->sValidateStep1();
        $this->assertInternalType('array', $result);
        $this->assertArrayHasKey('sErrorFlag', $result);
        $this->assertArrayHasKey('sErrorMessages', $result);
        $this->assertNull($result['sErrorMessages']);
        $this->assertNull($result['sErrorFlag']);

        // Test that session data is correctly set
        $sessionRegister = $this->session["sRegister"]['auth'];
        $this->assertEquals(0, $sessionRegister['accountmode']);
        $this->assertNull($sessionRegister['receiveNewsletter']);
        $this->assertEquals('foo@failmail.com', $sessionRegister['email']);
        $this->assertEquals('bcrypt', $sessionRegister['encoderName']);
        $this->assertArrayHasKey('password', $sessionRegister);

        // Test with skipLogin
        $this->post['skipLogin'] = true;
        $this->module->sValidateStep1();
        $sessionRegister = $this->session["sRegister"]['auth'];
        $this->assertEquals(1, $sessionRegister['accountmode']);
        $this->assertNull($sessionRegister['receiveNewsletter']);
        $this->assertEquals('foo@failmail.com', $sessionRegister['email']);
        $this->assertEquals('md5', $sessionRegister['encoderName']);
        $this->assertArrayHasKey('password', $sessionRegister);
    }

    /**
     * @covers sAdmin::sValidateStep1
     */
    public function testsValidateStep1WithEdit()
    {
        $customer = $this->createDummyCustomer();

        // Test with no data
        $result = $this->module->sValidateStep1(true);
        $this->assertInternalType('array', $result);
        $this->assertArrayHasKey('sErrorFlag', $result);
        $this->assertArrayHasKey('sErrorMessages', $result);
        $this->assertCount(1, $result['sErrorMessages']);
        $this->assertContains(
            'Das aktuelle Passwort stimmt nicht!',
            $result['sErrorMessages']
        );
        $this->assertCount(2, $result['sErrorFlag']);
        $this->assertArrayHasKey('email', $result['sErrorFlag']);
        $this->assertArrayHasKey('currentPassword', $result['sErrorFlag']);

        // Test with correct basic data, wrong password
        // First, hack session data with correct data
        $this->session["sUserMail"] = $customer->getEmail();
        Shopware()->Session()->offsetSet(
            "sUserPassword",
            Shopware()->PasswordEncoder()->encodePassword("fooobar", 'bcrypt')
        );
        // Then set post with wrong data
        $this->post = array(
            'email' => $customer->getEmail(),
            'currentPassword' => 'password',
        );
        $result = $this->module->sValidateStep1(true);
        $this->assertInternalType('array', $result);
        $this->assertArrayHasKey('sErrorFlag', $result);
        $this->assertArrayHasKey('sErrorMessages', $result);
        $this->assertCount(1, $result['sErrorMessages']);
        $this->assertContains(
            'Das aktuelle Passwort stimmt nicht!',
            $result['sErrorMessages']
        );
        $this->assertCount(2, $result['sErrorFlag']);
        $this->assertArrayHasKey('email', $result['sErrorFlag']);
        $this->assertArrayHasKey('currentPassword', $result['sErrorFlag']);

        // Now use correct data to test correct behavior
        $this->post['currentPassword'] = 'fooobar';
        $result = $this->module->sValidateStep1(true);
        $this->assertInternalType('array', $result);
        $this->assertArrayHasKey('sErrorFlag', $result);
        $this->assertArrayHasKey('sErrorMessages', $result);
        $this->assertNull($result['sErrorMessages']);
        $this->assertNull($result['sErrorFlag']);

        $this->deleteDummyCustomer($customer);
    }

    /**
     * @covers sAdmin::sLogin
     */
    public function testsLogin()
    {
        // Test with no data, get error
        $result = $this->module->sLogin();
        $this->assertInternalType('array', $result);
        $this->assertArrayHasKey('sErrorFlag', $result);
        $this->assertArrayHasKey('sErrorMessages', $result);
        $this->assertCount(1, $result['sErrorMessages']);
        $this->assertContains('LoginFailure', $result['sErrorMessages']);
        $this->assertCount(2, $result['sErrorFlag']);
        $this->assertArrayHasKey('email', $result['sErrorFlag']);
        $this->assertArrayHasKey('password', $result['sErrorFlag']);

        // Test with wrong data, get error
        $this->post = array(
            'email' => uniqid() . 'test',
            'password' => uniqid() . 'test',
        );
        $result = $this->module->sLogin();
        $this->assertInternalType('array', $result);
        $this->assertArrayHasKey('sErrorFlag', $result);
        $this->assertArrayHasKey('sErrorMessages', $result);
        $this->assertCount(1, $result['sErrorMessages']);
        $this->assertContains('LoginFailure', $result['sErrorMessages']);
        $this->assertNull($result['sErrorFlag']);

        $customer = $this->createDummyCustomer();

        // Test successful login
        $this->post = array(
            'email' => $customer->getEmail(),
            'password' => 'fooobar',
        );
        $result = $this->module->sLogin();
        $this->assertInternalType('array', $result);
        $this->assertArrayHasKey('sErrorFlag', $result);
        $this->assertArrayHasKey('sErrorMessages', $result);
        $this->assertNull($result['sErrorFlag']);
        $this->assertNull($result['sErrorMessages']);

        // Test wrong pre-hashed password. Need a user with md5 encoded password
        Shopware()->Db()->update(
            's_user',
            array(
                'password' => md5('fooobar'),
                'encoder' => 'md5'
            ),
            'id = '.$customer->getId()
        );

        $this->post = array(
            'email' => $customer->getEmail(),
            'passwordMD5' => uniqid(),
        );
        $result = $this->module->sLogin(true);
        $this->assertInternalType('array', $result);
        $this->assertArrayHasKey('sErrorFlag', $result);
        $this->assertArrayHasKey('sErrorMessages', $result);
        $this->assertNull($result['sErrorFlag']);
        $this->assertCount(1, $result['sErrorMessages']);
        $this->assertContains('LoginFailure', $result['sErrorMessages']);

        // Test correct pre-hashed password
        $this->post = array(
            'email' => $customer->getEmail(),
            'passwordMD5' => md5('fooobar'),
        );
        $result = $this->module->sLogin(true);
        $this->assertInternalType('array', $result);
        $this->assertArrayHasKey('sErrorFlag', $result);
        $this->assertArrayHasKey('sErrorMessages', $result);
        $this->assertNull($result['sErrorFlag']);
        $this->assertNull($result['sErrorMessages']);

        $modifiedMd5User = Shopware()->Db()->fetchRow(
            'SELECT * FROM s_user WHERE id = ?',
            array($customer->getId())
        );

        // Test that it's the same user, but with different last login
        $this->assertEquals($modifiedMd5User['email'], $customer->getEmail());
        $this->assertEquals($modifiedMd5User['password'], md5('fooobar'));
        $this->assertNotEquals($modifiedMd5User['lastlogin'], $customer->getLastLogin()->format('Y-m-d H:i:s'));

        // Test inactive account
        Shopware()->Db()->update('s_user', array('active' => 0), 'id = '.$customer->getId());
        $result = $this->module->sLogin(true);
        $this->assertInternalType('array', $result);
        $this->assertArrayHasKey('sErrorFlag', $result);
        $this->assertArrayHasKey('sErrorMessages', $result);
        $this->assertNull($result['sErrorFlag']);
        $this->assertCount(1, $result['sErrorMessages']);
        $this->assertContains('LoginFailureActive', $result['sErrorMessages']);

        // Test brute force lockout
        Shopware()->Db()->update('s_user', array('active' => 1), 'id = '.$customer->getId());
        $this->post = array(
            'email' => $customer->getEmail(),
            'password' => 'asasasasas',
        );
        $this->module->sLogin();
        $this->module->sLogin();
        $this->module->sLogin();
        $this->module->sLogin();
        $this->module->sLogin();
        $this->module->sLogin();
        $this->module->sLogin();
        $this->module->sLogin();
        $this->module->sLogin();
        $result = $this->module->sLogin();
        $this->assertInternalType('array', $result);
        $this->assertArrayHasKey('sErrorFlag', $result);
        $this->assertArrayHasKey('sErrorMessages', $result);
        $this->assertNull($result['sErrorFlag']);
        $this->assertCount(1, $result['sErrorMessages']);
        $this->assertContains('LoginFailureLocked', $result['sErrorMessages']);

        $this->deleteDummyCustomer($customer);
    }

    /**
     * @covers sAdmin::sCheckUser
     */
    public function testsCheckUser()
    {
        $customer = $this->createDummyCustomer();

        // Basic failing case
        $this->assertFalse($this->module->sCheckUser());

        // Test successful login
        $this->post = array(
            'email' => $customer->getEmail(),
            'password' => 'fooobar',
        );
        $result = $this->module->sLogin();
        $this->assertInternalType('array', $result);
        $this->assertArrayHasKey('sErrorFlag', $result);
        $this->assertArrayHasKey('sErrorMessages', $result);
        $this->assertNull($result['sErrorFlag']);
        $this->assertNull($result['sErrorMessages']);

        // Test that user is correctly logged in
        $this->assertTrue($this->module->sCheckUser());

        // Force timeout
        Shopware()->Db()->update('s_user', array('lastlogin' => '2000-01-01 00:00:00'), 'id = '.$customer->getId());
        $this->assertFalse($this->module->sCheckUser());

        $this->assertEquals($customer->getGroup()->getKey(), $this->session['sUserGroup']);
        $this->assertInternalType('array', $this->session['sUserGroupData']);
        $this->assertArrayHasKey('groupkey', $this->session['sUserGroupData']);
        $this->assertArrayHasKey('description', $this->session['sUserGroupData']);
        $this->assertArrayHasKey('tax', $this->session['sUserGroupData']);
        $this->assertArrayHasKey('taxinput', $this->session['sUserGroupData']);
        $this->assertArrayHasKey('mode', $this->session['sUserGroupData']);
        $this->assertArrayHasKey('discount', $this->session['sUserGroupData']);
        $this->assertArrayHasKey('minimumorder', $this->session['sUserGroupData']);
        $this->assertArrayHasKey('minimumordersurcharge', $this->session['sUserGroupData']);

        $this->deleteDummyCustomer($customer);
    }

    /**
     * @covers sAdmin::sGetCountryTranslation
     */
    public function testsGetCountryTranslation()
    {
        // Backup existing data and inject demo data
        $existingData = Shopware()->Db()->fetchRow("
            SELECT * FROM s_core_translations
            WHERE objecttype = 'config_countries' AND objectlanguage = 2
        ");

        $demoData = array(
            'objectkey' => 1,
            'objectlanguage' => 2,
            'objecttype' => 'config_countries',
            'objectdata' => 'a:2:{i:2;a:2:{s:6:"active";s:1:"1";s:11:"countryname";s:7:"Germany";}i:5;a:2:{s:6:"active";s:1:"1";s:11:"countryname";s:7:"Belgium";}}'
        );

        if($existingData) {
            Shopware()->Db()->update('s_core_translations', $demoData, 'id = '.$existingData['id']);
        } else {
            Shopware()->Db()->insert('s_core_translations', $demoData);
        }

        // Test loading all data, should return the test data
        $this->module->sSYSTEM->sLanguage = 2;
        $result = $this->module->sGetCountryTranslation();
        $this->assertCount(2, $result);
        $this->assertArrayHasKey(2, $result);
        $this->assertArrayHasKey(5, $result);
        $this->assertArrayHasKey('active', $result[2]);
        $this->assertArrayHasKey('countryname', $result[2]);
        $this->assertEquals(1, $result[2]['active']);
        $this->assertEquals('Germany', $result[2]['countryname']);
        $this->assertArrayHasKey('active', $result[5]);
        $this->assertArrayHasKey('countryname', $result[5]);
        $this->assertEquals(1, $result[5]['active']);
        $this->assertEquals('Belgium', $result[5]['countryname']);

        // Test with just one country
        $result = $this->module->sGetCountryTranslation(array('id' => 2, 'randomField' => 'randomValue'));
        $this->assertCount(4, $result);
        $this->assertArrayHasKey('id', $result);
        $this->assertArrayHasKey('active', $result);
        $this->assertArrayHasKey('countryname', $result);
        $this->assertArrayHasKey('randomField', $result);
        $this->assertEquals(2, $result['id']);
        $this->assertEquals(1, $result['active']);
        $this->assertEquals('Germany', $result['countryname']);
        $this->assertEquals('randomValue', $result['randomField']);

        // If backup data exists, restore it
        if($existingData) {
            $existingDataId = $existingData['id'];
            unset($existingData['id']);
            Shopware()->Db()->update('s_core_translations', $existingData, 'id = '.$existingDataId);
        }
    }

    /**
     * @covers sAdmin::sGetDispatchTranslation
     */
    public function testsGetDispatchTranslation()
    {
        // Backup existing data and inject demo data
        $existingData = Shopware()->Db()->fetchRow("
            SELECT * FROM s_core_translations
            WHERE objecttype = 'config_dispatch' AND objectlanguage = 2
        ");

        $demoData = array(
            'objectkey' => 1,
            'objectlanguage' => 2,
            'objecttype' => 'config_dispatch',
            'objectdata' => 'a:2:{i:9;a:3:{s:13:"dispatch_name";s:17:"Standard shipping";s:20:"dispatch_description";s:29:"Standard shipping description";s:20:"dispatch_status_link";s:18:"http://www.dhl.com";}i:10;a:3:{s:13:"dispatch_name";s:18:"Shipping by weight";s:20:"dispatch_description";s:30:"Shipping by weight description";s:20:"dispatch_status_link";s:3:"url";}}'
        );

        if($existingData) {
            Shopware()->Db()->update('s_core_translations', $demoData, 'id = '.$existingData['id']);
        } else {
            Shopware()->Db()->insert('s_core_translations', $demoData);
        }

        // Test loading all data, should return the test data
        $this->module->sSYSTEM->sLanguage = 2;
        $result = $this->module->sGetDispatchTranslation();
        $this->assertCount(2, $result);
        $this->assertArrayHasKey(9, $result);
        $this->assertArrayHasKey(10, $result);
        $this->assertArrayHasKey('dispatch_name', $result[9]);
        $this->assertArrayHasKey('dispatch_description', $result[9]);
        $this->assertArrayHasKey('dispatch_status_link', $result[9]);
        $this->assertArrayHasKey('dispatch_name', $result[10]);
        $this->assertArrayHasKey('dispatch_description', $result[10]);
        $this->assertArrayHasKey('dispatch_status_link', $result[10]);
        $this->assertEquals('Standard shipping', $result[9]['dispatch_name']);
        $this->assertEquals('Standard shipping description', $result[9]['dispatch_description']);
        $this->assertEquals('http://www.dhl.com', $result[9]['dispatch_status_link']);
        $this->assertEquals('Shipping by weight', $result[10]['dispatch_name']);
        $this->assertEquals('Shipping by weight description', $result[10]['dispatch_description']);
        $this->assertEquals('url', $result[10]['dispatch_status_link']);

        // Test with just one shipping method
        $result = $this->module->sGetDispatchTranslation(array('id' => 9, 'randomField' => 'randomValue'));
        $this->assertCount(5, $result);
        $this->assertArrayHasKey('id', $result);
        $this->assertArrayHasKey('name', $result);
        $this->assertArrayHasKey('description', $result);
        $this->assertArrayHasKey('status_link', $result);
        $this->assertArrayHasKey('randomField', $result);
        $this->assertEquals(9, $result['id']);
        $this->assertEquals('Standard shipping', $result['name']);
        $this->assertEquals('Standard shipping description', $result['description']);
        $this->assertEquals('http://www.dhl.com', $result['status_link']);
        $this->assertEquals('randomValue', $result['randomField']);

        // If backup data exists, restore it
        if($existingData) {
            $existingDataId = $existingData['id'];
            unset($existingData['id']);
            Shopware()->Db()->update('s_core_translations', $existingData, 'id = '.$existingDataId);
        }
    }

    /**
     * @covers sAdmin::sGetPaymentTranslation
     */
    public function testsGetPaymentTranslation()
    {
        // Backup existing data and inject demo data
        $existingData = Shopware()->Db()->fetchRow("
            SELECT * FROM s_core_translations
            WHERE objecttype = 'config_payment' AND objectlanguage = 2
        ");

        $demoData = array(
            'objectkey' => 1,
            'objectlanguage' => 2,
            'objecttype' => 'config_payment',
            'objectdata' => 'a:5:{i:4;a:2:{s:11:"description";s:7:"Invoice";s:21:"additionalDescription";s:141:"Payment by invoice. Shopware provides automatic invoicing for all customers on orders after the first, in order to avoid defaults on payment.";}i:2;a:2:{s:11:"description";s:5:"Debit";s:21:"additionalDescription";s:15:"Additional text";}i:3;a:2:{s:11:"description";s:16:"Cash on delivery";s:21:"additionalDescription";s:25:"(including 2.00 Euro VAT)";}i:5;a:2:{s:11:"description";s:15:"Paid in advance";s:21:"additionalDescription";s:57:"The goods are delivered directly upon receipt of payment.";}i:6;a:1:{s:21:"additionalDescription";s:17:"SEPA direct debit";}}'
        );

        if($existingData) {
            Shopware()->Db()->update('s_core_translations', $demoData, 'id = '.$existingData['id']);
        } else {
            Shopware()->Db()->insert('s_core_translations', $demoData);
        }

        // Test loading all data, should return the test data
        $this->module->sSYSTEM->sLanguage = 2;
        $result = $this->module->sGetPaymentTranslation();
        $this->assertCount(5, $result);
        $this->assertArrayHasKey(2, $result);
        $this->assertArrayHasKey(3, $result);
        $this->assertArrayHasKey(4, $result);
        $this->assertArrayHasKey(5, $result);
        $this->assertArrayHasKey(6, $result);
        $this->assertArrayHasKey('description', $result[2]);
        $this->assertArrayHasKey('additionalDescription', $result[2]);
        $this->assertArrayHasKey('description', $result[3]);
        $this->assertArrayHasKey('additionalDescription', $result[3]);
        $this->assertArrayHasKey('description', $result[5]);
        $this->assertArrayHasKey('additionalDescription', $result[5]);
        $this->assertEquals('Debit', $result[2]['description']);
        $this->assertEquals('Additional text', $result[2]['additionalDescription']);
        $this->assertEquals('Cash on delivery', $result[3]['description']);
        $this->assertEquals('(including 2.00 Euro VAT)', $result[3]['additionalDescription']);
        $this->assertEquals('Paid in advance', $result[5]['description']);
        $this->assertEquals('The goods are delivered directly upon receipt of payment.', $result[5]['additionalDescription']);

        // Test with just one payment mean
        $result = $this->module->sGetPaymentTranslation(array('id' => 2, 'randomField' => 'randomValue'));
        $this->assertCount(4, $result);
        $this->assertArrayHasKey('id', $result);
        $this->assertArrayHasKey('description', $result);
        $this->assertArrayHasKey('additionaldescription', $result);
        $this->assertArrayHasKey('randomField', $result);
        $this->assertEquals(2, $result['id']);
        $this->assertEquals('Debit', $result['description']);
        $this->assertEquals('Additional text', $result['additionaldescription']);
        $this->assertEquals('randomValue', $result['randomField']);

        // If backup data exists, restore it
        if($existingData) {
            $existingDataId = $existingData['id'];
            unset($existingData['id']);
            Shopware()->Db()->update('s_core_translations', $existingData, 'id = '.$existingDataId);
        }
    }


    /**
     * @covers sAdmin::sGetCountryStateTranslation
     * @group wip
     */
    public function testsGetCountryStateTranslation()
    {
        // Backup existing data and inject demo data
        $existingData = Shopware()->Db()->fetchRow("
            SELECT * FROM s_core_translations
            WHERE objecttype = 'config_country_states' AND objectlanguage = 1
        ");

        $demoData = array(
            'objectkey' => 1,
            'objectlanguage' => 1,
            'objecttype' => 'config_country_states',
            'objectdata' => 'a:2:{i:24;a:1:{s:4:"name";s:10:"California";}i:23;a:1:{s:4:"name";s:18:"Arkansas (english)";}}'
        );

        if($existingData) {
            Shopware()->Db()->update('s_core_translations', $demoData, 'id = '.$existingData['id']);
        } else {
            Shopware()->Db()->insert('s_core_translations', $demoData);
        }

        // Test with default shop, return empty array
        $this->assertCount(0, $this->module->sGetCountryStateTranslation());

        // Hack the current system shop, so we can properly test this
        Shopware()->Shop()->setDefault(false);

        $result = $this->module->sGetCountryStateTranslation();
        $this->assertCount(2, $result);
        $this->assertArrayHasKey(23, $result);
        $this->assertArrayHasKey(24, $result);
        $this->assertArrayHasKey('name', $result[23]);
        $this->assertArrayHasKey('name', $result[24]);
        $this->assertEquals('Arkansas (english)', $result[23]['name']);
        $this->assertEquals('California', $result[24]['name']);

        // Create a stub of a Shop for fallback.
        $stub = $this->getMockBuilder('\Shopware\Models\Shop\Shop')
            ->setMethods(array('getId'))
            ->disableOriginalConstructor()
            ->getMock();

        $stub->expects($this->any())
            ->method('getId')
            ->will($this->returnValue(10000));
        Shopware()->Shop()->setFallback($stub);

        Shopware()->Db()->insert('s_core_translations', array(
            'objectkey' => 1,
            'objectlanguage' => 10000,
            'objecttype' => 'config_country_states',
            'objectdata' => 'a:1:{i:2;a:1:{s:4:"name";s:13:"asdfasfdasdfa";}}'
        ));

        // Test with fallback
        $result = $this->module->sGetCountryStateTranslation();
        $this->assertCount(3, $result);
        $this->assertArrayHasKey(2, $result);
        $this->assertArrayHasKey(23, $result);
        $this->assertArrayHasKey(24, $result);
        $this->assertArrayHasKey('name', $result[2]);
        $this->assertArrayHasKey('name', $result[23]);
        $this->assertArrayHasKey('name', $result[24]);
        $this->assertEquals('asdfasfdasdfa', $result[2]['name']);
        $this->assertEquals('Arkansas (english)', $result[23]['name']);
        $this->assertEquals('California', $result[24]['name']);

        // If backup data exists, restore it
        if($existingData) {
            $existingDataId = $existingData['id'];
            unset($existingData['id']);
            Shopware()->Db()->update('s_core_translations', $existingData, 'id = '.$existingDataId);
        }
        Shopware()->Db()->delete('s_core_translations', 'objectlanguage = 10000');

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
        $firstLogin = $date->format(DateTime::ISO8601);

        $date->modify('+2 day');
        $lastLogin = $date->format(DateTime::ISO8601);

        $birthday = DateTime::createFromFormat('Y-m-d', '1986-12-20')->format(DateTime::ISO8601);

        $testData = array(
            "password" => "fooobar",
            "email"    => uniqid() . 'test@foobar.com',

            "firstlogin" => $firstLogin,
            "lastlogin"  => $lastLogin,

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
    private function deleteDummyCustomer(\Shopware\Models\Customer\Customer $customer)
    {
        $billingId = Shopware()->Db()->fetchOne('SELECT id FROM s_user_billingaddress WHERE userID = ?', array($customer->getId()));
        $shippingId = Shopware()->Db()->fetchOne('SELECT id FROM s_user_shippingaddress WHERE userID = ?', array($customer->getId()));

        Shopware()->Db()->delete('s_user_billingaddress_attributes', 'billingID = '.$billingId);
        Shopware()->Db()->delete('s_user_shippingaddress_attributes', 'shippingID = '.$shippingId);
        Shopware()->Db()->delete('s_user_billingaddress', 'id = '.$billingId);
        Shopware()->Db()->delete('s_user_shippingaddress', 'id = '.$shippingId);
        Shopware()->Db()->delete('s_core_payment_data', 'user_id = '.$customer->getId());
        Shopware()->Db()->delete('s_user', 'id = '.$customer->getId());
    }
}
