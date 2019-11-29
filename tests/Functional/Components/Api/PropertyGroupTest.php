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

use Shopware\Bundle\StoreFrontBundle\Struct\Property\Set;
use Shopware\Components\Api\Exception\CustomValidationException;
use Shopware\Components\Api\Exception\NotFoundException;
use Shopware\Components\Api\Exception\ParameterMissingException;
use Shopware\Components\Api\Resource\PropertyGroup;
use Shopware\Components\Api\Resource\Resource;
use Shopware\Models\Property\Group;

class PropertyGroupTest extends TestCase
{
    /**
     * @var PropertyGroup
     */
    protected $resource;

    /**
     * @return PropertyGroup
     */
    public function createResource()
    {
        return new PropertyGroup();
    }

    public function testCreateShouldThrowCustomValidationException()
    {
        $this->expectException(CustomValidationException::class);
        $testData = [
            'position' => 1,
            'comparable' => 1,
            'sortmode' => Set::SORT_LEGACY,
        ];

        $this->resource->create($testData);
    }

    public function testCreateShouldBeSuccessful()
    {
        $testData = [
            'name' => 'Eigenschaft1',
            'position' => 1,
            'comparable' => 1,
            'sortmode' => Set::SORT_ALPHANUMERIC,
        ];

        $group = $this->resource->create($testData);

        static::assertInstanceOf(Group::class, $group);
        static::assertGreaterThan(0, $group->getId());

        static::assertEquals($group->getPosition(), $testData['position']);
        static::assertEquals($group->getComparable(), $testData['comparable']);
        static::assertEquals($group->getSortMode(), $testData['sortmode']);

        return $group->getId();
    }

    /**
     * @depends testCreateShouldBeSuccessful
     */
    public function testGetOneShouldBeSuccessful($id)
    {
        $group = $this->resource->getOne($id);
        static::assertGreaterThan(0, $group['id']);
    }

    /**
     * @depends testCreateShouldBeSuccessful
     */
    public function testGetOneShouldBeAbleToReturnObject($id)
    {
        $this->resource->setResultMode(1);
        $group = $this->resource->getOne($id);

        static::assertInstanceOf(Group::class, $group);
        static::assertGreaterThan(0, $group->getId());
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
    public function testGetListShouldBeAbleToReturnObjects()
    {
        $this->resource->setResultMode(Resource::HYDRATE_OBJECT);
        $result = $this->resource->getList();

        static::assertArrayHasKey('data', $result);
        static::assertArrayHasKey('total', $result);

        static::assertGreaterThanOrEqual(1, $result['total']);
        static::assertGreaterThanOrEqual(1, $result['data']);

        static::assertInstanceOf(Group::class, $result['data'][0]);
    }

    /**
     * @depends testCreateShouldBeSuccessful
     */
    public function testUpdateShouldBeSuccessful($id)
    {
        $testData = [
            'name' => uniqid(mt_rand(), true) . 'testProperty',
            'sortmode' => 99,
        ];

        $group = $this->resource->update($id, $testData);

        static::assertInstanceOf(Group::class, $group);
        static::assertEquals($id, $group->getId());

        static::assertEquals($group->getName(), $testData['name']);
        static::assertEquals($group->getSortMode(), $testData['sortmode']);

        return $id;
    }

    public function testUpdateWithInvalidIdShouldThrowNotFoundException()
    {
        $this->expectException(NotFoundException::class);
        $this->resource->update(9999999, []);
    }

    public function testUpdateWithMissingIdShouldThrowParameterMissingException()
    {
        $this->expectException(ParameterMissingException::class);
        $this->resource->update('', []);
    }

    /**
     * @depends testUpdateShouldBeSuccessful
     */
    public function testDeleteShouldBeSuccessful($id)
    {
        $group = $this->resource->delete($id);

        static::assertInstanceOf(Group::class, $group);
        static::assertEquals(null, $group->getId());
    }

    public function testDeleteWithInvalidIdShouldThrowNotFoundException()
    {
        $this->expectException(NotFoundException::class);
        $this->resource->delete(9999999);
    }

    public function testDeleteWithMissingIdShouldThrowParameterMissingException()
    {
        $this->expectException(ParameterMissingException::class);
        $this->resource->delete('');
    }
}
