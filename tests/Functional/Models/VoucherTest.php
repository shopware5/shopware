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

namespace Shopware\Tests\Functional\Models;

use Enlight_Components_Test_TestCase;
use Shopware\Components\Model\ModelManager;
use Shopware\Models\Voucher\Repository;
use Shopware\Models\Voucher\Voucher;

class VoucherTest extends Enlight_Components_Test_TestCase
{
    protected ModelManager $em;

    protected Repository $repo;

    /**
     * @var array<string, string|int> voucher dummy data
     */
    private array $testData = [
        'description' => 'description',
        'minimumCharge' => '20',
        'modus' => '1',
        'numOrder' => '0',
        'voucherCode' => '',
        'numberOfUnits' => '50',
        'orderCode' => '65168phpunit',
        'percental' => '0',
        'taxConfig' => 'none',
        'shippingFree' => 0,
        'customerGroup' => 0,
        'restrictArticles' => '',
        'strict' => 0,
        'shopId' => 0,
        'bindToSupplier' => 0,
        'value' => '10',
    ];

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->em = Shopware()->Models();
        $this->repo = Shopware()->Models()->getRepository(Voucher::class);
    }

    /**
     * Tear down
     */
    protected function tearDown(): void
    {
        $voucher = $this->repo->findOneBy(['description' => 'description']);

        if (!empty($voucher)) {
            $this->em->remove($voucher);
            $this->em->flush();
        }
        parent::tearDown();
    }

    /**
     * Test case getter and setter
     */
    public function testGetterAndSetter(): void
    {
        $voucher = new Voucher();

        foreach ($this->testData as $field => $value) {
            $setMethod = 'set' . ucfirst($field);
            $getMethod = 'get' . ucfirst($field);

            $voucher->$setMethod($value);
            static::assertEquals($voucher->$getMethod(), $value);
        }
    }

    /**
     * Test case from array
     */
    public function testFromArrayWorks(): void
    {
        $voucher = new Voucher();
        $voucher->fromArray($this->testData);

        foreach ($this->testData as $fieldname => $value) {
            $getMethod = 'get' . ucfirst($fieldname);
            static::assertEquals($voucher->$getMethod(), $value);
        }
    }

    /**
     * Test case voucher should be persisted
     */
    public function testVoucherShouldBePersisted(): void
    {
        $voucher = new Voucher();
        $voucher->fromArray($this->testData);

        $this->em->persist($voucher);
        $this->em->flush();

        $voucherId = $voucher->getId();

        // remove form from entity manager
        $this->em->detach($voucher);
        unset($voucher);

        $voucher = $this->repo->find($voucherId);

        foreach ($this->testData as $fieldname => $value) {
            $getMethod = 'get' . ucfirst($fieldname);
            static::assertEquals($voucher->$getMethod(), $value);
        }
    }
}
