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

namespace Shopware\Tests\Functional\Bundle\ESIndexingBundle\Subscriber;

use Shopware\Bundle\ESIndexingBundle\BacklogReader;
use Shopware\Bundle\ESIndexingBundle\Struct\Backlog;
use Shopware\Bundle\ESIndexingBundle\Subscriber\ORMBacklogSubscriber;
use Shopware\Components\Model\ModelManager;
use Shopware\Models\Article\Article;
use Shopware\Tests\Functional\Bundle\StoreFrontBundle\TestCase;
use Shopware\Tests\Functional\Traits\DatabaseTransactionBehaviour;

/**
 * @group elasticSearch
 */
class ORMBacklogSubscriberTest extends TestCase
{
    use DatabaseTransactionBehaviour;

    /**
     * @var ORMBacklogSubscriber
     */
    private $backlogSubscriber;

    /**
     * @var BacklogReader
     */
    private $backlogReader;

    /**
     * @var ModelManager
     */
    private $modelManager;

    public function __construct($name = null, array $data = [], $dataName = '')
    {
        parent::__construct($name, $data, $dataName);

        $this->backlogSubscriber = Shopware()->Container()->get('shopware_elastic_search.orm_backlog_subscriber');
        $this->backlogReader = Shopware()->Container()->get('shopware_elastic_search.backlog_reader');
        $this->modelManager = Shopware()->Container()->get('models');
    }

    /**
     * Checks if corresponding backlog entries are created when a new product is created
     */
    public function testORMSubscriberAddsBacklogOnProductCreation(): void
    {
        $testProduct = $this->createDummyProduct();

        $this->backlogSubscriber->processQueue();

        $backlogEntries = $this->backlogReader->read(0, null);

        $articleInsertedEvents = array_filter($backlogEntries, $this->backlogFilter(
            ORMBacklogSubscriber::EVENT_ARTICLE_INSERTED,
            'id',
            $testProduct->getId()
        ));

        static::assertCount(1, $articleInsertedEvents);
    }

    /**
     * Checks if corresponding backlog entries are created when a product is updated
     */
    public function testORMSubscriberAddsBacklogOnProductUpdate(): void
    {
        $testProduct = $this->createDummyProduct();

        $testProduct->setName(sprintf('%s Update', $testProduct->getName()));
        $this->modelManager->persist($testProduct);
        $this->modelManager->flush();

        $this->backlogSubscriber->processQueue();

        $backlogEntries = $this->backlogReader->read(0, null);

        $articleUpdatedEvents = array_filter($backlogEntries, $this->backlogFilter(
            ORMBacklogSubscriber::EVENT_ARTICLE_UPDATED,
            'id',
            $testProduct->getId()
        ));

        static::assertCount(1, $articleUpdatedEvents);
    }

    /**
     * Checks if corresponding backlog entries are created when a product is deleted
     */
    public function testORMSubscriberAddsBacklogOnProductRemoval(): void
    {
        $testProduct = $this->createDummyProduct();
        $testProductId = $testProduct->getId();

        $this->modelManager->remove($testProduct);
        $this->modelManager->flush();

        $this->backlogSubscriber->processQueue();

        $backlogEntries = $this->backlogReader->read(0, null);

        $articleDeletedEvents = array_filter($backlogEntries, $this->backlogFilter(
            ORMBacklogSubscriber::EVENT_ARTICLE_DELETED,
            'id',
            $testProductId
        ));

        static::assertCount(1, $articleDeletedEvents);
    }

    /**
     * Returns a function which checks the event type and payload of a Backlog instance
     */
    private function backlogFilter(string $event, string $payloadProperty, int $payloadPropertyValue): callable
    {
        return static function (Backlog $backlog) use ($event, $payloadProperty, $payloadPropertyValue) {
            return $backlog->getEvent() === $event
                && array_key_exists($payloadProperty, $backlog->getPayload())
                && $backlog->getPayload()[$payloadProperty] === $payloadPropertyValue;
        };
    }

    private function createDummyProduct(): Article
    {
        $product = new Article();
        $product->setName('Shopware');

        $this->modelManager->persist($product);
        $this->modelManager->flush();

        return $product;
    }
}
