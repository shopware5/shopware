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

namespace Shopware\Bundle\BenchmarkBundle\Provider;

use Doctrine\DBAL\Connection;
use Shopware\Bundle\BenchmarkBundle\BatchableProviderInterface;
use Shopware\Bundle\StoreFrontBundle\Struct\ShopContextInterface;

class ProductsProvider implements BatchableProviderInterface
{
    private const NAME = 'products';

    /**
     * @var Connection
     */
    private $dbalConnection;

    /**
     * @var ShopContextInterface
     */
    private $shopContext;

    public function __construct(Connection $dbalConnection)
    {
        $this->dbalConnection = $dbalConnection;
    }

    public function getName()
    {
        return self::NAME;
    }

    /**
     * {@inheritdoc}
     */
    public function getBenchmarkData(ShopContextInterface $shopContext, $batchSize = null)
    {
        $this->shopContext = $shopContext;

        return [
            'list' => $this->getProductList($batchSize),
        ];
    }

    /**
     * @param int $batchSize
     *
     * @return array
     */
    private function getProductList($batchSize = null)
    {
        $config = $this->getConfig();
        $batch = (int) $config['batch_size'];
        $lastProductId = (int) $config['last_product_id'];

        if ($batchSize !== null) {
            $batch = $batchSize;
        }

        $productIds = $this->getProductIds($batch, $lastProductId);

        $basicProducts = $this->getBasicProductData($productIds);

        $productIds = array_keys($basicProducts);

        $variantsPerProduct = $this->getVariantsForProducts($productIds);
        $imagesPerProduct = $this->getImagesPerProduct($productIds);

        foreach ($basicProducts as $productId => &$basicProduct) {
            $basicProduct['variants'] = [];
            if (array_key_exists($productId, $variantsPerProduct)) {
                $basicProduct['variants'] = $variantsPerProduct[$productId];
            }

            $basicProduct['images'] = [];
            if (array_key_exists($productId, $imagesPerProduct)) {
                $basicProduct['images'] = $imagesPerProduct[$productId];
            }
        }

        $basicProducts = array_map(function ($item) {
            $item['active'] = (bool) $item['active'];
            $item['notificationEnabled'] = (bool) $item['notificationEnabled'];
            $item['instock'] = (int) $item['instock'];
            $item['instockMinimum'] = (int) $item['instockMinimum'];
            $item['sale'] = (int) $item['sale'];
            $item['minPurchase'] = (int) $item['minPurchase'];
            $item['maxPurchase'] = (int) $item['maxPurchase'];
            $item['purchaseSteps'] = (int) $item['purchaseSteps'];
            $item['shippingReady'] = (bool) $item['shippingReady'];
            $item['shippingFree'] = (bool) $item['shippingFree'];
            $item['pseudoSales'] = (int) $item['pseudoSales'];
            $item['topSeller'] = (int) $item['topSeller'];

            $item['variants'] = array_map(function ($item) {
                $item['active'] = (bool) $item['active'];
                $item['instock'] = (int) $item['instock'];
                $item['instockMinimum'] = (int) $item['instockMinimum'];
                $item['sale'] = (int) $item['sale'];
                $item['minPurchase'] = (int) $item['minPurchase'];
                $item['maxPurchase'] = (int) $item['maxPurchase'];
                $item['purchaseSteps'] = (int) $item['purchaseSteps'];
                $item['shippingReady'] = (bool) $item['shippingReady'];
                $item['shippingFree'] = (bool) $item['shippingFree'];

                return $item;
            }, $item['variants']);

            return $item;
        }, $basicProducts);

        return array_values($basicProducts);
    }

    /**
     * @return array
     */
    private function getBasicProductData(array $productIds)
    {
        $queryBuilder = $this->dbalConnection->createQueryBuilder();

        return $queryBuilder->select([
            'details.articleID',
            'details.articleID as productId',
            'details.active',
            'details.instock',
            'details.stockmin as instockMinimum',
            'details.lastStock as sale',
            'details.minpurchase as minPurchase',
            'details.maxpurchase as maxPurchase',
            'details.purchasesteps as purchaseSteps',
            'details.instock > 0 as shippingReady',
            'details.shippingfree as shippingFree',
            'productMain.pseudosales as pseudoSales',
            'productMain.topseller as topSeller',
            'productMain.notification as notificationEnabled',
            'details.shippingtime as shippingTime',
        ])
            ->from('s_articles_details', 'details')
            ->innerJoin('details', 's_articles', 'productMain', 'productMain.id = details.articleID')
            ->where('productMain.id IN (:productIds)')
            ->setParameter(':productIds', $productIds, Connection::PARAM_INT_ARRAY)
            ->execute()
            ->fetchAll(\PDO::FETCH_GROUP | \PDO::FETCH_UNIQUE | \PDO::FETCH_ASSOC);
    }

