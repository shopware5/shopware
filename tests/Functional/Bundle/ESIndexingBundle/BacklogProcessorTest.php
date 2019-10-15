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

namespace Shopware\Tests\Functional\Bundle\ESIndexingBundle;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\DBAL\Connection;
use Shopware\Bundle\ESIndexingBundle\BacklogProcessor;
use Shopware\Bundle\ESIndexingBundle\BacklogProcessorInterface;
use Shopware\Bundle\ESIndexingBundle\BacklogReader;
use Shopware\Bundle\ESIndexingBundle\Product\ProductSynchronizer;
use Shopware\Bundle\ESIndexingBundle\Struct\Backlog;
use Shopware\Bundle\ESIndexingBundle\Struct\ShopIndex;
use Shopware\Bundle\ESIndexingBundle\Subscriber\ORMBacklogSubscriber;
use Shopware\Bundle\StoreFrontBundle\Struct\Shop;
use Shopware\Components\ContainerAwareEventManager;
use Shopware\Tests\Functional\Bundle\StoreFrontBundle\TestCase;
use Shopware\Tests\Functional\Traits\DatabaseTransactionBehaviour;

/**
 * @group elasticSearch
 */
class BacklogProcessorTest extends TestCase
{
    use DatabaseTransactionBehaviour;

    /**
     * @var BacklogProcessorInterface
     */
    private $backlogProcessor;

    /**
     * @var BacklogReader
     */
    private $backlogReader;

    /**
     * @var Connection
     */
    private $connection;

    /**
     * @var ContainerAwareEventManager
     */
    private $eventManager;

    public function __construct($name = null, array $data = [], $dataName = '')
    {
        parent::__construct($name, $data, $dataName);

        $this->backlogProcessor = Shopware()->Container()->get('shopware_elastic_search.backlog_processor');
        $this->backlogReader = Shopware()->Container()->get('shopware_elastic_search.backlog_reader');
        $this->connection = Shopware()->Container()->get('dbal_connection');
        $this->eventManager = Shopware()->Container()->get('events');
    }

    public function testBacklogProcessorAdds(): void
    {
        $payloadId = 1234;
        $this->backlogProcessor->add([
            $this->createDummyProductBacklog($payloadId),
        ]);

        $backlog = $this->retrieveLatestBacklog();

        static::assertNotNull($backlog);
        static::assertNotNull($backlog->getPayload());
        static::assertEquals($payloadId, $backlog->getPayload()['id']);
    }

    public function testBacklogProcessorProcesses(): void
    {
        $synchronizers = new ArrayCollection([$this->mockProductSynchronizer()]);

        $processor = new BacklogProcessor(
            $this->connection,
            $synchronizers,
            $this->eventManager
        );

        $processor->process(new ShopIndex('', new Shop(), ''), []);
    }

    private function retrieveLatestBacklog(): Backlog
    {
        $backlogs = $this->retrieveLatestBacklogs(1);

        return array_pop($backlogs);
    }

    private function retrieveLatestBacklogs(int $limit): array
    {
        return $this->backlogReader->read($this->backlogReader->getLastBacklogId(), $limit);
    }

    private function createDummyProductBacklog(int $productId): Backlog
    {
        return new Backlog(ORMBacklogSubscriber::EVENT_ARTICLE_INSERTED, ['id' => $productId]);
    }

    private function mockProductSynchronizer()
    {
        $synchronizer = $this->getMockBuilder(ProductSynchronizer::class)
            ->disableOriginalConstructor()
            ->setMethods(['supports', 'synchronize'])
            ->getMock();

        $synchronizer->expects(static::once())
            ->method('supports');

        $synchronizer->expects(static::once())
            ->method('synchronize');

        return $synchronizer;
    }
}
