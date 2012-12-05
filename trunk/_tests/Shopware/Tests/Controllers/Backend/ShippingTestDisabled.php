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
class Shopware_Tests_Controllers_Backend_ShippingTest extends Enlight_Components_Test_Controller_TestCase
{
    /**
     * Customer dummy data
     * @var array
     */
    protected $testData = array(
    'feedId' => '',
    'id' => 22,
    'name' => '__UITest',
    'type' => 0,
    'description' => 'Test test test',
    'comment' => 'some testing tracking link',
    'active' => 1,
    'position' => '1',
    'calculation' => 0,
    'surchargeCalculation' => 2,
    'taxCalculation' => 1,
    'subshop' => '',
    'shippingFree' => '5',
    'multiShopId' => 0,
    'customerGroupId' => 0,
    'bindShippingFree' => 0,
    'bindTimeFrom' => '2012-05-03T00:15:00',
    'bindTimeTo' => '2012-05-03T03:15:00',
    'bindInStock' => 0,
    'bindLastStock' => 1,
    'bindWeekdayFrom' => 0,
    'bindWeekdayTo' => 0,
    'bindWeightFrom' => 0,
    'bindWeightTo' => 0,
    'bindPriceFrom' => 0,
    'bindPriceTo' => 0,
    'bindSql' => 'your rules',
    'statusLink' => 'http => //www.example.com/tracking',
    'calculationSql' => 'your calculations',
    'shop' => '',
    'bind_sql' => '',
    'calculation_sql' => '',
    'payments' => array(array('id'=> 2,
                        'name' => 'debit',
                        'description' => 'Lastschrift',
                        'position' => 4,
                        'active' => 1,
                        'shopware.apps.shipping.model.dispatch_id' => 22),
//                  array('id' => 3,
//                        'name' => 'cash',
//                        'description' => 'Nachnahme',
//                        'position' => 2,
//                        'active' => 1,
//                        'shopware.apps.shipping.model.dispatch_id' => 22),
    'countries' => array(array('id' => 2,
                        'name' => 'Deutschland',
                        'iso' => 'DE',
                        'area' => 'deutschland',
                        'en' => '',
                        'position' => 1,
                        'notice' => '',
                        'shippingFree' => 0,
                        'taxFree' => 0,
                        'taxFreeUstId' => 0,
                        'active' => 1,
                        'iso3' => 'DEU',
                        'shopware.apps.shipping.model.dispatch_id')),
    'holidays' => array(array('id' => 6,
                        'name' => 'Karfreitag (22.04.2012)')),
    'categories' => array(array())
    ));
    
    private $dispatchId = 9;

    /** @var $repository \Shopware\Components\Model\ModelRepository|PHPUnit_Framework_MockObject_MockBuilder */
    protected $repository = null;

    /** @var $manager \Shopware\Components\Model\ModelManager|PHPUnit_Framework_MockObject_MockBuilder */
    protected $manager = null;
    /** @var Shopware\Models\Dispatch\Dispatch */
    protected $modelMock = null;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    public function setUp()
    {
        parent::setUp();

        $this->repository = $this->getMockBuilder('Shopware\Models\Customer\Repository')
             ->disableOriginalConstructor(true)
             ->setMethods(array(
                    'getShippingCostsQuery', 
                    'getHolidayInfoQuery', 
                    'getCustomerGroupInfoQuery', 
                    'getShopInfoQuery',
                    'getTaxInfoQuery',
                    'getShippingCostsMatrixQuery',
                    'getPurgeShippingCostsMatrixQuery',
                    'getPaymentQuery',
                    'getCountryQuery',
                    'getHolidayQuery',
                    'sortOrderQuery' ))
             ->getMock();
        $this->manager = $this->getMockBuilder('Shopware\Components\Model\ModelManager')
             ->disableOriginalConstructor(true)
             ->setMethods(array('persist', 
                'flush', 
                'remove',
                'toArray', 
                'getQueryCount', 
                'getConfiguration'
        ))->getMock();
        
        $this->modelMock = $this->getMockBuilder('Shopware\Models\Dispatch\Dispatch')
            ->disableOriginalConstructor(true)
            ->setMethods(array(
                'fromArray',
                'addPayment'
            ))
            ->getMock();
        
        Shopware_Controllers_Backend_Shipping::$repository = $this->repository;
        Shopware_Controllers_Backend_Shipping::$manager    = $this->manager;
        Shopware_Controllers_Backend_Shipping::$newDispatchModel   = $this->modelMock;

        Shopware()->Plugins()->Backend()->Auth()->setNoAcl();
        Shopware()->Plugins()->Backend()->Auth()->setNoAuth();
    }

