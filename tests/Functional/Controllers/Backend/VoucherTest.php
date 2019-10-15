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

namespace Shopware\Tests\Functional\Controllers\Backend;

class VoucherTest extends \Enlight_Components_Test_Controller_TestCase
{
    /** @var \Shopware\Models\Voucher\Voucher $repository */
    protected $repository;

    /**
     * Voucher dummy data
     *
     * @var array
     */
    private $voucherData = [
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
        'value' => '10',
    ];

    /** @var \Shopware\Components\Model\ModelManager */
    private $manager;

    /**
     * Standard set up for every test - just disable auth
     */
    public function setUp(): void
    {
        parent::setUp();

        $this->manager = Shopware()->Models();
        $this->repository = Shopware()->Models()->getRepository(\Shopware\Models\Voucher\Voucher::class);

        // Disable auth and acl
        Shopware()->Plugins()->Backend()->Auth()->setNoAuth();
        Shopware()->Plugins()->Backend()->Auth()->setNoAcl();
    }

    /**
     * test the voucher list
     */
    public function testGetVoucher()
    {
        // Delete old data
        $vouchers = $this->repository->findBy(['orderCode' => '65168phpunit']);
        foreach ($vouchers as $voucher) {
            $this->manager->remove($voucher);
        }
        $this->manager->flush();

        $voucher = $this->createDummy();

        $this->dispatch('backend/voucher/getVoucher?page=1&start=0&limit=2000');
        static::assertTrue($this->View()->success);
        $returnData = $this->View()->data;
        static::assertNotEmpty($returnData);
        static::assertGreaterThan(0, $this->View()->totalCount);
        $lastInsert = $returnData[count($returnData) - 1];
        static::assertEquals($voucher->getId(), $lastInsert['id']);

        $this->manager->remove($voucher);
        $this->manager->flush();
    }

    /**
     * test adding a voucher
     *
     * @return string The id to for the testUpdateVoucher Method
     */
    public function testAddVoucher()
    {
        $this->manager->getConnection()->executeUpdate('DELETE FROM s_emarketing_vouchers WHERE ordercode = :code', [
            ':code' => $this->voucherData['orderCode'],
        ]);

        $params = $this->voucherData;
        $this->Request()->setParams($params);
        $this->dispatch('backend/voucher/saveVoucher');
        static::assertTrue($this->View()->success);
        static::assertEquals($params['description'], $this->View()->data['description']);

        return $this->View()->data['id'];
    }

    /**
     * the the getVoucherDetail Method
     *
     * @depends testAddVoucher
     *
     * @param string $id
     *
     * @return string the id to for the testUpdateVoucher Method
     */
    public function testGetVoucherDetail($id)
    {
        $params['voucherID'] = $id;
        $this->Request()->setParams($params);
        $this->dispatch('backend/voucher/getVoucherDetail');
        static::assertTrue($this->View()->success);
        $returningData = $this->View()->data;
        $voucherData = $this->voucherData;
        static::assertEquals($voucherData['description'], $returningData['description']);
        static::assertEquals($voucherData['numberOfUnits'], $returningData['numberOfUnits']);
        static::assertEquals($voucherData['minimumCharge'], $returningData['minimumCharge']);
        static::assertEquals($voucherData['orderCode'], $returningData['orderCode']);
        static::assertEquals($voucherData['modus'], $returningData['modus']);
        static::assertEquals($voucherData['taxConfig'], $returningData['taxConfig']);

        return $id;
    }

    /**
     * test the voucherCode validation methods with the created voucher
     *
     * @depends testAddVoucher
     */
    public function testValidateVoucherCode()
    {
        $params = [];
        $voucherModel = $this->createDummy(false);
        $voucherData = Shopware()->Models()->toArray($voucherModel);
        $params['value'] = $voucherData['voucherCode'];
        $params['param'] = $voucherData['id'];
        $this->Request()->setParams($params);
        $this->dispatch('backend/voucher/validateVoucherCode');

        static::assertEquals(1, $this->Response()->getBody());

        $this->Request()->clearParams();
        $this->Response()->clearBody();

        $params['value'] = $voucherData['voucherCode'];

        // Test with an unknown voucher id
        $params['param'] = 416531;
        $this->Request()->setParams($params);
        $this->dispatch('backend/voucher/validateVoucherCode');

        static::assertEmpty($this->Response()->getBody());
        $this->manager->remove($voucherModel);
        $this->manager->flush();
    }

