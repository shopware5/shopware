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

use Doctrine\DBAL\Connection;
use Shopware\Components\Api\Resource\CustomerStream;
use Shopware\Models\CustomerStream\CustomerStream as CustomerStreamEntity;

class CustomerStreamTest extends \Enlight_Components_Test_TestCase
{
    /**
     * @var CustomerStream
     */
    protected $resource;

    /**
     * @var Connection
     */
    protected $connection;

    protected function setUp(): void
    {
        Shopware()->Models()->clear();

        $this->resource = $this->createResource();
        $this->resource->setManager(Shopware()->Models());
        $this->connection = Shopware()->Container()->get('dbal_connection');
        $this->connection->beginTransaction();
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        Shopware()->Models()->clear();
        $this->connection->rollBack();
    }

    public function createResource()
    {
        return Shopware()->Container()->get('shopware.api.customer_stream');
    }

    public function testCreateStaticStream()
    {
        $data = [
            'name' => 'static stream',
            'customers' => [1],
            'static' => true,
        ];

        $stream = $this->resource->create($data);

        static::assertInstanceOf(CustomerStreamEntity::class, $stream);
        static::assertNotNull($stream->getId());
        static::assertTrue($stream->isStatic());

        $ids = $this->connection->fetchAll(
            'SELECT customer_id FROM s_customer_streams_mapping WHERE stream_id = :streamId',
            [':streamId' => (int) $stream->getId()]
        );

        $ids = array_column($ids, 'customer_id');

        static::assertEquals([1], $ids);
    }
}
