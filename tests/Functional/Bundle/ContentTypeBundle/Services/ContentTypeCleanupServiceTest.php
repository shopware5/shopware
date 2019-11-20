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
use Shopware\Bundle\ContentTypeBundle\Services\AclSynchronizerInterface;
use Shopware\Bundle\ContentTypeBundle\Services\ContentTypeCleanupServiceInterface;
use Shopware\Tests\Functional\Traits\DatabaseTransactionBehaviour;

class ContentTypeCleanupServiceTest extends TestCase
{
    use DatabaseTransactionBehaviour;

    public function testCleanup(): void
    {
        Shopware()->Container()->get(AclSynchronizerInterface::class)->update(['customfoo']);

        Shopware()->Container()->get('dbal_connection')->insert('s_core_rewrite_urls', [
            'org_path' => 'sViewport=customfoo',
            'path' => 'foo',
            'main' => 0,
            'subshopID' => 0,
        ]);

        $urlCount = (int) Shopware()->Container()->get('dbal_connection')->fetchColumn('SELECT COUNT(*) FROM s_core_rewrite_urls');

        Shopware()->Container()->get(ContentTypeCleanupServiceInterface::class)->deleteContentType('foo');

        $newUrlCount = (int) Shopware()->Container()->get('dbal_connection')->fetchColumn('SELECT COUNT(*) FROM s_core_rewrite_urls');
        static::assertNotSame($urlCount, $newUrlCount);

        static::assertFalse((bool) Shopware()->Models()->getConnection()->fetchColumn('SELECT 1 FROM s_core_acl_resources WHERE name = "customfoo"'));
    }
}