    /**
     * Test the orderCode validation methods with the created voucher
     *
     * @depends testAddVoucher
     *
     * @param string $id
     */
    public function testValidateOrderCode($id)
    {
        $params = [];
        $voucherData = $this->voucherData;
        $params['value'] = $voucherData['orderCode'];
        $params['param'] = $id;

        $this->Request()->setParams($params);
        $this->dispatch('backend/voucher/validateOrderCode');

        static::assertEquals(1, $this->Response()->getBody());

        $this->Request()->clearParams();
        $this->Response()->clearBody();

        $params['value'] = $voucherData['orderCode'];

        // Test with an unknown voucher id
        $params['param'] = 416531;
        $this->Request()->setParams($params);
        $this->dispatch('backend/voucher/validateOrderCode');
        static::assertEmpty($this->Response()->getBody());
    }

    /**
     * Test updating a voucher
     *
     * @depends testGetVoucherDetail
     *
     * @param string $id
     */
    public function testUpdateVoucher($id)
    {
        $params = $this->voucherData;
        $params['id'] = $id;
        $params['description'] = 'description_update';
        $this->Request()->setParams($params);

        $this->dispatch('backend/voucher/saveVoucher');
        static::assertTrue($this->View()->success);
        static::assertEquals($params['description'], $this->View()->data['description']);

        return $id;
    }

    /**
     * Test generating voucher codes
     *
     * @depends testUpdateVoucher
     *
     * @param string $id
     */
    public function testGenerateVoucherCodes($id)
    {
        $voucherData = $this->voucherData;
        $params = [];
        $params['numberOfUnits'] = $voucherData['numberOfUnits'];
        $params['voucherId'] = (int) $id;
        $this->Request()->setParams($params);
        $this->dispatch('backend/voucher/createVoucherCodes');
        static::assertTrue($this->View()->success);

        return $id;
    }

    /**
     * Test the listing of the voucher codes
     *
     * @depends testGenerateVoucherCodes
     *
     * @param string $id
     */
    public function testGetVoucherCodes($id)
    {
        $this->dispatch('backend/voucher/getVoucherCodes?voucherID=' . $id);
        static::assertTrue($this->View()->success);
        static::assertCount(50, $this->View()->data);

        return $id;
    }

    /**
     * Test the exportVoucherCode Method
     *
     * @depends testGetVoucherCodes
     *
     * @param string $id
     */
    public function testExportVoucherCode($id)
    {
        $params = [];
        $params['voucherId'] = (int) $id;
        $this->Request()->setParams($params);
        $this->dispatch('backend/voucher/exportVoucherCode');
        $header = $this->Response()->getHeaders();

        $lastHeader = array_pop($header);
        static::assertEquals('Content-Disposition', $lastHeader['name']);
        static::assertEquals('attachment;filename=voucherCodes.csv', $lastHeader['value']);
        static::assertGreaterThan(1000, strlen($this->Response()->getBody()));

        return $id;
    }

    /**
     * Test the delete the voucher method
     *
     * @depends testExportVoucherCode
     *
     * @param string $id
     */
    public function testDeleteVoucher($id)
    {
        $params = [];
        $params['id'] = (int) $id;
        $this->Request()->setParams($params);
        $this->dispatch('backend/voucher/deleteVoucher');
        static::assertTrue($this->View()->success);
        static::assertNull($this->repository->find($params['id']));
    }

    /**
     * Test getTaxConfiguration Method
     */
    public function testGetTaxConfiguration()
    {
        $this->dispatch('backend/voucher/getTaxConfiguration');
        static::assertTrue($this->View()->success);
        static::assertNotEmpty($this->View()->data);
    }

    /**
     * Creates the dummy voucher
     *
     * @param bool $individualMode
     *
     * @return \Shopware\Models\Voucher\Voucher
     */
    private function getDummyVoucher($individualMode = true)
    {
        $voucher = new \Shopware\Models\Voucher\Voucher();
        $voucherData = $this->voucherData;
        if (!$individualMode) {
            $voucherData['modus'] = 0;
            $voucherData['voucherCode'] = 'phpUnitVoucherCode';
        }
        $voucher->fromArray($voucherData);

        return $voucher;
    }

    /**
     * Helper method to create the dummy object
     *
     * @param bool $individualMode
     *
     * @return \Shopware\Models\Voucher\Voucher
     */
    private function createDummy($individualMode = true)
    {
        $voucher = $this->getDummyVoucher($individualMode);
        $this->manager->persist($voucher);
        $this->manager->flush();

        return $voucher;
    }
}
