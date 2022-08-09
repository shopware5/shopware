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

namespace Shopware\Tests\Functional\Bundle\EsBackendBundle\Customer;

use Doctrine\DBAL\Connection;
use Elasticsearch\Client;
use Enlight_Components_Test_TestCase;
use Shopware\Tests\Functional\Bundle\StoreFrontBundle\Helper\ProgressHelper;
use Shopware\Tests\Functional\Traits\ContainerTrait;
use Shopware\Tests\Functional\Traits\DatabaseTransactionBehaviour;

/**
 * @group elasticSearch
 */
class CustomerIndexingTest extends Enlight_Components_Test_TestCase
{
    use ContainerTrait;
    use DatabaseTransactionBehaviour;

    public function testCanIndexCustomerWithBirthday(): void
    {
        $fixtures = file_get_contents(__DIR__ . '/fixtures/customer.sql');
        static::assertIsString($fixtures);
        $this->getContainer()->get(Connection::class)->exec($fixtures);

        $indexer = $this->getContainer()->get('shopware_es_backend.indexer');
        $indexer->cleanupIndices();
        $indexer->index(new ProgressHelper());

        $client = $this->getContainer()->get(Client::class);

        static::assertGreaterThanOrEqual(3, $client->count([
            'index' => 'sw_shop_backend_index_customer',
        ]));
    }
}
