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

use Doctrine\DBAL\Driver\ResultStatement;
use PDO;
use Shopware\Bundle\StoreFrontBundle\Gateway;
use Shopware\Bundle\StoreFrontBundle\Service\MediaServiceInterface;
use Shopware\Bundle\StoreFrontBundle\Struct;

class CategoryGateway implements Gateway\CategoryGatewayInterface
{
    /**
     * @var Gateway\CategoryQueryHelperInterface
     */
    private $queryHelper;

    /**
     * @var Hydrator\CategoryHydrator
     */
    private $categoryHydrator;

    /**
     * @var MediaServiceInterface
     */
    private $mediaService;

    public function __construct(
        Gateway\CategoryQueryHelperInterface $queryHelper,
        Hydrator\CategoryHydrator $categoryHydrator,
        MediaServiceInterface $mediaService
    ) {
        $this->queryHelper = $queryHelper;
        $this->categoryHydrator = $categoryHydrator;
        $this->mediaService = $mediaService;
    }

    /**
     * @return Struct\Category
     */
    public function get($id, Struct\ShopContextInterface $context)
    {
        /*
         * @deprecated since 5.5, will be removed in Shopware 5.7
         *
         * Allowing an array as a parameter is supported in 5.5/5.6 for legacy reasons.
         * The check below will be removed in Shopware 5.7
         */
        if (is_array($id)) {
            trigger_error(sprintf('Calling %s:%s with an array of Ids is deprecated since Shopware 5.5 and will crash with 5.7. Use a single int Id instead.', __CLASS__, __METHOD__), E_USER_DEPRECATED);

            $id = $id[0];
        }
        $categories = $this->getList([$id], $context);

        return array_shift($categories);
    }

    /**
     * {@inheritdoc}
     */
    public function getProductsCategories(array $products, Struct\ShopContextInterface $context)
    {
        $productIds = array_map(function (Struct\BaseProduct $product) {
            return $product->getId();
        }, $products);

        $mapping = $this->getMapping($productIds);

        $ids = $this->getMappingIds($mapping);

        $categories = $this->getList($ids, $context);

        $result = [];
        /** @var Struct\BaseProduct[] $products */
        foreach ($products as $product) {
            $id = $product->getId();
            if (!isset($mapping[$id])) {
                continue;
            }

            /** @var int[] $ids */
            $ids = explode(',', $mapping[$id]);

            /** @var int[] $productCategories */
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
    public function getList(array $ids, Struct\ShopContextInterface $context)
    {
        $query = $this->getQuery($ids, $context);

        /** @var ResultStatement $statement */
        $statement = $query->execute();

        $data = $statement->fetchAll(PDO::FETCH_ASSOC);

        //use php usort instead of running mysql order by to prevent file-sort and temporary table statement
        usort($data, function ($a, $b) {
            if ($a['__category_position'] === $b['__category_position']) {
                return $a['__category_id'] > $b['__category_id'];
            }

            return $a['__category_position'] > $b['__category_position'];
        });

        $categories = [];
        foreach ($data as $row) {
            $id = $row['__category_id'];
            $categories[$id] = $this->categoryHydrator->hydrate($this->translateCategoryData($row, $context));
        }

        return $categories;
    }

    protected function getQuery(array $numbers, Struct\ShopContextInterface $context)
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
    private function getMappingIds(array $mapping)
    {
        $ids = [];
        foreach ($mapping as $row) {
            $ids = array_merge($ids, explode(',', $row));
        }
        /** @var array<int> $ids */
        $ids = array_unique($ids);

        return $ids;
    }

    /**
     * @param int[]             $mapping
     * @param Struct\Category[] $categories
     *
     * @return Struct\Category[]
     */
    private function getProductCategories(array $mapping, array $categories)
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
     * @return array
     */
    private function translateCategoryData(array $category, Struct\ShopContextInterface $context)
    {
        if (empty($category['__category_translation'])) {
            return $category;
        }

        $translation = unserialize($category['__category_translation'], ['allowed_classes' => false]);

        if (!empty($translation['imagePath'])) {
            $category['mediaTranslation'] = $this->mediaService->get($translation['imagePath'], $context);
        }

        if (!empty($translation['streamId'])) {
            $category['__stream_id'] = $translation['streamId'];
        }

        return $category;
    }
}
