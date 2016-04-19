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

namespace Shopware\Tests\Components\Api;

use Shopware\Components\Api\Resource\Customer;
use Shopware\Components\Api\Resource\Resource;

/**
 * @category  Shopware
 * @package   Shopware\Tests
 * @copyright Copyright (c) 2013, shopware AG (http://www.shopware.de)
 */
class CustomerTest extends TestCase
{
    /**
     * @var Customer
     */
    protected $resource;

    /**
     * @return Customer
     */
    public function createResource()
    {
        return new Customer();
    }

    /**
     * @expectedException \Shopware\Components\Api\Exception\CustomValidationException
     * @expectedExceptionMessage Emailaddress test@example.com for shopId 1 is not unique
     */
    public function testCreateWithNonUniqueEmailShouldThrowException()
    {
        $testData = array(
            "password" => "fooobar",
            "active"   => true,
            "email"    => 'test@example.com',
        );

        $this->resource->create($testData);
    }

    public function testCreateShouldBeSuccessful()
    {
        $date = new \DateTime();
        $date->modify('-10 days');
        $firstlogin = $date->format(\DateTime::ISO8601);

        $date->modify('+2 day');
        $lastlogin = $date->format(\DateTime::ISO8601);

        $birthday = \DateTime::createFromFormat('Y-m-d', '1986-12-20')->format(\DateTime::ISO8601);

        $testData = array(
            "password" => "fooobar",
            "email"    => uniqid() . 'test@foobar.com',

            "firstlogin" => $firstlogin,
            "lastlogin"  => $lastlogin,

            "salutation" => "mr",
            "firstname" => "Max",
            "lastname"  => "Mustermann",
            "birthday"  => $birthday,

            "billing" => array(
                "firstName" => "Max",
                "lastName"  => "Mustermann",
                "additionalAddressLine1"  => "Address Billing Addition 1",
                "additionalAddressLine2"  => "Address Billing Addition 2",
                "countryId" => "2",
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
                "additionalAddressLine1"  => "Address Shipping Addition 1",
                "additionalAddressLine2"  => "Address Shipping Addition 2",
                "countryId" => "2",
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

        /** @var \Shopware\Models\Customer\Customer $customer */
        $customer = $this->resource->create($testData);

        $this->assertInstanceOf('\Shopware\Models\Customer\Customer', $customer);
        $this->assertGreaterThan(0, $customer->getId());

        // Test default values
        $this->assertEquals($customer->getShop()->getId(), 1);
        $this->assertEquals($customer->getAccountMode(), 0);
        $this->assertEquals($customer->getGroup()->getKey(), "EK");
        $this->assertEquals($customer->getActive(), true);


        $this->assertEquals($customer->getEmail(), $testData['email']);
        $this->assertEquals($customer->getBilling()->getFirstName(), $testData['billing']['firstName']);
        $this->assertEquals($customer->getBilling()->getAttribute()->getText1(), $testData['billing']['attribute']['text1']);
        $this->assertEquals($customer->getShipping()->getFirstName(), $testData['shipping']['firstName']);
        $this->assertEquals($customer->getShipping()->getAttribute()->getText1(), $testData['shipping']['attribute']['text1']);



        //test additional address lines
        $this->assertEquals($customer->getShipping()->getAdditionalAddressLine1(), $testData['shipping']['additionalAddressLine1']);
        $this->assertEquals($customer->getShipping()->getAdditionalAddressLine2(), $testData['shipping']['additionalAddressLine2']);
        $this->assertEquals($customer->getBilling()->getAdditionalAddressLine1(), $testData['billing']['additionalAddressLine1']);
        $this->assertEquals($customer->getBilling()->getAdditionalAddressLine2(), $testData['billing']['additionalAddressLine2']);

        return $customer->getId();
    }

    /**
     * @depends testCreateShouldBeSuccessful
     */
    public function testGetOneShouldBeSuccessful($id)
    {
        $customer = $this->resource->getOne($id);
        $this->assertGreaterThan(0, $customer['id']);
    }

    /**
     * @depends testCreateShouldBeSuccessful
     */
    public function testGetOneByNumberShouldBeSuccessful($id)
    {
        $this->resource->setResultMode(Resource::HYDRATE_OBJECT);
        $customer = $this->resource->getOne($id);
        $number = $customer->getBilling()->getNumber();

        $customer = $this->resource->getOneByNumber($number);
        $this->assertEquals($id, $customer->getId());
    }

    /**
     * @depends testCreateShouldBeSuccessful
     */
    public function testGetOneShouldBeAbleToReturnObject($id)
    {
        $this->resource->setResultMode(Resource::HYDRATE_OBJECT);
        $customer = $this->resource->getOne($id);

        $this->assertInstanceOf('\Shopware\Models\Customer\Customer', $customer);
        $this->assertGreaterThan(0, $customer->getId());
    }

    /**
     * @depends testCreateShouldBeSuccessful
     */
    public function testGetListShouldBeSuccessful()
    {
        $result = $this->resource->getList();

        $this->assertArrayHasKey('data', $result);
        $this->assertArrayHasKey('total', $result);

        $this->assertGreaterThanOrEqual(1, $result['total']);
        $this->assertGreaterThanOrEqual(1, $result['data']);
    }

    /**
     * @depends testCreateShouldBeSuccessful
     */
    public function testGetListShouldBeAbleToReturnObjects()
    {
        $this->resource->setResultMode(Resource::HYDRATE_OBJECT);
        $result = $this->resource->getList();

        $this->assertArrayHasKey('data', $result);
        $this->assertArrayHasKey('total', $result);

        $this->assertGreaterThanOrEqual(1, $result['total']);
        $this->assertGreaterThanOrEqual(1, $result['data']);

        $this->assertInstanceOf('\Shopware\Models\Customer\Customer', $result['data'][0]);
    }

    /**
     * @expectedException \Shopware\Components\Api\Exception\ValidationException
     */
    public function testCreateWithInvalidDataShouldThrowValidationException()
    {
        $testData = array(
            'active'  => true,
            'email'   => 'invalid',
            'billing' => array(
                'firstName' => 'Max',
                'lastName'  => 'Mustermann',
            ),
        );

        $this->resource->create($testData);
    }

    /**
     * @depends testCreateShouldBeSuccessful
     */
    public function testUpdateShouldBeSuccessful($id)
    {
        $testData = array(
            'active'  => true,
            'email'   => uniqid() . 'update@foobar.com',
            'billing' => array(
                'firstName' => 'Max Update',
                'lastName'  => 'Mustermann Update',
                'additionalAddressLine1'  => 'additional billing address Line 1',
                'additionalAddressLine2'  => 'additional billing address Line 2',
            ),
            'shipping' => array(
                'additionalAddressLine1'  => 'additional shipping address Line 1',
                'additionalAddressLine2'  => 'additional shipping address Line 2',
            ),
        );

        $customer = $this->resource->update($id, $testData);

        $this->assertInstanceOf('\Shopware\Models\Customer\Customer', $customer);
        $this->assertEquals($id, $customer->getId());

        $this->assertEquals($customer->getEmail(), $testData['email']);
        $this->assertEquals($customer->getBilling()->getFirstName(), $testData['billing']['firstName']);

        //test additional fields
        $this->assertEquals($customer->getBilling()->getAdditionalAddressLine1(), $testData['billing']['additionalAddressLine1']);
        $this->assertEquals($customer->getBilling()->getAdditionalAddressLine2(), $testData['billing']['additionalAddressLine2']);

        $this->assertEquals($customer->getShipping()->getAdditionalAddressLine1(), $testData['shipping']['additionalAddressLine1']);
        $this->assertEquals($customer->getShipping()->getAdditionalAddressLine2(), $testData['shipping']['additionalAddressLine2']);

        return $id;
    }

    /**
     * @depends testCreateShouldBeSuccessful
     */
    public function testStreetAndStreetNumberShouldBeJoined($id)
    {
        $testData = array(
            'billing' => array(
                'street' => 'Fakestreet',
                'streetNumber' => '333'
            ),
            'shipping' => array(
                'street' => 'Teststreet',
                'streetNumber' => '111'
            ),
        );

        $customer = $this->resource->update($id, $testData);
        $this->assertEquals(
            $customer->getBilling()->getStreet(),
            'Fakestreet 333'
        );
        $this->assertEquals(
            $customer->getShipping()->getStreet(),
            'Teststreet 111'
        );
    }

    /**
     * @depends testCreateShouldBeSuccessful
     */
    public function testUpdateByNumberShouldBeSuccessful($id)
    {
        $this->resource->setResultMode(Resource::HYDRATE_OBJECT);
        $customer = $this->resource->getOne($id);
        $number = $customer->getBilling()->getNumber();

        $testData = array(
            'active'  => true,
            'email'   => uniqid() . 'update@foobar.com',
            'billing' => array(
                'firstName' => 'Max Update',
                'lastName'  => 'Mustermann Update',
            ),
        );

        $customer = $this->resource->updateByNumber($number, $testData);

        $this->assertInstanceOf('\Shopware\Models\Customer\Customer', $customer);
        $this->assertEquals($id, $customer->getId());

        $this->assertEquals($customer->getEmail(), $testData['email']);
        $this->assertEquals($customer->getBilling()->getFirstName(), $testData['billing']['firstName']);

        return $number;
    }

    /**
     * @depends testCreateShouldBeSuccessful
     * @expectedException \Shopware\Components\Api\Exception\ValidationException
     */
    public function testUpdateWithInvalidDataShouldThrowValidationException($id)
    {
        $testData = array(
            'active'  => true,
            'email'   => 'invalid',
            'billing' => array(
                'firstName' => 'Max',
                'lastName'  => 'Mustermann',
            ),
        );

        $this->resource->update($id, $testData);
    }

    /**
     * @expectedException \Shopware\Components\Api\Exception\NotFoundException
     */
    public function testUpdateWithInvalidIdShouldThrowNotFoundException()
    {
        $this->resource->update(9999999, array());
    }

    /**
     * @expectedException \Shopware\Components\Api\Exception\ParameterMissingException
     */
    public function testUpdateWithMissingIdShouldThrowParameterMissingException()
    {
        $this->resource->update('', array());
    }

    /**
     * @depends testUpdateShouldBeSuccessful
     */
    public function testDeleteShouldBeSuccessful($id)
    {
        $customer = $this->resource->delete($id);

        $this->assertInstanceOf('\Shopware\Models\Customer\Customer', $customer);
        $this->assertEquals(null, $customer->getId());
        $this->assertEquals(null, $customer->getShipping()->getId());
        $this->assertEquals(null, $customer->getBilling()->getId());
    }

    /**
     * @expectedException \Shopware\Components\Api\Exception\NotFoundException
     */
    public function testDeleteWithInvalidIdShouldThrowNotFoundException()
    {
        $this->resource->delete(9999999);
    }

    /**
     * @expectedException \Shopware\Components\Api\Exception\ParameterMissingException
     */
    public function testDeleteWithMissingIdShouldThrowParameterMissingException()
    {
        $this->resource->delete('');
    }


    /**
     * @return int
     */
    public function testPostCustomersWithDebitShouldCreatePaymentData()
    {
        $date = new \DateTime();
        $date->modify('-10 days');
        $firstlogin = $date->format(\DateTime::ISO8601);

        $date->modify('+2 day');
        $lastlogin = $date->format(\DateTime::ISO8601);

        $birthday = \DateTime::createFromFormat('Y-m-d', '1986-12-20')->format(\DateTime::ISO8601);

        $requestData = array(
            "password" => "fooobar",
            "active"   => true,
            "email"    => uniqid() . 'test1@foobar.com',

            "firstlogin" => $firstlogin,
            "lastlogin"  => $lastlogin,

            "salutation" => "mr",
            "firstname" => "Max",
            "lastname"  => "Mustermann",
            "birthday"  => $birthday,

            "billing" => array(
                "firstName" => "Max",
                "lastName"  => "Mustermann",
                "countryId" => "2"
            ),

            "shipping" => array(
                "salutation" => "Mr",
                "company"    => "Widgets Inc.",
                "firstName"  => "Max",
                "lastName"   => "Mustermann",
                "countryId"  => "2"
            ),

            "debit" => array(
                "account"       => "Fake Account",
                "bankCode"      => "55555555",
                "bankName"      => "Fake Bank",
                "accountHolder" => "Max Mustermann",
            ),
        );


        $customer = $this->resource->create($requestData);
        $identifier = $customer->getId();

        $this->resource->getManager()->clear();
        $customer = Shopware()->Models()->getRepository('Shopware\Models\Customer\Customer')->find($identifier);

        $paymentData = array_shift($customer->getPaymentData()->toArray());

        $this->assertNotNull($paymentData);
        $this->assertEquals('Max Mustermann', $paymentData->getAccountHolder());
        $this->assertEquals('Fake Account', $paymentData->getAccountNumber());
        $this->assertEquals('Fake Bank', $paymentData->getBankName());
        $this->assertEquals('55555555', $paymentData->getBankCode());

        $this->testDeleteShouldBeSuccessful($identifier);
    }

    /**
     * @return int
     */
    public function testPostCustomersWithDebitPaymentDataShouldCreateDebitData()
    {
        $date = new \DateTime();
        $date->modify('-10 days');
        $firstlogin = $date->format(\DateTime::ISO8601);

        $date->modify('+2 day');
        $lastlogin = $date->format(\DateTime::ISO8601);

        $birthday = \DateTime::createFromFormat('Y-m-d', '1986-12-20')->format(\DateTime::ISO8601);

        $requestData = array(
            "password" => "fooobar",
            "active"   => true,
            "email"    => uniqid() . 'test2@foobar.com',

            "firstlogin" => $firstlogin,
            "lastlogin"  => $lastlogin,

            "salutation" => "mr",
            "firstname" => "Max",
            "lastname"  => "Mustermann",
            "birthday"  => $birthday,

            "billing" => array(
                "firstName" => "Max",
                "lastName"  => "Mustermann",
                "countryId" => "2"
            ),

            "shipping" => array(
                "salutation" => "Mr",
                "company"    => "Widgets Inc.",
                "firstName"  => "Max",
                "lastName"   => "Mustermann",
                "countryId"  => "2"
            ),

            "paymentData" => array(
                array(
                    "paymentMeanId"   => 2,
                    "accountNumber" => "Fake Account",
                    "bankCode"      => "55555555",
                    "bankName"      => "Fake Bank",
                    "accountHolder" => "Max Mustermann",
                ),
            ),
        );

        $customer = $this->resource->create($requestData);
        $identifier = $customer->getId();

        $this->resource->getManager()->clear();
        $customer = Shopware()->Models()->getRepository('Shopware\Models\Customer\Customer')->find($identifier);

        $paymentData = array_shift($customer->getPaymentData()->toArray());

        $this->assertNotNull($paymentData);
        $this->assertEquals('Max Mustermann', $paymentData->getAccountHolder());
        $this->assertEquals('Fake Account', $paymentData->getAccountNumber());
        $this->assertEquals('Fake Bank', $paymentData->getBankName());
        $this->assertEquals('55555555', $paymentData->getBankCode());

        $this->testDeleteShouldBeSuccessful($identifier);
    }
}
