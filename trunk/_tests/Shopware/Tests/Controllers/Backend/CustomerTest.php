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
 * @author     Oliver Denter
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
 */
class Shopware_Tests_Controllers_Backend_CustomerTest extends Enlight_Components_Test_Controller_TestCase
{
    /**
     * Customer dummy data
     * @var array
     */
    protected $dummy = array(
            'id' => 99999,
            'active' => 1,
            'email' => 'dr123@shopware.de',
            'accountMode' => 0,
            'confirmationKey' => '',
            'paymentId' => 2,
            'newsletter' => 0,
            'validation' => 0,
            'affiliate' => 0,
            'customerGroupKey' => 'EK',
            'paymentPreset' => 0,
            'languageIso' => 'de',
            'shopId' => 1,
            'referer' => '',
            'priceGroupId' => 1,
            'internalComment' => 123123,
            'failedLogins' => 0,
            'lockedUntil' => '',
            'billingAttribute' => array(),
            'shippingAttribute' => array(),
            'attribute' => array(),
            'billing' => Array(
                array(
                    'company' => 'Shopware AG',
                    'department' => 'Entwicklung',
                    'salutation' => 'Mr',
                    'number' => 00001,
                    'firstName' => 'Oliver',
                    'lastName' => 'Denter',
                    'street' => 'Eggeroder Strasse',
                    'streetNumber' => 16,
                    'zipCode' => 48624,
                    'city' => 'Schöppingen',
                    'fax' => 'test123',
                    'countryId' => 2
                )
            ),
            'shipping' => Array(
                array (
                    'salutation' => 'mr',
                    'city' => 'Schöppingen',
                    'countryId' => 2
                )
            ),
            'debit' => Array(
                array (
                    'account' => 'a',
                    'bankCode' => 'a',
                    'bankName' => 'a',
                    'accountHolder' => 'a',
                )
            )
    );

    /** @var $repository \Shopware\Components\Model\ModelRepository|PHPUnit_Framework_MockObject_MockBuilder */
    protected $repository = null;

    /** @var $manager \Shopware\Components\Model\ModelManager|PHPUnit_Framework_MockObject_MockBuilder */
    protected $manager = null;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    public function setUp()
    {
        parent::setUp();

        $this->repository = $this->getMockBuilder('Shopware\Models\Customer\Repository')
             ->disableOriginalConstructor(true)
             ->setMethods(array('getOrdersQuery', 'getListQuery', 'getCustomerDetailQuery', 'find'))
             ->getMock();

        $this->manager = $this->getMockBuilder('Shopware\Components\Model\ModelManager')
             ->disableOriginalConstructor(true)
             ->setMethods(array('persist', 'flush', 'remove', 'toArray', 'getQueryCount'))
             ->getMock();

        Shopware_Controllers_Backend_Customer::$repository = $this->repository;
        Shopware_Controllers_Backend_Customer::$manager = $this->manager;

        Shopware()->Plugins()->Backend()->Auth()->setNoAcl();
        Shopware()->Plugins()->Backend()->Auth()->setNoAuth();
    }

    /**
     * Test case for the customer list
     */
    public function testList()
    {
        $filter = array(array('property' => 'firstName', 'value' => 'test%'));
        $sort = array(array('property' => 'firstName'));

        $this->Request()->setParam('limit', 20)
             ->setParam('start', 0)
             ->setParam('sort', Zend_Json::encode($sort))
             ->setParam('filter', Zend_Json::encode($filter))
             ->setParam('customerGroup', 1);

        $query = $this->getMockBuilder('Doctrine\ORM\Query')
                ->disableOriginalConstructor(true)
                ->setMethods(array('getArrayResult'))
                ->getMock();

        $this->repository->expects($this->once())
             ->method('getListQuery')
             ->with(
                $this->equalTo($filter[0]['value']),
                $this->equalTo(1),
                $this->equalTo($sort),
                $this->equalTo(20),
                $this->equalTo(0)
             )
             ->will($this->returnValue($query));

        $query->expects($this->once())
              ->method('getArrayResult')
              ->will($this->returnValue(array(1, 2)));

        $this->manager->expects($this->once())
             ->method('getQueryCount')
             ->will($this->returnValue(2));

        $this->dispatch('backend/customer/getList');

        $this->assertTrue($this->View()->success);
        $this->assertArrayCount(2, $this->View()->data);
        $this->assertEquals($this->View()->total, 2);
    }

