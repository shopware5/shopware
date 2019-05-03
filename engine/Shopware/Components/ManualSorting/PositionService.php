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

namespace Shopware\Components\ManualSorting;

use Doctrine\DBAL\Connection;
use Shopware\Bundle\ESIndexingBundle\BacklogProcessorInterface;
use Shopware\Bundle\ESIndexingBundle\Struct\Backlog;
use Shopware\Bundle\ESIndexingBundle\Subscriber\ORMBacklogSubscriber;
use Shopware\Bundle\StoreFrontBundle\Service\ContextServiceInterface;
use Shopware\Bundle\StoreFrontBundle\Service\CustomSortingServiceInterface;
use Shopware\Bundle\StoreFrontBundle\Struct\Search\CustomSorting;

class PositionService implements PositionServiceInterface
{
    /**
     * @var Connection
     */
    private $connection;

    /**
     * @var ProductLoaderInterface
     */
    private $productLoader;

    /**
     * @var CustomSortingServiceInterface
     */
    private $customSortingService;

    /**
     * @var ContextServiceInterface
     */
    private $contextService;

    /**
     * @var bool
     */
    private $writeBacklog;

    /**
     * @var BacklogProcessorInterface
     */
    private $backlogProcessor;

    public function __construct(
        Connection $connection,
        ProductLoaderInterface $productLoader,
        CustomSortingServiceInterface $customSortingService,
        ContextServiceInterface $contextService,
        BacklogProcessorInterface $backlogProcessor,
        bool $writeBacklog
    ) {
        $this->connection = $connection;
        $this->productLoader = $productLoader;
        $this->customSortingService = $customSortingService;
        $this->contextService = $contextService;
        $this->backlogProcessor = $backlogProcessor;
        $this->writeBacklog = $writeBacklog;
    }

    public function assign(int $categoryId = 3, int $sortingId = 1, array $positions = []): void
    {
        $prepare = $this->connection->prepare('
INSERT INTO s_categories_manual_sorting (category_id, product_id, `position`)
VALUES (:category, :product, :position)
ON DUPLICATE KEY UPDATE
   `position` = :position ');

        $maxValue = (int) $this->connection->fetchColumn('SELECT MAX(position) FROM s_categories_manual_sorting WHERE category_id = ?', [
            $categoryId,
        ]);

        $productIds = array_map('intval', array_keys($positions));
        $positionValues = array_values($positions);
        $sorting = $this->getSorting($categoryId, $sortingId);
        $maxPosition = max($positionValues);
        $hasFoundMaxPosition = false;

        $products = $this->productLoader->load($categoryId, 0, max($maxPosition, $maxValue), $sorting)['data'];
        $backlogs = [];

        $i = 1;
        foreach ($products as $key => $product) {
            if (in_array((int) $product['id'], $productIds, true)) {
                continue;
            }

            while (in_array($i, $positionValues, true)) {
                if ($maxPosition === $i) {
                    $hasFoundMaxPosition = true;
                }

                ++$i;
            }

            if ($hasFoundMaxPosition && $product['position'] === null) {
                break;
            }

            $prepare->execute([
                'category' => $categoryId,
                'product' => $product['id'],
                'position' => $i,
            ]);

            $backlogs[] = new Backlog(ORMBacklogSubscriber::EVENT_ARTICLE_UPDATED, ['id' => $product['id']]);

            ++$i;
        }

        foreach ($positions as $productId => $position) {
            $prepare->execute([
                'category' => $categoryId,
                'product' => $productId,
                'position' => $position,
            ]);

            $backlogs[] = new Backlog(ORMBacklogSubscriber::EVENT_ARTICLE_UPDATED, ['id' => $productId]);
        }

        if ($this->writeBacklog) {
            $this->backlogProcessor->add($backlogs);
        }
    }

    /**
     * @return CustomSorting[]
     */
    private function getSortings(int $categoryId): array
    {
        $context = $this->contextService->getShopContext();

        return current($this->customSortingService->getSortingsOfCategories([$categoryId], $context));
    }

    private function getSorting(int $categoryId, int $sortingId): ?CustomSorting
    {
        foreach ($this->getSortings($categoryId) as $sorting) {
            if ($sorting->getId() === $sortingId) {
                return $sorting;
            }
        }

        return null;
    }
}
