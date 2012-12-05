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
 * @author     M.Schmaeing
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
 * @group Voucher
 * @group Shopware_Tests
 * @group Controllers
 */
class Shopware_Tests_Controllers_Backend_VoucherTest extends Enlight_Components_Test_Controller_TestCase
{
    /**
     * Voucher dummy data
     *
     * @var array
     */
    private $voucherData = array(
        'description' => 'description',
        'minimumCharge' => '20',
        'modus' => '1',
        'numOrder' => '0',
        'voucherCode' => '',
        'numberOfUnits' => '50',
        'orderCode' => '65168phpunit',
        'percental' => '0',
        'taxConfig' => 'none',
        'shippingFree' => '',
        'customerGroup' => '',
        'restrictArticles' => '',
        'strict' => 0,
        'shopId' => 0,
        'bindToSupplier' => '',
        'validFrom' => null,
        'validTo' => null,
        'value' => '10'
    );

    /** @var Shopware\Components\Model\ModelManager */
    private $manager = null;

    /**@var $model \Shopware\Models\Voucher\Voucher*/
    protected $repository = null;

    /**
     * Standard set up for every test - just disable auth
     */
    public function setUp()
    {
        parent::setUp();

        $this->manager    = Shopware()->Models();
        $this->repository = Shopware()->Models()->Voucher();

        // disable auth and acl
        Shopware()->Plugins()->Backend()->Auth()->setNoAuth();
        Shopware()->Plugins()->Backend()->Auth()->setNoAcl();


    }

    /**
     * Cleaning up old testData
     */
    protected function tearDown()
    {
        parent::tearDown();

    }

    /**
     * Creates the dummy voucher
     *
     * @param bool $individualMode
     * @return Shopware\Models\Voucher\Voucher
     */
    private function getDummyVoucher($individualMode = true)
    {
        $voucher = new \Shopware\Models\Voucher\Voucher();
        $voucherData = $this->voucherData;
        if(!$individualMode){
            $voucherData["modus"] = 0;
            $voucherData["voucherCode"] = "phpUnitVoucherCode";
        }
        $voucher->fromArray($voucherData);
        return $voucher;
    }

    /**
     * helper method to create the dummy object
     *
     * @param bool $individualMode
     * @return \Shopware\Models\Voucher\Voucher
     */
    private function createDummy($individualMode = true)
    {
        $voucher = $this->getDummyVoucher($individualMode);
        $this->manager->persist($voucher);
        $this->manager->flush();

        return $voucher;
    }

    /**
     * test the voucher list
     */
    public function testGetVoucher()
    {
        //delete old data
        $vouchers = $this->repository->findBy(array('orderCode' => '65168phpunit'));
        foreach($vouchers as $voucher) {
            $this->manager->remove($voucher);
        }
        $this->manager->flush();

        $voucher = $this->createDummy();

        /** @var Enlight_Controller_Response_ResponseTestCase */
        $this->dispatch('backend/voucher/getVoucher?page=1&start=0&limit=2000');
        $this->assertTrue($this->View()->success);
        $returnData = $this->View()->data;
        $this->assertNotEmpty($returnData);
        $this->assertGreaterThan(0, $this->View()->totalCount);
        $lastInsert = $returnData[count($returnData) -1];
        $this->assertEquals($voucher->getId(), $lastInsert["id"]);

        $this->manager->remove($voucher);
        $this->manager->flush();
    }

    /**
     * test adding a voucher
     * @return the id to for the testUpdateVoucher Method
     */
    public function testAddVoucher()
    {
        $params = $this->voucherData;
        $this->Request()->setParams($params);
        $this->dispatch('backend/voucher/saveVoucher');
        $this->assertTrue($this->View()->success);
        $this->assertArrayCount(1, $this->View()->data);
        $this->assertEquals($params["description"], $this->View()->data["description"]);

        return $this->View()->data["id"];
    }

    /**
     * the the getVoucherDetail Method
     *
     * @depends testAddVoucher
     * @param $id
     * @return the id to for the testUpdateVoucher Method
     */
    public function testGetVoucherDetail($id)
    {
        $params["voucherID"] = $id;
        $this->Request()->setParams($params);
        $this->dispatch('backend/voucher/getVoucherDetail');
        $this->assertTrue($this->View()->success);
        $returningData = $this->View()->data;
        $voucherData = $this->voucherData;
        $this->assertEquals($voucherData["description"],$returningData["description"]);
        $this->assertEquals($voucherData["numberOfUnits"],$returningData["numberOfUnits"]);
        $this->assertEquals($voucherData["minimumCharge"],$returningData["minimumCharge"]);
        $this->assertEquals($voucherData["orderCode"],$returningData["orderCode"]);
        $this->assertEquals($voucherData["modus"],$returningData["modus"]);
        $this->assertEquals($voucherData["taxConfig"],$returningData["taxConfig"]);
        return $id;
    }