    /**
     * Test case for the customer detail page.
     */
    public function testDetail()
    {
        $this->Request()->setParam('customerID', 2);

        $query = $this->getMockBuilder('Doctrine\ORM\Query')
                ->disableOriginalConstructor(true)
                ->setMethods(array('getOneOrNullResult'))
                ->getMock();

        $this->repository->expects($this->once())
             ->method('getCustomerDetailQuery')
             ->with($this->equalTo(2))
             ->will($this->returnValue($query));

        $query->expects($this->once())
              ->method('getOneOrNullResult')
              ->with($this->equalTo(Doctrine\ORM\AbstractQuery::HYDRATE_ARRAY))
              ->will($this->returnValue(array(array(2))));

        $this->dispatch('backend/customer/getDetail');

        $this->assertTrue($this->View()->success);
        $this->assertArrayCount(5, $this->View()->data);
    }

    /**
     * Test case for the customer order list
     */
    public function testOrder()
    {
        $filter = array(array('property' => 'orderNumber', 'value' => '%1%'));
        $sort = array(array('property' => 'orderNumber'));

        $this->Request()->setParam('limit', 20)
             ->setParam('start', 0)
             ->setParam('sort', Zend_Json::encode($sort))
             ->setParam('filter', Zend_Json::encode($filter))
             ->setParam('customerID', 1);

        $query = $this->getMockBuilder('Doctrine\ORM\Query')
                ->disableOriginalConstructor(true)
                ->setMethods(array('getArrayResult', 'getCount'))
                ->getMock();

        $this->repository->expects($this->once())
             ->method('getOrdersQuery')
             ->with(
                $this->equalTo(1),
                $this->equalTo($filter[0]['value']),
                $this->equalTo($sort),
                $this->equalTo(20),
                $this->equalTo(0)
             )
             ->will($this->returnValue($query));

        $query->expects($this->once())
              ->method('getArrayResult')
              ->will($this->returnValue(array(1, 2)));

        $this->manager->expects($this->once())
             ->method('getQueryCount')
             ->will($this->returnValue(2));

        $this->dispatch('backend/customer/getOrders');

        $this->assertTrue($this->View()->success);
        $this->assertArrayCount(2, $this->View()->data);
        $this->assertEquals($this->View()->total, 2);
    }


    public function testInsert()
    {
        //todo@dr: wenn das kunden module wieder läuft bitte den test anpassen.
        return true;
        $params = $this->dummy;
        unset($params['id']);

        $this->Request()->setParams($params);

        $this->manager->expects($this->once())
             ->method('persist')
             ->with($this->isType('object'));

        $this->manager->expects($this->once())
             ->method('flush');

        $this->manager->expects($this->once())
             ->method('toArray')
             ->with($this->isType('object'))
             ->will($this->returnValue(array(1)));

        $this->dispatch('backend/customer/save');

        $this->assertTrue($this->View()->success);
        $this->assertArrayCount(1, $this->View()->data);
    }