    /**
     * Test case for the customer list
     */
    public function testGetShippingCosts()
    {
        $filter = array(array('property' => 'name', 'value' => 'test%'));
        $sort = array(array('property' => 'firstName')); 

        $this->Request()->setParam('limit', 20)
             ->setParam('start', 0)
             ->setParam('sort', Zend_Json::encode($sort))
             ->setParam('filter', Zend_Json::encode($filter))
             ->setParam('dispatchID', $this->dispatchId);

        $query = $this->getMockBuilder('Doctrine\ORM\Query')
                ->disableOriginalConstructor(true)
                ->setConstructorArgs(array($this->manager))
                ->setMethods(array('getArrayResult'))
                ->getMock();

        $this->repository->expects($this->once())
             ->method('getShippingCostsQuery')
             ->with(
                $this->equalTo($this->dispatchId),
                $this->equalTo('test%'),
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

        $this->dispatch('backend/Shipping/getShippingCosts');

        $this->assertTrue($this->View()->success);
        $this->assertArrayCount(2, $this->View()->data);
        $this->assertEquals($this->View()->total, 2);
    }
    
    public function testGetCustomerGroupInfoCosts()
    {
        $filter = array(array('property' => 'name', 'value' => 'test%'));
        $sort = array();

        $this->Request()->setParam('limit', 20)
             ->setParam('start', 0)
             ->setParam('sort', Zend_Json::encode($sort))
             ->setParam('filter', Zend_Json::encode($filter))
             ->setParam('customergroupID', null);

        $query = $this->getMockBuilder('Doctrine\ORM\Query')
                ->disableOriginalConstructor(true)
                ->setConstructorArgs(array($this->manager))
                ->setMethods(array('getArrayResult'))
                ->getMock();

        $this->repository->expects($this->once())
             ->method('getCustomerGroupInfoQuery')
             ->with(
                $this->equalTo(null),
                $this->equalTo('test%'),
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

        $this->dispatch('backend/Shipping/getCustomerGroupInfo');

        $this->assertTrue($this->View()->success);
        $this->assertArrayCount(2, $this->View()->data);
        $this->assertEquals($this->View()->total, 2);
    }
    
    public function testGetShopInfo()
    {
        $filter = array(array('property' => 'name', 'value' => 'test%'));
        $sort = array(array());

        $this->Request()->setParam('limit', 20)
             ->setParam('start', 0)
             ->setParam('sort', Zend_Json::encode($sort))
             ->setParam('filter', Zend_Json::encode($filter))
             ->setParam('customergroupID', null);

        $query = $this->getMockBuilder('Doctrine\ORM\Query')
                ->disableOriginalConstructor(true)
                ->setConstructorArgs(array($this->manager))
                ->setMethods(array('getArrayResult'))
                ->getMock();

        $this->repository->expects($this->once())
             ->method('getShopInfoQuery')
             ->with(
                $this->equalTo(null),
                $this->equalTo('test%'),
                $this->equalTo($sort),
                $this->equalTo(20),
                $this->equalTo(0)
             )
             ->will($this->returnValue($query));
        
        $query->expects($this->once())
              ->method('getArrayResult')
              ->will($this->returnValue(array(1, 2)));

        $this->dispatch('backend/Shipping/getShopInfo');

        $this->assertTrue($this->View()->success);
        
        // we add an additional element, so we have to check for 3 element rather than two
        $this->assertArrayCount(3, $this->View()->data);
        $this->assertEquals($this->View()->total, 3);
    } 
//    
    public function testGetTaxInfo()
    {
        $filter = array(array('property' => 'name', 'value' => 'test%'));
        $sort = array(array());

        $this->Request()->setParam('limit', 20)
             ->setParam('start', 0)
             ->setParam('sort', Zend_Json::encode($sort))
             ->setParam('filter', Zend_Json::encode($filter))
             ->setParam('taxID', 1);

        $query = $this->getMockBuilder('Doctrine\ORM\Query')
                ->disableOriginalConstructor(true)
                ->setConstructorArgs(array($this->manager))
                ->setMethods(array('getArrayResult'))
                ->getMock();

        $this->repository->expects($this->once())
             ->method('getTaxInfoQuery')
             ->with(
                $this->equalTo(1),
                $this->equalTo('test%'),
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
        $this->dispatch('backend/Shipping/getTaxInfo');
        $this->assertTrue($this->View()->success);
        // we add an additional element, so we have to check for 3 element rather than two
        $this->assertArrayCount(2, $this->View()->data);
        $this->assertEquals($this->View()->total, 2);
    }
//    
    public function testGetCostsMatrix()
    {
        $filter = array(array('property' => 'name', 'value' => 'test%'));
        $sort = array(array());

        $this->Request()->setParam('limit', 20)
             ->setParam('start', 0)
             ->setParam('sort', Zend_Json::encode($sort))
             ->setParam('filter', Zend_Json::encode($filter))
             ->setParam('dispatchId', $this->dispatchId);

        $query = $this->getMockBuilder('Doctrine\ORM\Query')
                ->disableOriginalConstructor(true)
                ->setConstructorArgs(array($this->manager))
                ->setMethods(array('getArrayResult'))
                ->getMock();

        $this->repository->expects($this->once())
             ->method('getShippingCostsMatrixQuery')
             ->with(
                $this->equalTo($this->dispatchId),
                $this->equalTo('test%'),
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
        $this->dispatch('backend/Shipping/getCostsMatrix');
        $this->assertTrue($this->View()->success);
        // we add an additional element, so we have to check for 3 element rather than two
        $this->assertArrayCount(2, $this->View()->data);
        $this->assertEquals($this->View()->total, 2);
    }
    
    public function testGetPayments()
    {
        $filter = array(array('property' => 'name', 'value' => 'test%'));
        $sort = array(array());

        $this->Request()->setParam('limit', 20)
             ->setParam('start', 0)
             ->setParam('sort', Zend_Json::encode($sort))
             ->setParam('filter', Zend_Json::encode($filter))
             ->setParam('dispatchId', $this->dispatchId);

        $query = $this->getMockBuilder('Doctrine\ORM\Query')
                ->disableOriginalConstructor(true)
                ->setConstructorArgs(array($this->manager))
                ->setMethods(array('getArrayResult'))
                ->getMock();

        $this->repository->expects($this->once())
             ->method('getPaymentQuery')
             ->with(
                $this->equalTo('test%'),
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
        $this->dispatch('backend/Shipping/getPayments');
        $this->assertTrue($this->View()->success);
        // we add an additional element, so we have to check for 3 element rather than two
        $this->assertArrayCount(2, $this->View()->data);
        $this->assertEquals($this->View()->total, 2);
    }
    
//    public function testGetCountries()
//    {
//        $filter = array(array());
//        $sort = array(array());
//
//        $this->Request()->setParam('limit', 20)
//             ->setParam('start', 0)
//             ->setParam('sort', Zend_Json::encode($sort))
//             ->setParam('filter', Zend_Json::encode($filter))
//             ->setParam('dispatchId', $this->dispatchId);
//
//        $query = $this->getMockBuilder('Doctrine\ORM\Query')
//                ->disableOriginalConstructor(true)
//                ->setConstructorArgs(array($this->manager))
//                ->setMethods(array('getArrayResult'))
//                ->getMock();
//
//        $this->repository->expects($this->once())
//             ->method('getCountryQuery')
//             ->with(
//                $this->equalTo(Zend_Json::encode($filter)),
//                $this->equalTo($sort),
//                $this->equalTo(20),
//                $this->equalTo(0)
//             )
//             ->will($this->returnValue($query));
//        $query->expects($this->once())
//              ->method('getArrayResult')
//              ->will($this->returnValue(array(1, 2)));
//        
//         $this->manager->expects($this->once())
//             ->method('getQueryCount')
//             ->will($this->returnValue(2));
//        $this->dispatch('backend/Shipping/getCountries');
//        $this->assertTrue($this->View()->success);
//        // we add an additional element, so we have to check for 3 element rather than two
//        $this->assertArrayCount(2, $this->View()->data);
//        $this->assertEquals($this->View()->total, 2);
//    }
//    public function testGetHolidays()
//    {
//        $filter = array(array());
//        $sort = array(array());
//
//        $this->Request()->setParam('limit', 20)
//             ->setParam('start', 0)
//             ->setParam('sort', Zend_Json::encode($sort))
//             ->setParam('filter', Zend_Json::encode($filter))
//             ->setParam('dispatchId', $this->dispatchId);
//
//        $query = $this->getMockBuilder('Doctrine\ORM\Query')
//                ->disableOriginalConstructor(true)
//                ->setConstructorArgs(array($this->manager))
//                ->setMethods(array('getArrayResult'))
//                ->getMock();
//
//        $this->repository->expects($this->once())
//             ->method('getHolidayQuery')
//             ->with(
//                $this->equalTo(Zend_Json::encode($filter)),
//                $this->equalTo($sort),
//                $this->equalTo(20),
//                $this->equalTo(0)
//             )
//             ->will($this->returnValue($query));
//        $query->expects($this->once())
//              ->method('getArrayResult')
//        
//              ->will($this->returnValue(array(array('date'=>new \DateTime()))));
//              
//        
//         $this->manager->expects($this->once())
//             ->method('getQueryCount')
//             ->will($this->returnValue(2));
//        $this->dispatch('backend/Shipping/getHolidays');
//        $this->assertTrue($this->View()->success);
//        // we add an additional element, so we have to check for 3 element rather than two
//        $this->assertArrayCount(2, $this->View()->data);
//        $this->assertEquals($this->View()->total, 2);
//    }
//    
//todo@js This needs to be implemented after community day.
//    public function testDelete()
//    {
//        $this->Request()->setParam('id', 20);
//        
//        $dispatch = $this->getMockBuilder('Shopware\Models\Dispatch\Dispatch')
//            ->disableOriginalConstructor(true)
//            ->getMock();
//        
//        $this->repository->expects($this->any())
//              ->method('find')
//              ->with($this->equalTo(1))
//              ->will($this->returnValue($dispatch));
//        
//        $this->manager->expects($this->any())
//             ->method('remove')
//             ->with($this->isType('object'));
//        $this->manager->expects($this->once())
//             ->method('flush');
//        
//        $this->dispatch('backend/Shipping/delete');
//        $this->assertTrue($this->View()->success);
//    }
//    
//todo@js This needs to be implemented after community day.
//    public function testInsert()
//    {        
//        $params = $this->testData;
//        unset($params['id']);
//       
//        $this->Request()->setParams($params);
//        
//        
//        $paymentMock = $this->getMockBuilder('Shopware\Models\Payment\Payment')
//            ->disableOriginalConstructor(true)
//            ->getMock();
//        
//        // ich erwarte hier das die methode 'find' auf dem manager object
//        // mit dem parameter aufegrufen wird
//        $this->manager->expects($this->any())
//            ->method('find')
//            ->with(array('Shopware\Models\Payment\Payment',2))
//            ->will($this->returnValue('sdf'));
//        
//        // ich erwarte das die methode addPayment aus dem Model aufgerufen wird
//        $this->modelMock->expects($this->any())
//            ->method('addPayment')
//            ->with($paymentMock)
//            ->will($this->returnValue('sdf'));
//        
//        $this->manager->expects($this->once())
//             ->method('persist')
//             ->with($this->isType('object'));
//
//        $this->manager->expects($this->once())
//             ->method('flush');
//
//        $this->manager->expects($this->any())
//             ->method('find')
//             ->will($this->returnValue(array('test123')));
//
//        $this->dispatch('backend/Shipping/createDispatch');
//
//        $this->assertTrue($this->View()->success);
//        $this->assertArrayCount(1, $this->View()->data);
//    }
//todo@js This needs to be implemented after community day.
//    public function testUpdate()
//    {
//        $this->Request()->setParams($this->testData);
//
//        $params = $this->testData;
//
//        $this->Request()->setParams($params);
//
//        $this->manager->expects($this->once())
//             ->method('persist')
//             ->with($this->isType('object'));
//
//        $this->manager->expects($this->once())
//             ->method('flush');
//
//        $this->dispatch('backend/Shipping/createDispatch');
//
//        $this->assertTrue($this->View()->success);
//        $this->assertArrayCount(1, $this->View()->data);
//    }
}