    /**
     * test the voucherCode validation methods with the created voucher
     *
     * @depends testAddVoucher
     */
    public function testValidateVoucherCode()
    {
        $params = array();
        $voucherModel = $this->createDummy(false);
        $voucherData = Shopware()->Models()->toArray($voucherModel);
        $params["value"] = $voucherData["voucherCode"];
        $params["param"] = $voucherData["id"];
        $this->Request()->setParams($params);
        $this->dispatch('backend/voucher/validateVoucherCode');

        $this->assertEquals(1,$this->Response()->getBody());

        $this->Request()->clearParams();
        $this->Response()->clearBody();

        $params["value"] = $voucherData["voucherCode"];

        //test with an unknown voucher id
        $params["param"] = 416531;
        $this->Request()->setParams($params);
        $this->dispatch('backend/voucher/validateVoucherCode');


        $this->assertEmpty($this->Response()->getBody());
        $this->manager->remove($voucherModel);
        $this->manager->flush();
    }

    /**
     * test the orderCode validation methods with the created voucher
     *
     * @depends testAddVoucher
     * @param $id
     */
    public function testValidateOrderCode($id)
    {
        $params = array();
        $voucherData = $this->voucherData;
        $params["value"] = $voucherData["orderCode"];
        $params["param"] = $id;

        $this->Request()->setParams($params);
        $this->dispatch('backend/voucher/validateOrderCode');

        $this->assertEquals(1, $this->Response()->getBody());

        $this->Request()->clearParams();
        $this->Response()->clearBody();

        $params["value"] = $voucherData["orderCode"];

        //test with an unknown voucher id
        $params["param"] = 416531;
        $this->Request()->setParams($params);
        $this->dispatch('backend/voucher/validateOrderCode');
        $this->assertEmpty($this->Response()->getBody());
    }


    /**
     * test updating a voucher
     *
     * @depends testGetVoucherDetail
     * @param $id
     */
    public function testUpdateVoucher($id)
    {
        $params = $this->voucherData;
        $params["id"] = $id;
        $params["description"] = "description_update";
        $this->Request()->setParams($params);

        $this->dispatch('backend/voucher/saveVoucher');

        $this->assertTrue($this->View()->success);
        $this->assertArrayCount(1, $this->View()->data);
        $this->assertEquals($params["description"], $this->View()->data["description"]);
        return $id;
    }

    /**
     * test generating voucher codes
     *
     * @depends testUpdateVoucher
     * @param $id
     */
    public function testGenerateVoucherCodes($id)
    {
        $voucherData = $this->voucherData;
        $params = array();
        $params["numberOfUnits"] = $voucherData["numberOfUnits"];
        $params["voucherId"] = intval($id);
        $this->Request()->setParams($params);
        $this->dispatch('backend/voucher/createVoucherCodes');
        $this->assertTrue($this->View()->success);
        return $id;
    }

    /**
     * the the listing of the voucher codes
     *
     * @depends testGenerateVoucherCodes
     * @param $id
     */
    public function testGetVoucherCodes($id)
    {
        $this->dispatch('backend/voucher/getVoucherCodes?voucherID=' . $id);
        $this->assertTrue($this->View()->success);
        $this->assertArrayCount(50, $this->View()->data);
        return $id;
    }

    /**
     * test the exportVoucherCode Method
     *
     * @depends testGetVoucherCodes
     * @param $id
     */
    public function testExportVoucherCode($id)
    {
        $params = array();
        $params["voucherId"] = intval($id);
        $this->Request()->setParams($params);
        $this->dispatch('backend/voucher/exportVoucherCode');
        $header = $this->Response()->getHeaders();

        $lastHeader = array_pop($header);
        $this->assertEquals("Content-Disposition",$lastHeader["name"]);
        $this->assertEquals("attachment;filename=voucherCodes.csv",$lastHeader["value"]);
        $this->assertGreaterThan(1000,strlen($this->Response()->getBody()));
        return $id;

    }



    /**
     * test delete the voucher method
     *
     * @depends testExportVoucherCode
     * @param $id
     */
    public function testDeleteVoucher($id)
    {
        $params = array();
        $params["id"] = intval($id);
        $this->Request()->setParams($params);
        $this->dispatch('backend/voucher/deleteVoucher');
        $this->assertTrue($this->View()->success);
        $this->assertNull($this->repository->find($params["id"]));
    }

    /**
     * test getTaxConfiguration Method
     */
    public function testGetTaxConfiguration()
    {
        $this->dispatch('backend/voucher/getTaxConfiguration');
        $this->assertTrue($this->View()->success);
        $this->assertNotEmpty($this->View()->data);
    }
}
