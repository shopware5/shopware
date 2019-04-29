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
use Shopware\Bundle\MediaBundle\MediaServiceInterface;
use Shopware\Bundle\SearchBundle\Condition\CategoryCondition;
use Shopware\Bundle\SearchBundle\Criteria;
use Shopware\Bundle\SearchBundle\Sorting\ManualSorting;
use Shopware\Bundle\SearchBundleDBAL\QueryBuilderFactoryInterface;
use Shopware\Bundle\StoreFrontBundle\Service\ContextServiceInterface;
use Shopware\Bundle\StoreFrontBundle\Struct\Search\CustomSorting;

class ProductLoader implements ProductLoaderInterface
{
    /**
     * @var Connection
     */
    private $connection;

    /**
     * @var MediaServiceInterface
     */
    private $mediaService;

    /**
     * @var ContextServiceInterface
     */
    private $contextService;

    /**
     * @var QueryBuilderFactoryInterface
     */
    private $queryBuilderFactory;

    public function __construct(
        QueryBuilderFactoryInterface $queryBuilderFactory,
        ContextServiceInterface $contextService,
        Connection $connection,
        MediaServiceInterface $mediaService
    ) {
        $this->queryBuilderFactory = $queryBuilderFactory;
        $this->contextService = $contextService;
        $this->connection = $connection;
        $this->mediaService = $mediaService;
    }

    public function load(int $categoryId, ?int $start, ?int $limit, CustomSorting $customSorting): array
    {
        $criteria = new Criteria();
        $criteria->addBaseCondition(new CategoryCondition([$categoryId]));
        $criteria->addSorting(new ManualSorting());
        $criteria->offset($start);
        $criteria->limit($limit);

        foreach ($customSorting->getSortings() as $sorting) {
            $criteria->addSorting($sorting);
        }

        $query = $this->queryBuilderFactory->createQueryWithSorting($criteria, $this->contextService->getShopContext());

        $data = [];
        $query
            ->leftJoin('variant', 's_articles_prices', 'price', 'price.articledetailsID = variant.id
            AND price.from = 1
            AND price.pricegroup = \'EK\'')
            ->leftJoin('product', 's_core_tax', 'tax', 'tax.id =  product.taxID')
            ->leftJoin('product', 's_articles_img', 'img', 'img.articleID = product.id AND img.main = 1')
            ->leftJoin('img', 's_media', 'media', 'media.id = img.media_id')
            ->addSelect('SQL_CALC_FOUND_ROWS product.id as id')
            ->addSelect('product.name')
            ->addSelect('product.active')
            ->addSelect('ROUND(price.price*(100+tax.tax)/100,2) as price')
            ->addSelect('manual_sorting.position as position')
            ->addSelect('CONCAT("media/image/thumbnail/", img.img, "_140x140.", img.extension) as thumbnail')
            ->setParameter('categoryId', $categoryId)
            ->setMaxResults($limit)
            ->setFirstResult($start)
            ->addGroupBy('product.id');

        $data['data'] = $query->execute()->fetchAll();

        $data['data'] = array_map(function ($item) {
            $item['thumbnail'] = $this->mediaService->getUrl($item['thumbnail']);

            return $item;
        }, $data['data']);

        $data['total'] = $query->getConnection()->fetchColumn('SELECT FOUND_ROWS()');

        return $data;
    }
}
