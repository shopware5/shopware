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

namespace Shopware\Tests\Functional\Components\Api;

use Shopware\Components\Api\Resource\CustomerGroup;

/**
 * @CustomerGroup  Shopware
 */
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

    public function testCreateWithInvalidDataShouldThrowValidationException()
    {
        $this->expectException('Shopware\Components\Api\Exception\CustomValidationException');
        // required parameter 'name' is missing
        $testData = [
            'key' => 'TS',
            'tax' => 0,
            'taxInput' => true,
            'mode' => 0,
        ];

        $this->resource->create($testData);
    }

    public function testCreateShouldBeSuccessful()
    {
        $testData = [
            'key' => 'TS',
            'name' => 'Test',
            'tax' => 0,
            'taxInput' => 0,
            'mode' => 0,
        ];

        $customerGroup = $this->resource->create($testData);

        static::assertInstanceOf('\Shopware\Models\Customer\Group', $customerGroup);
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
    public function testGetOneShouldBeSuccessful($id)
    {
        $CustomerGroup = $this->resource->getOne($id);
        static::assertGreaterThan(0, $CustomerGroup['id']);
    }

    /**
     * @depends testCreateShouldBeSuccessful
     */
    public function testGetListShouldBeSuccessful()
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

        static::assertInstanceOf('\Shopware\Models\Customer\Group', $customerGroup);
        static::assertGreaterThan(0, $customerGroup->getId());

        static::assertEquals($customerGroup->getKey(), $testData['key']);
        static::assertEquals($customerGroup->getName(), $testData['name']);
        static::assertEquals($customerGroup->getTax(), $testData['tax']);
        static::assertEquals($customerGroup->getTaxInput(), $testData['taxInput']);
        static::assertEquals($customerGroup->getMode(), $testData['mode']);

        return $id;
    }

    public function testUpdateWithInvalidIdShouldThrowNotFoundException()
    {
        $this->expectException('Shopware\Components\Api\Exception\NotFoundException');
        $this->resource->update(9999999, []);
    }

    public function testUpdateWithMissingIdShouldThrowParameterMissingException()
    {
        $this->expectException('Shopware\Components\Api\Exception\ParameterMissingException');
        $this->resource->update('', []);
    }

    /**
     * @depends testUpdateShouldBeSuccessful
     */
    public function testDeleteShouldBeSuccessful($id)
    {
        $customerGroup = $this->resource->delete($id);

        static::assertInstanceOf('\Shopware\Models\Customer\Group', $customerGroup);
        static::assertEquals(null, $customerGroup->getId());
    }

    public function testDeleteWithInvalidIdShouldThrowNotFoundException()
    {
        $this->expectException('Shopware\Components\Api\Exception\NotFoundException');
        $this->resource->delete(9999999);
    }

    public function testDeleteWithMissingIdShouldThrowParameterMissingException()
    {
        $this->expectException('Shopware\Components\Api\Exception\ParameterMissingException');
        $this->resource->delete('');
    }

    /**
     * @depends testDeleteShouldBeSuccessful
     */
    public function testCreateShouldShouldPopulateDefaultValues()
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

        static::assertInstanceOf('\Shopware\Models\Customer\Group', $customerGroup);
        static::assertGreaterThan(0, $customerGroup->getId());

        static::assertEquals($customerGroup->getKey(), $testData['key']);
        static::assertEquals($customerGroup->getName(), $testData['name']);
        static::assertEquals($customerGroup->getTax(), $defaults['tax']);
        static::assertEquals($customerGroup->getTaxInput(), $defaults['taxInput']);
        static::assertEquals($customerGroup->getMode(), $defaults['mode']);

        $this->resource->delete($customerGroup->getId());
    }
}