    /**
     * @return array
     */
    private function getVariantsForProducts(array $productIds)
    {
        $queryBuilder = $this->dbalConnection->createQueryBuilder();

        return $queryBuilder->select([
            'details.articleID',
            'details.active',
            'details.instock',
            'details.stockmin as instockMinimum',
            'details.lastStock as sale',
            'details.minpurchase as minPurchase',
            'details.maxpurchase as maxPurchase',
            'details.purchasesteps as purchaseSteps',
            'details.instock > 0 as shippingReady',
            'details.shippingfree as shippingFree',
            'details.shippingtime as shippingTime',
        ])
            ->from('s_articles_details', 'details')
            ->where('details.articleID IN (:productIds)')
            ->andWhere('details.kind = 2')
            ->setParameter(':productIds', $productIds, Connection::PARAM_INT_ARRAY)
            ->execute()
            ->fetchAll(\PDO::FETCH_GROUP);
    }

    /**
     * @return array
     */
    private function getImagesPerProduct(array $productIds)
    {
        $productMedias = [];

        $queryBuilder = $this->dbalConnection->createQueryBuilder();

        $mediaIdArray = $queryBuilder->select('image.articleID, GROUP_CONCAT(image.media_id)')
            ->from('s_articles_img', 'image')
            ->where('image.articleID IN (:productIds)')
            ->groupBy('image.articleID')
            ->setParameter(':productIds', $productIds, Connection::PARAM_INT_ARRAY)
            ->execute()
            ->fetchAll(\PDO::FETCH_KEY_PAIR);

        foreach ($mediaIdArray as $productId => $mediaIds) {
            $mediaQueryBuilder = $this->dbalConnection->createQueryBuilder();
            $medias = $mediaQueryBuilder->select('media.width, media.height, media.extension, media.file_size as fileSize')
                ->from('s_media', 'media')
                ->where('media.id IN (:mediaIds)')
                ->setParameter(':mediaIds', explode(',', $mediaIds), Connection::PARAM_INT_ARRAY)
                ->execute()
                ->fetchAll();

            $productMedias[$productId] = $medias;
        }

        return $productMedias;
    }

    /**
     * @param int $batch
     * @param int $lastProductId
     *
     * @return array
     */
    private function getProductIds($batch, $lastProductId)
    {
        $categoryIds = $this->getPossibleCategoryIds();

        $queryBuilder = $this->dbalConnection->createQueryBuilder();

        return $queryBuilder->select('DISTINCT productCat.articleID')
            ->from('s_articles_categories', 'productCat')
            ->where('productCat.categoryID IN (:categoryIds)')
            ->andWhere('productCat.articleID > :lastProductId')
            ->setMaxResults($batch)
            ->setParameter(':categoryIds', $categoryIds, Connection::PARAM_INT_ARRAY)
            ->setParameter(':lastProductId', $lastProductId)
            ->execute()
            ->fetchAll(\PDO::FETCH_COLUMN);
    }

    /**
     * @return array
     */
    private function getPossibleCategoryIds()
    {
        $categoryId = $this->shopContext->getShop()->getCategory()->getId();

        $queryBuilder = $this->dbalConnection->createQueryBuilder();

        return $queryBuilder->select('category.id')
            ->from('s_categories', 'category')
            ->where('category.path LIKE :categoryIdPath')
            ->orWhere('category.id = :categoryId')
            ->setParameter(':categoryId', $categoryId)
            ->setParameter(':categoryIdPath', '%|' . $categoryId . '|%')
            ->execute()
            ->fetchAll(\PDO::FETCH_COLUMN);
    }

    /**
     * @return array
     */
    private function getConfig()
    {
        $configsQueryBuilder = $this->dbalConnection->createQueryBuilder();

        return $configsQueryBuilder->select('configs.*')
            ->from('s_benchmark_config', 'configs')
            ->where('configs.shop_id = :shopId')
            ->setParameter(':shopId', $this->shopContext->getShop()->getId())
            ->execute()
            ->fetch();
    }
}
