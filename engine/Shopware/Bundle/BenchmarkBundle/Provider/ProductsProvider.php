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
use Shopware\Bundle\BenchmarkBundle\BenchmarkProviderInterface;

class ProductsProvider implements BenchmarkProviderInterface
{
    /**
     * @var Connection
     */
    private $dbalConnection;

    public function __construct(Connection $dbalConnection)
    {
        $this->dbalConnection = $dbalConnection;
    }

    public function getName()
    {
        return 'products';
    }

    public function getBenchmarkData()
    {
        return [
            'total' => $this->getProductsTotal(),
            'variants' => $this->getVariantsInformation(),
            'images' => $this->getProductImages(),
        ];
    }

    /**
     * @return int
     */
    private function getProductsTotal()
    {
        $queryBuilder = $this->dbalConnection->createQueryBuilder();

        return (int) $queryBuilder->select('COUNT(products.id)')
            ->from('s_articles', 'products')
            ->execute()
            ->fetchColumn();
    }

    /**
     * @return array
     */
    private function getVariantsInformation()
    {
        $countsQueryBuilder = $this->dbalConnection->createQueryBuilder();
        $resultQueryBuilder = $this->dbalConnection->createQueryBuilder();

        // Fetch count of variants per product ID
        $countsQueryBuilder->select('COUNT(details.ordernumber) as variantCount')
            ->from('s_articles_details', 'details')
            ->groupBy('details.articleID');

        $result = $resultQueryBuilder->select('AVG(variantCounts.variantCount) as average, MAX(variantCounts.variantCount) as max')
            ->from('(' . $countsQueryBuilder->getSQL() . ')', 'variantCounts')
            ->execute()
            ->fetch();

        $result['average'] = (float) $result['average'];
        $result['max'] = (int) $result['max'];

        return $result;
    }

    /**
     * @return array
     */
    private function getProductImages()
    {
        return [
            'sizes' => $this->getImageSizes(),
            'average' => $this->getAverageImages(),
            'missing' => $this->getMissingImagesCount(),
        ];
    }

    /**
     * @return array
     */
    private function getImageSizes()
    {
        $queryBuilder = $this->dbalConnection->createQueryBuilder();
        $albumSizeData = $queryBuilder->select("GROUP_CONCAT(album.thumbnail_size SEPARATOR ';')")
            ->from('s_media_album_settings', 'album')
            ->where("album.thumbnail_size != ''")
            ->execute()
            ->fetchColumn();

        $albumSizeData = explode(';', $albumSizeData);

        return array_keys(array_flip($albumSizeData));
    }

    /**
     * @return float
     */
    private function getAverageImages()
    {
        $productsWithImagesCountQb = $this->dbalConnection->createQueryBuilder();
        $productsTotalCountQb = $this->dbalConnection->createQueryBuilder();

        $productsTotalCount = (int) $productsTotalCountQb->select('COUNT(article.id)')
            ->from('s_articles', 'article')
            ->execute()
            ->fetchColumn();

        $productImageCount = (int) $productsWithImagesCountQb->select('COUNT(img.articleID) as articleCount')
            ->from('s_articles_img', 'img')
            ->where('img.articleID IS NOT NULL')
            ->execute()
            ->fetchColumn();

        return (float) $productImageCount / $productsTotalCount;
    }

    /**
     * @return int
     */
    private function getMissingImagesCount()
    {
        $productsWithImagesCountQb = $this->dbalConnection->createQueryBuilder();
        $productsTotalCountQb = $this->dbalConnection->createQueryBuilder();

        $productsWithImagesCount = (int) $productsWithImagesCountQb->select('COUNT(DISTINCT img.articleID) as articleCount')
            ->from('s_articles_img', 'img')
            ->where('img.articleID IS NOT NULL')
            ->execute()
            ->fetchColumn();

        $productsTotalCount = (int) $productsTotalCountQb->select('COUNT(article.id)')
            ->from('s_articles', 'article')
            ->execute()
            ->fetchColumn();

        return $productsTotalCount - $productsWithImagesCount;
    }
}