    /**
     * Test case to update a customer
     */
    public function testUpdate()
    {
        //todo@dr: Es muss noch ein Vorgehen definiert werden, wie neue Models gespeichert werden können in unit tests.
        return true;
        $this->Request()->setParams($this->dummy);

        $customer = $this->getMockBuilder('Shopware\Models\Customer\Customer')
            ->disableOriginalConstructor(true)
            ->setMethods(array(
                'getBilling', 'getShipping', 'getDebit', 'getAttribute',
                'setBilling', 'setShipping', 'setDebit',
                'fromArray', 'getPaymentId', 'setPassword'))
            ->getMock();

        $billing = $this->getMockBuilder('Shopware\Models\Customer\Billing')
            ->disableOriginalConstructor(true)
            ->setMethods(array('fromArray', 'getAttribute'))
            ->getMock();
        $shipping = $this->getMockBuilder('Shopware\Models\Customer\Shipping')
            ->disableOriginalConstructor(true)
            ->setMethods(array('fromArray', 'getAttribute'))
            ->getMock();
        $debit = $this->getMockBuilder('Shopware\Models\Customer\Debit')
            ->disableOriginalConstructor(true)
            ->setMethods(array('fromArray'))
            ->getMock();


        $billingAttributes = $this->getMockBuilder('Shopware\Models\Attribute\CustomerBilling')
            ->disableOriginalConstructor(true)
            ->setMethods(array('fromArray', 'setCustomerBilling'))
            ->getMock();

        $shippingAttributes = $this->getMockBuilder('Shopware\Models\Attribute\CustomerShipping')
            ->disableOriginalConstructor(true)
            ->setMethods(array('fromArray', 'setCustomerShipping'))
            ->getMock();

        $attributes = $this->getMockBuilder('Shopware\Models\Attribute\Customer')
            ->disableOriginalConstructor(true)
            ->setMethods(array('fromArray', 'setCustomer'))
            ->getMock();


        $this->repository->expects($this->once())
              ->method('find')
              ->with($this->equalTo(99999))
              ->will($this->returnValue($customer));

        $customer->expects($this->once())
                 ->method('getBilling')
                 ->will($this->returnValue($billing));

        $customer->expects($this->once())
                 ->method('getShipping')
                 ->will($this->returnValue($shipping));

        $customer->expects($this->once())
                 ->method('getDebit')
                 ->will($this->returnValue($debit));

        $customer->expects($this->once())
                 ->method('getPaymentId')
                 ->will($this->returnValue(2));

        $customer->expects($this->once())
                ->method('getAttribute')
                ->will($this->returnValue($attributes));


        $attributes->expects($this->once())
                    ->method('setCustomer')
                    ->with($this->isType('object'));

        $billingAttributes->expects($this->once())
                    ->method('setCustomerBilling')
                    ->with($this->isType('object'));

        $shippingAttributes->expects($this->once())
                    ->method('setCustomerShipping')
                    ->with($this->isType('object'));

        $billing->expects($this->once())
                ->method('fromArray')
                ->with($this->isType('array'))
                ->will($this->returnValue($billing));

        $shipping->expects($this->once())
                ->method('fromArray')
                ->with($this->isType('array'))
                ->will($this->returnValue($shipping));

        $debit->expects($this->once())
                ->method('fromArray')
                ->with($this->isType('array'))
                ->will($this->returnValue($debit));

        $customer->expects($this->once())
                 ->method('fromArray')
                 ->with($this->isType('array'));

        $this->manager->expects($this->once())
             ->method('persist')
             ->with($this->isType('object'));
        $this->manager->expects($this->once())
             ->method('persist')
             ->with($this->isType('object'));
        $this->manager->expects($this->once())
             ->method('persist')
             ->with($this->isType('object'));

        $this->manager->expects($this->once())
             ->method('persist')
             ->with($customer);

        $this->manager->expects($this->once())
             ->method('flush');

        $this->manager->expects($this->once())
             ->method('toArray')
             ->with($this->equalTo($customer))
             ->will($this->returnValue(array(1)));

        $this->dispatch('backend/customer/save');

        echo "<pre>";
        var_dump($this->View()->message);
        echo "</pre>";
        exit();

        $this->assertTrue($this->View()->success);
        $this->assertArrayCount(1, $this->View()->data);
    }


    /**
     * Test case to delete a customer
     */
    public function testDelete() {
        $this->Request()->setParam('id', 1);

        $customer = $this->getMockBuilder('Shopware\Models\Customer\Customer')
            ->disableOriginalConstructor(true)
            ->getMock();

        $this->repository->expects($this->any())
              ->method('find')
              ->with($this->equalTo(1))
              ->will($this->returnValue($customer));

        $this->manager->expects($this->any())
             ->method('remove')
             ->with($this->isType('object'));

        $this->manager->expects($this->once())
             ->method('flush');

        $this->dispatch('backend/customer/delete');

        $this->assertTrue($this->View()->success);
        $this->assertArrayCount(1, $this->View()->data);
    }
}