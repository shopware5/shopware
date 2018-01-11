<?php declare(strict_types=1);

namespace Shopware\DbalIndexing\Search;

use Doctrine\DBAL\Connection;
use Ramsey\Uuid\Uuid;
use Shopware\Api\Entity\Search\Criteria;
use Shopware\Api\Entity\Write\GenericWrittenEvent;
use Shopware\Api\Product\Definition\ProductDefinition;
use Shopware\Api\Product\Repository\ProductRepository;
use Shopware\Context\Struct\TranslationContext;
use Shopware\DbalIndexing\Common\ContextVariationService;
use Shopware\DbalIndexing\Common\IndexTableOperator;
use Shopware\DbalIndexing\Common\RepositoryIterator;
use Shopware\DbalIndexing\Event\ProgressAdvancedEvent;
use Shopware\DbalIndexing\Event\ProgressFinishedEvent;
use Shopware\DbalIndexing\Event\ProgressStartedEvent;
use Shopware\DbalIndexing\Indexer\IndexerInterface;
use Shopware\Framework\Doctrine\MultiInsertQueryQueue;
use Shopware\Product\Struct\ProductSearchResult;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class SearchIndexer implements IndexerInterface
{
    public const TABLE = 'search_keyword';
    public const DOCUMENT_TABLE = 'product_search_keyword';

    /**
     * @var Connection
     */
    private $connection;

    /**
     * @var ProductRepository
     */
    private $productRepository;

    /**
     * @var EventDispatcherInterface
     */
    private $eventDispatcher;

    /**
     * @var SearchAnalyzerRegistry
     */
    private $analyzerRegistry;

    /**
     * @var ContextVariationService
     */
    private $contextVariationService;

    /**
     * @var IndexTableOperator
     */
    private $indexTableOperator;

    public function __construct(
        Connection $connection,
        ProductRepository $productRepository,
        EventDispatcherInterface $eventDispatcher,
        ContextVariationService $contextVariationService,
        SearchAnalyzerRegistry $analyzerRegistry,
        IndexTableOperator $indexTableOperator
    ) {
        $this->connection = $connection;
        $this->productRepository = $productRepository;
        $this->eventDispatcher = $eventDispatcher;
        $this->analyzerRegistry = $analyzerRegistry;
        $this->contextVariationService = $contextVariationService;
        $this->indexTableOperator = $indexTableOperator;
    }

    public function index(\DateTime $timestamp): void
    {
        $this->indexTableOperator->createTable(self::TABLE, $timestamp);
        $this->indexTableOperator->createTable(self::DOCUMENT_TABLE, $timestamp);

        $table = $this->indexTableOperator->getIndexName(self::TABLE, $timestamp);
        $documentTable = $this->indexTableOperator->getIndexName(self::DOCUMENT_TABLE, $timestamp);

        $this->connection->executeUpdate('ALTER TABLE `' . $table . '` ADD PRIMARY KEY `shop_keyword` (`keyword`, `shop_id`);');
        $this->connection->executeUpdate('ALTER TABLE `' . $table . '` ADD INDEX `keyword` (`keyword`);');
        $this->connection->executeUpdate('ALTER TABLE `' . $table . '` ADD FOREIGN KEY (`shop_id`) REFERENCES `shop` (`id`) ON DELETE CASCADE ON UPDATE CASCADE');

        $this->connection->executeUpdate('ALTER TABLE `' . $documentTable . '` ADD PRIMARY KEY `product_shop_keyword` (`keyword`, `shop_id`, `product_id`);');
        $this->connection->executeUpdate('ALTER TABLE `' . $documentTable . '` ADD INDEX `keyword` (`keyword`);');
        $this->connection->executeUpdate('ALTER TABLE `' . $documentTable . '` ADD FOREIGN KEY (`product_id`) REFERENCES `product` (`id`) ON DELETE CASCADE ON UPDATE CASCADE');
        $this->connection->executeUpdate('ALTER TABLE `' . $documentTable . '` ADD FOREIGN KEY (`shop_id`) REFERENCES `shop` (`id`) ON DELETE CASCADE ON UPDATE CASCADE');

        $contexts = $this->contextVariationService->createContexts();

        foreach ($contexts as $context) {
            $this->indexContext($context, $timestamp);
        }

        $this->indexTableOperator->renameTable(self::TABLE, $timestamp);
        $this->indexTableOperator->renameTable(self::DOCUMENT_TABLE, $timestamp);
    }

    public function refresh(GenericWrittenEvent $event): void
    {
        $productEvent = $event->getEventByDefinition(ProductDefinition::class);
        if (!$productEvent) {
            return;
        }

        $context = $productEvent->getContext();
        $products = $this->productRepository->readBasic($productEvent->getIds(), $context);

        foreach ($products as $product) {
            $keywords = $this->analyzerRegistry->analyze($product, $context);
            $this->writeKeywords($context, $product->getId(), $keywords, self::TABLE, self::DOCUMENT_TABLE);
        }
    }

    private function indexContext(TranslationContext $context, \DateTime $timestamp): void
    {
        $criteria = new Criteria();
        $criteria->setOffset(0);
        $criteria->setLimit(50);

        $iterator = new RepositoryIterator($this->productRepository, $context, $criteria);

        $this->eventDispatcher->dispatch(
            ProgressStartedEvent::NAME,
            new ProgressStartedEvent(
                sprintf('Start analyzing search keywords for shop %s', $context->getShopId()),
                $iterator->getTotal()
            )
        );

        $table = $this->indexTableOperator->getIndexName(self::TABLE, $timestamp);
        $documentTable = $this->indexTableOperator->getIndexName(self::DOCUMENT_TABLE, $timestamp);

        $products = $iterator->fetch();
        /** @var ProductSearchResult $products */
        while ($products) {
            foreach ($products as $product) {
                $keywords = $this->analyzerRegistry->analyze($product, $context);
                $this->writeKeywords($context, $product->getId(), $keywords, $table, $documentTable);
            }

            $this->eventDispatcher->dispatch(
                ProgressAdvancedEvent::NAME,
                new ProgressAdvancedEvent($products->count())
            );

            $products = $iterator->fetch();
        }

        $this->eventDispatcher->dispatch(
            ProgressFinishedEvent::NAME,
            new ProgressFinishedEvent(sprintf('Finished analyzing search keywords for shop id %s', $context->getShopId()))
        );
    }

    private function writeKeywords(TranslationContext $context, string $productId, array $keywords, string $table, string $documentTable)
    {
        $queue = new MultiInsertQueryQueue($this->connection, 250, false, true);

        $shopId = Uuid::fromString($context->getShopId())->getBytes();
        $productId = Uuid::fromString($productId)->getBytes();

        foreach ($keywords as $keyword => $ranking) {
            $queue->addInsert($table, [
                'shop_id' => $shopId,
                'keyword' => $keyword,
            ]);

            $queue->addInsert($documentTable, [
                'id' => Uuid::uuid4()->getBytes(),
                'shop_id' => $shopId,
                'keyword' => $keyword,
                'ranking' => $ranking,
                'product_id' => $productId,
            ]);
        }

        $queue->execute();
    }
}