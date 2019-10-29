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

namespace Shopware\Tests\Functional\Bundle\ContentTypeBundle\Services;

use PHPUnit\Framework\TestCase;
use Shopware\Bundle\ContentTypeBundle\Services\AclSynchronizer;
use Shopware\Bundle\ContentTypeBundle\Services\AclSynchronizerInterface;
use Shopware\Tests\Functional\Traits\DatabaseTransactionBehaviour;

class AclSynchronizerTest extends TestCase
{
    use DatabaseTransactionBehaviour;

    /**
     * @var AclSynchronizer
     */
    private $service;

    protected function setUp(): void
    {
        $this->service = Shopware()->Container()->get(AclSynchronizerInterface::class);
    }

    public function testUpdate(): void
    {
        $this->service->update(['foo']);

        static::assertTrue((bool) Shopware()->Models()->getConnection()->fetchColumn('SELECT 1 FROM s_core_acl_resources WHERE name = "foo"'));
    }

    public function testUpdateTwice(): void
    {
        $this->service->update(['foo']);
        $this->service->update(['foo']);

        static::assertTrue((bool) Shopware()->Models()->getConnection()->fetchColumn('SELECT 1 FROM s_core_acl_resources WHERE name = "foo"'));
    }

    public function testUpdateAndRemove(): void
    {
        $this->service->update(['foo']);
        $this->service->remove('foo');

        static::assertFalse((bool) Shopware()->Models()->getConnection()->fetchColumn('SELECT 1 FROM s_core_acl_resources WHERE name = "foo"'));
    }
}
