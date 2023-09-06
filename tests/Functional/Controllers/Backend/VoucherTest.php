<?php

declare(strict_types=1);
/**
 * Shopware 5
 * Copyright (c) shopware AG
 *
 * According to our licensing model, this program can be used
 * under the terms of the GNU Affero General Public License, version 3.
 *
 * The texts of the GNU Affero General Public License with an additional
 * permission can be found at and in the LICENSE file you have received
 * along with this program.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 * See the GNU Affero General Public License for more details.
 *
 * "Shopware" is a registered trademark of shopware AG.
 * The licensing of the program under the AGPLv3 does not imply a
 * trademark license. Therefore, any rights, title and interest in
 * our trademarks remain entirely with the shopware AG.
 */

namespace Shopware\Tests\Functional\Controllers\Backend;

use Enlight_Components_Test_Controller_TestCase;
use Shopware\Components\Model\ModelManager;
use Shopware\Models\Voucher\Repository;
use Shopware\Models\Voucher\Voucher;

class VoucherTest extends Enlight_Components_Test_Controller_TestCase
{
    /**
     * @var array<string, mixed>
     */
    private array $voucherData = [
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

    private Repository $repository;

    private ModelManager $manager;

    public function setUp(): void
    {
        parent::setUp();

        $this->manager = Shopware()->Models();
        $this->repository = Shopware()->Models()->getRepository(Voucher::class);

        // Disable auth and acl
        Shopware()->Plugins()->Backend()->Auth()->setNoAuth();
        Shopware()->Plugins()->Backend()->Auth()->setNoAcl();
    }

    public function testGetVoucher(): void
    {
        // Delete old data
        $vouchers = $this->repository->findBy(['orderCode' => '65168phpunit']);
        foreach ($vouchers as $voucher) {
            $this->manager->remove($voucher);
        }
        $this->manager->flush();

        $voucher = $this->createDummy();

        $this->dispatch('backend/voucher/getVoucher?page=1&start=0&limit=2000');
        static::assertTrue($this->View()->getAssign('success'));
        $returnData = $this->View()->getAssign('data');
        static::assertNotEmpty($returnData);
        static::assertGreaterThan(0, $this->View()->getAssign('totalCount'));
        $lastInsert = $returnData[\count($returnData) - 1];
        static::assertEquals($voucher->getId(), $lastInsert['id']);

        $this->manager->remove($voucher);
        $this->manager->flush();
    }

    public function testAddVoucher(): int
    {
        $this->manager->getConnection()->executeStatement('DELETE FROM s_emarketing_vouchers WHERE ordercode = :code', [
            ':code' => $this->voucherData['orderCode'],
        ]);

        $params = $this->voucherData;
        $this->Request()->setParams($params);
        $this->dispatch('backend/voucher/saveVoucher');
        static::assertTrue($this->View()->getAssign('success'));
        static::assertEquals($params['description'], $this->View()->getAssign('data')['description']);

        return $this->View()->getAssign('data')['id'];
    }

    /**
     * @depends testAddVoucher
     */
    public function testGetVoucherDetail(int $id): int
    {
        $params['voucherID'] = $id;
        $this->Request()->setParams($params);
        $this->dispatch('backend/voucher/getVoucherDetail');
        static::assertTrue($this->View()->getAssign('success'));
        $returningData = $this->View()->getAssign('data');
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
     * @depends testAddVoucher
     */
    public function testValidateVoucherCode(): void
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
     * @depends testAddVoucher
     */
    public function testValidateOrderCode(int $id): void
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
     * @depends testGetVoucherDetail
     */
    public function testUpdateVoucher(int $id): int
    {
        $params = $this->voucherData;
        $params['id'] = $id;
        $params['description'] = 'description_update';
        $this->Request()->setParams($params);

        $this->dispatch('backend/voucher/saveVoucher');
        static::assertTrue($this->View()->getAssign('success'));
        static::assertEquals($params['description'], $this->View()->getAssign('data')['description']);

        return $id;
    }

    /**
     * @depends testUpdateVoucher
     */
    public function testGenerateVoucherCodes(int $id): int
    {
        $voucherData = $this->voucherData;
        $params = [];
        $params['numberOfUnits'] = $voucherData['numberOfUnits'];
        $params['voucherId'] = $id;
        $this->Request()->setParams($params);
        $this->dispatch('backend/voucher/createVoucherCodes');
        static::assertTrue($this->View()->getAssign('success'));

        return $id;
    }

    /**
     * @depends testGenerateVoucherCodes
     */
    public function testGetVoucherCodes(int $id): int
    {
        $this->dispatch('backend/voucher/getVoucherCodes?voucherID=' . $id);
        static::assertTrue($this->View()->getAssign('success'));
        static::assertCount(50, $this->View()->getAssign('data'));

        return $id;
    }

    /**
     * @depends testGetVoucherCodes
     */
    public function testExportVoucherCode(int $id): int
    {
        $params = [];
        $params['voucherId'] = $id;
        $this->Request()->setParams($params);
        $this->dispatch('backend/voucher/exportVoucherCode');
        $header = $this->Response()->getHeaders();

        $lastHeader = array_pop($header);
        static::assertEquals('Content-Disposition', $lastHeader['name']);
        static::assertEquals('attachment;filename=voucherCodes.csv', $lastHeader['value']);
        $body = $this->Response()->getBody();
        static::assertIsString($body);
        static::assertGreaterThan(1000, mb_strlen($body));

        return $id;
    }

    /**
     * @depends testExportVoucherCode
     */
    public function testDeleteVoucher(int $id): void
    {
        $params = [];
        $params['id'] = $id;
        $this->Request()->setParams($params);
        $this->dispatch('backend/voucher/deleteVoucher');
        static::assertTrue($this->View()->getAssign('success'));
        static::assertNull($this->repository->find($params['id']));
    }

    public function testGetTaxConfiguration(): void
    {
        $this->dispatch('backend/voucher/getTaxConfiguration');
        static::assertTrue($this->View()->getAssign('success'));
        static::assertNotEmpty($this->View()->getAssign('data'));
    }

    private function getDummyVoucher(bool $individualMode = true): Voucher
    {
        $voucher = new Voucher();
        $voucherData = $this->voucherData;
        if (!$individualMode) {
            $voucherData['modus'] = 0;
            $voucherData['voucherCode'] = 'phpUnitVoucherCode';
        }
        $voucher->fromArray($voucherData);

        return $voucher;
    }

    private function createDummy(bool $individualMode = true): Voucher
    {
        $voucher = $this->getDummyVoucher($individualMode);
        $this->manager->persist($voucher);
        $this->manager->flush();

        return $voucher;
    }
}
