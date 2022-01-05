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

use Enlight_Components_Test_TestCase;
use Shopware\Components\Api\Exception\NotFoundException;
use Shopware\Components\Api\Exception\ParameterMissingException;
use Shopware\Components\Api\Exception\PrivilegeException;
use Shopware\Components\Api\Resource\Resource as APIResource;
use Shopware_Components_Acl;

/**
 * Abstract TestCase for Resource-Tests
 */
abstract class TestCase extends Enlight_Components_Test_TestCase
{
    /**
     * @var APIResource
     */
    protected $resource;

    protected function setUp(): void
    {
        parent::setUp();

        Shopware()->Models()->clear();

        $this->resource = $this->createResource();
        $this->resource->setManager(Shopware()->Models());
    }

    protected function tearDown(): void
    {
        Shopware()->Models()->clear();
        parent::tearDown();
    }

    /**
     * @return APIResource
     */
    abstract public function createResource();

    public function testGetOneWithMissingPrivilegeShouldThrowPrivilegeException(): void
    {
        $this->expectException(PrivilegeException::class);
        $this->resource->setRole('dummy');
        $this->resource->setAcl($this->getAclMock());

        static::assertTrue(method_exists($this->resource, 'getOne'));
        $this->resource->getOne(1);
    }

    public function testGetOneWithInvalidIdShouldThrowNotFoundException(): void
    {
        $this->expectException(NotFoundException::class);
        static::assertTrue(method_exists($this->resource, 'getOne'));
        $this->resource->getOne(9999999);
    }

    public function testGetOneWithMissingIdShouldThrowParameterMissingException(): void
    {
        $this->expectException(ParameterMissingException::class);
        static::assertTrue(method_exists($this->resource, 'getOne'));
        $this->resource->getOne(0);
    }

    protected function getAclMock(): Shopware_Components_Acl
    {
        $aclMock = $this->createMock(Shopware_Components_Acl::class);

        $aclMock->method('has')
                ->willReturn(true);

        $aclMock->method('isAllowed')
                ->willReturn(false);

        return $aclMock;
    }
}
