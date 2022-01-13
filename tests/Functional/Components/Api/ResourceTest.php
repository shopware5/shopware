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

use Enlight_Components_Test_TestCase;
use Shopware\Components\Api\Exception\PrivilegeException;
use Shopware\Components\Api\Resource\Resource;
use Shopware_Components_Acl;

class ResourceTest extends Enlight_Components_Test_TestCase
{
    private Resource $resource;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp(): void
    {
        parent::setUp();

        Shopware()->Models()->clear();

        $this->resource = $this->getMockForAbstractClass(Resource::class);

        $this->resource->setManager(Shopware()->Models());
    }

    public function testResultModeShouldDefaultToArray(): void
    {
        static::assertEquals(Resource::HYDRATE_ARRAY, $this->resource->getResultMode());
    }

    public function testSetResultModeShouldShouldWork(): void
    {
        $this->resource->setResultMode(Resource::HYDRATE_OBJECT);

        static::assertEquals(Resource::HYDRATE_OBJECT, $this->resource->getResultMode());
    }

    public function testAutoFlushShouldDefaultToTrue(): void
    {
        static::assertTrue($this->resource->getAutoFlush());
    }

    public function testSetAutoFlushShouldWork(): void
    {
        $this->resource->setAutoFlush(false);

        static::assertFalse($this->resource->getAutoFlush());
    }

    public function testCheckPrivilegeShouldThrowException(): void
    {
        $this->expectException(PrivilegeException::class);
        $aclMock = $this->createMock(Shopware_Components_Acl::class);

        $aclMock->method('has')->willReturn(true);

        $aclMock->method('isAllowed')->willReturn(false);

        $this->resource->setRole('dummy');
        $this->resource->setAcl($aclMock);

        $this->resource->checkPrivilege('test');
    }

    public function testFooFlushShouldWork(): void
    {
        $aclMock = $this->createMock(Shopware_Components_Acl::class);

        $aclMock->expects(static::once())->method('isAllowed')->willReturn(true);

        $this->resource->setRole('dummy');
        $this->resource->setAcl($aclMock);
        $this->resource->checkPrivilege('test');
    }
}
