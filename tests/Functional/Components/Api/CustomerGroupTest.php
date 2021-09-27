<?php

declare(strict_types=1);
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

namespace Shopware\Tests\Functional\Components\Api;

use Shopware\Components\Api\Exception\CustomValidationException;
use Shopware\Components\Api\Exception\NotFoundException;
use Shopware\Components\Api\Exception\ParameterMissingException;
use Shopware\Components\Api\Resource\CustomerGroup;
use Shopware\Models\Customer\Group;

class CustomerGroupTest extends TestCase
{
    /**
     * @var CustomerGroup
     */
    protected $resource;

    /**
     * @return CustomerGroup
     */
    public function createResource()
    {
        return new CustomerGroup();
    }

    public function testCreateWithInvalidDataShouldThrowValidationException(): void
    {
        $this->expectException(CustomValidationException::class);
        // required parameter 'name' is missing
        $testData = [
            'key' => 'TS',
            'tax' => 0,
            'taxInput' => true,
            'mode' => 0,
        ];

        $this->resource->create($testData);
    }

    public function testCreateShouldBeSuccessful(): int
    {
        $testData = [
            'key' => 'TS',
            'name' => 'Test',
            'tax' => 0,
            'taxInput' => 0,
            'mode' => 0,
        ];

        $customerGroup = $this->resource->create($testData);

        static::assertInstanceOf(Group::class, $customerGroup);
        static::assertGreaterThan(0, $customerGroup->getId());

        static::assertEquals($customerGroup->getKey(), $testData['key']);
        static::assertEquals($customerGroup->getName(), $testData['name']);
        static::assertEquals($customerGroup->getTax(), $testData['tax']);
        static::assertEquals($customerGroup->getTaxInput(), $testData['taxInput']);
        static::assertEquals($customerGroup->getMode(), $testData['mode']);

        return $customerGroup->getId();
    }

    /**
     * @depends testCreateShouldBeSuccessful
     */
    public function testGetOneShouldBeSuccessful($id): void
    {
        $CustomerGroup = $this->resource->getOne($id);
        static::assertGreaterThan(0, $CustomerGroup['id']);
    }

    /**
     * @depends testCreateShouldBeSuccessful
     */
    public function testGetListShouldBeSuccessful(): void
    {
        $result = $this->resource->getList();

        static::assertArrayHasKey('data', $result);
        static::assertArrayHasKey('total', $result);

        static::assertGreaterThanOrEqual(1, $result['total']);
        static::assertGreaterThanOrEqual(1, $result['data']);
    }

    /**
     * @depends testCreateShouldBeSuccessful
     */
    public function testUpdateShouldBeSuccessful($id)
    {
        $testData = [
            'key' => 'TS',
            'name' => 'Test update',
            'tax' => true,
            'taxInput' => 1,
            'mode' => 1,
        ];

        $customerGroup = $this->resource->update($id, $testData);

        static::assertInstanceOf(Group::class, $customerGroup);
        static::assertGreaterThan(0, $customerGroup->getId());

        static::assertEquals($customerGroup->getKey(), $testData['key']);
        static::assertEquals($customerGroup->getName(), $testData['name']);
        static::assertEquals($customerGroup->getTax(), $testData['tax']);
        static::assertEquals($customerGroup->getTaxInput(), $testData['taxInput']);
        static::assertEquals($customerGroup->getMode(), $testData['mode']);

        return $id;
    }

    public function testUpdateWithInvalidIdShouldThrowNotFoundException(): void
    {
        $this->expectException(NotFoundException::class);
        $this->resource->update(9999999, []);
    }

    public function testUpdateWithMissingIdShouldThrowParameterMissingException(): void
    {
        $this->expectException(ParameterMissingException::class);
        $this->resource->update('', []);
    }

    /**
     * @depends testUpdateShouldBeSuccessful
     */
    public function testDeleteShouldBeSuccessful($id): void
    {
        $customerGroup = $this->resource->delete($id);

        static::assertInstanceOf(Group::class, $customerGroup);
        static::assertSame(0, (int) $customerGroup->getId());
    }

    public function testDeleteWithInvalidIdShouldThrowNotFoundException(): void
    {
        $this->expectException(NotFoundException::class);
        $this->resource->delete(9999999);
    }

    public function testDeleteWithMissingIdShouldThrowParameterMissingException(): void
    {
        $this->expectException(ParameterMissingException::class);
        $this->resource->delete('');
    }

    /**
     * @depends testDeleteShouldBeSuccessful
     */
    public function testCreateShouldShouldPopulateDefaultValues(): void
    {
        $defaults = [
            'taxInput' => 1,
            'tax' => 1,
            'mode' => 0,
        ];

        $testData = [
            'key' => 'DT',
            'name' => 'Default-Test',
        ];

        $customerGroup = $this->resource->create($testData);

        static::assertInstanceOf(Group::class, $customerGroup);
        static::assertGreaterThan(0, $customerGroup->getId());

        static::assertEquals($customerGroup->getKey(), $testData['key']);
        static::assertEquals($customerGroup->getName(), $testData['name']);
        static::assertEquals($customerGroup->getTax(), $defaults['tax']);
        static::assertEquals($customerGroup->getTaxInput(), $defaults['taxInput']);
        static::assertEquals($customerGroup->getMode(), $defaults['mode']);

        $this->resource->delete($customerGroup->getId());
    }
}
