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

namespace Shopware\Bundle\StoreFrontBundle\Gateway\DBAL;

use Assert\Assertion;
use Doctrine\DBAL\Query\QueryBuilder;
use PDO;
use Shopware\Bundle\StoreFrontBundle\Gateway\CategoryGatewayInterface;
use Shopware\Bundle\StoreFrontBundle\Gateway\CategoryQueryHelperInterface;
use Shopware\Bundle\StoreFrontBundle\Gateway\DBAL\Hydrator\CategoryHydrator;
use Shopware\Bundle\StoreFrontBundle\Service\MediaServiceInterface;
use Shopware\Bundle\StoreFrontBundle\Struct\BaseProduct;
use Shopware\Bundle\StoreFrontBundle\Struct\Category;
use Shopware\Bundle\StoreFrontBundle\Struct\ShopContextInterface;

class CategoryGateway implements CategoryGatewayInterface
{
    private CategoryQueryHelperInterface $queryHelper;

    private CategoryHydrator $categoryHydrator;

    private MediaServiceInterface $mediaService;

    public function __construct(
        CategoryQueryHelperInterface $queryHelper,
        CategoryHydrator $categoryHydrator,
        MediaServiceInterface $mediaService
    ) {
        $this->queryHelper = $queryHelper;
        $this->categoryHydrator = $categoryHydrator;
        $this->mediaService = $mediaService;
    }

    /**
     * {@inheritdoc}
     */
    public function get($id, ShopContextInterface $context)
    {
        Assertion::integer($id);
        $categories = $this->getList([$id], $context);

        return array_shift($categories);
    }

    /**
     * {@inheritdoc}
     */
    public function getProductsCategories(array $products, ShopContextInterface $context)
    {
        $productIds = array_map(function (BaseProduct $product) {
            return $product->getId();
        }, $products);

        $mapping = $this->getMapping($productIds);

        $ids = $this->getMappingIds($mapping);

        $categories = $this->getList($ids, $context);

        $result = [];
        foreach ($products as $product) {
            $id = $product->getId();
            if (!isset($mapping[$id])) {
                continue;
            }

            $ids = explode(',', $mapping[$id]);
            $ids = array_map('\intval', $ids);

            $productCategories = $this->getProductCategories(
                $ids,
                $categories
            );
            $result[$product->getNumber()] = $productCategories;
        }

        return $result;
    }

    /**
     * {@inheritdoc}
     */
    public function getList(array $ids, ShopContextInterface $context)
    {
        $data = $this->getQuery($ids, $context)->execute()->fetchAll(PDO::FETCH_ASSOC);

        //use php usort instead of running mysql order by to prevent file-sort and temporary table statement
        usort($data, function ($a, $b) {
            if ($a['__category_position'] === $b['__category_position']) {
                return $a['__category_id'] <=> $b['__category_id'];
            }

            return $a['__category_position'] <=> $b['__category_position'];
        });

        $categories = [];
        foreach ($data as $row) {
            $id = $row['__category_id'];
            $categories[$id] = $this->categoryHydrator->hydrate($this->translateCategoryData($row, $context));
        }

        return $categories;
    }

    /**
     * @param array<int> $numbers Category IDs
     *
     * @return QueryBuilder
     */
    protected function getQuery(array $numbers, ShopContextInterface $context)
    {
        return $this->queryHelper->getQuery($numbers, $context);
    }

    /**
     * @param int[] $ids
     *
     * @return array<int, string> indexed by product id
     */
    protected function getMapping(array $ids)
    {
        return $this->queryHelper->getMapping($ids)->execute()->fetchAll(PDO::FETCH_KEY_PAIR);
    }

    /**
     * @param string[] $mapping
     *
     * @return int[]
     */
    private function getMappingIds(array $mapping): array
    {
        $ids = [];
        foreach ($mapping as $row) {
            $ids = array_merge($ids, explode(',', $row));
        }
        $ids = array_unique($ids);

        return array_map('\intval', $ids);
    }

    /**
     * @param int[]      $mapping
     * @param Category[] $categories
     *
     * @return Category[]
     */
    private function getProductCategories(array $mapping, array $categories): array
    {
        $productCategories = [];
        foreach ($mapping as $categoryId) {
            if (!isset($categories[$categoryId])) {
                continue;
            }
            $productCategories[] = $categories[$categoryId];
        }

        return $productCategories;
    }

    /**
     * Resolves translated data for media and streamId
     *
     * @param array<string, mixed> $category
     *
     * @return array<string, mixed>
     */
    private function translateCategoryData(array $category, ShopContextInterface $context): array
    {
        if (empty($category['__category_translation'])) {
            return $category;
        }

        $translation = @unserialize($category['__category_translation'], ['allowed_classes' => false]);
        if ($translation === false) {
            $translation = [];
        }

        if (!empty($translation['imagePath'])) {
            $category['mediaTranslation'] = $this->mediaService->get($translation['imagePath'], $context);
        }

        if (!empty($translation['streamId'])) {
            $category['__stream_id'] = $translation['streamId'];
        }

        return $category;
    }
}
