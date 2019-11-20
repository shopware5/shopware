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

namespace Shopware\Tests\Functional\Bundle\EsBackendBundle;

use Shopware\Components\Random;
use Shopware\Models\Customer\Customer;
use Shopware\Tests\Functional\Bundle\StoreFrontBundle\ProgressHelper;

/**
 * @group elasticSearch
 */
class BacklogTest extends \Enlight_Components_Test_TestCase
{
    public function setUp(): void
    {
        parent::setUp();

        $indexer = Shopware()->Container()->get('shopware_es_backend.indexer');
        $indexer->index(new ProgressHelper());
    }

    public function testBacklogWillBeWritten()
    {
        $customers = Shopware()->Models()->getRepository(Customer::class)->findAll();
        $customers[0]->setReferer(Random::getAlphanumericString(12));

        Shopware()->Models()->persist($customers[0]);
        Shopware()->Models()->flush($customers[0]);

        static::assertGreaterThanOrEqual(1, Shopware()->Db()->fetchOne('SELECT COUNT(*) FROM s_es_backend_backlog'));
    }
}
