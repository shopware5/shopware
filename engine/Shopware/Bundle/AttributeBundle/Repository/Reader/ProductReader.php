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

namespace Shopware\Bundle\AttributeBundle\Repository\Reader;

use Doctrine\DBAL\Connection;
use Shopware\Bundle\StoreFrontBundle\Service\AdditionalTextServiceInterface;
use Shopware\Bundle\StoreFrontBundle\Service\ContextServiceInterface;
use Shopware\Bundle\StoreFrontBundle\Service\Core\ContextService;
use Shopware\Bundle\StoreFrontBundle\Struct\ListProduct;
use Shopware\Components\Model\ModelManager;
use Shopware\Models\Article\Detail;
use Shopware\Models\Shop\Repository;
use Shopware\Models\Shop\Shop;

class ProductReader extends GenericReader
{
    /**
     * @var ContextServiceInterface
     */
    private $contextService;

    /**
     * @var AdditionalTextServiceInterface
     */
    private $additionalTextService;

    /**
     * @param string $entity
     */
    public function __construct(
        $entity,
        ModelManager $entityManager,
        ContextServiceInterface $contextService,
        AdditionalTextServiceInterface $additionalTextService
    ) {
        parent::__construct($entity, $entityManager);
        $this->contextService = $contextService;
        $this->additionalTextService = $additionalTextService;
    }

    /**
     * @param int[]|string[] $identifiers
     *
     * @return array[]
     */
    public function getList($identifiers)
    {
        $products = parent::getList($identifiers);
        $products = $this->assignAdditionalText($products);
        $products = $this->assignCategoryIds($products);
        $products = $this->assignPrice($products);

        return $products;
    }

    /**
     * {@inheritdoc}
     */
    protected function createListQuery()
    {
        $query = $this->entityManager->createQueryBuilder();
        $query->select([
            'variant.id as id',
            'variant.id as variantId',
            'article.id as articleId',
            'article.name',
            'article.taxId',
            'article.description',
            'variant.number',
            'variant.kind',
            'variant.inStock',
            'variant.ean',
            'variant.supplierNumber',
            'variant.additionalText',
            'article.active as articleActive',
            'variant.active as variantActive',
            'supplier.id as supplierId',
            'supplier.name as supplierName',
        ]);
        $query->from(Detail::class, 'variant', $this->getIdentifierField());
        $query->leftJoin('variant.article', 'article');
        $query->leftJoin('article.supplier', 'supplier');

        return $query;
    }

    /**
     * {@inheritdoc}
     */
    protected function getIdentifierField()
    {
        return 'variant.number';
    }

    /**
     * @param array[] $products
     *
     * @return array[]
     */
    private function assignAdditionalText(array $products)
    {
        /** @var Repository $shopRepo */
        $shopRepo = $this->entityManager->getRepository(Shop::class);

        /** @var Shop $shop */
        $shop = $shopRepo->getActiveDefault();

        $context = $this->contextService->createShopContext(
            $shop->getId(),
            $shop->getCurrency()->getId(),
            ContextService::FALLBACK_CUSTOMER_GROUP
        );

        $tempProducts = $this->buildListProducts($products);
        $tempProducts = $this->additionalTextService->buildAdditionalTextLists($tempProducts, $context);

        foreach ($tempProducts as $tempProduct) {
            $number = $tempProduct->getNumber();
            if (!isset($products[$number])) {
                continue;
            }
            $products[$number]['additionalText'] = $tempProduct->getAdditional();
        }

        return $products;
    }

    /**
     * @param array[] $products
     *
     * @return ListProduct[]
     */
    private function buildListProducts(array $products)
    {
        $listProducts = [];
        foreach ($products as $product) {
            $listProduct = new ListProduct($product['articleId'], $product['variantId'], $product['number']);
            $listProduct->setAdditional($product['additionalText']);
            $listProducts[$product['number']] = $listProduct;
        }

        return $listProducts;
    }

    private function assignCategoryIds(array $products)
    {
        $ids = array_column($products, 'articleId');

        $query = $this->entityManager->getConnection()->createQueryBuilder();
        $query->select(['articleID', 'GROUP_CONCAT(categoryID)']);
        $query->from('s_articles_categories_ro');
        $query->where('articleID IN (:ids)');
        $query->groupBy('articleID');
        $query->setParameter('ids', $ids, Connection::PARAM_INT_ARRAY);

        $categories = $query->execute()->fetchAll(\PDO::FETCH_KEY_PAIR);

        foreach ($products as &$product) {
            $mapping = [];
            $id = $product['articleId'];
            if (array_key_exists($id, $categories)) {
                $mapping = array_values(array_filter(explode(',', $categories[$id])));
            }
            $product['categoryIds'] = $mapping;
        }

        return $products;
    }

    private function assignPrice(array $products)
    {
        $ids = array_column($products, 'articleId');
        $variantIds = array_column($products, 'variantId');

        $query = $this->entityManager->getConnection()->createQueryBuilder();
        $query->select(['articledetailsID', 'price']);
        $query->from('s_articles_prices');
        $query->where('articleID IN (:ids)');
        $query->andWhere('articledetailsID IN (:variantIds)');
        $query->andWhere('`from` = 1');
        $query->setParameter('ids', $ids, Connection::PARAM_INT_ARRAY);
        $query->setParameter('variantIds', $variantIds, Connection::PARAM_INT_ARRAY);

        $prices = $query->execute()->fetchAll(\PDO::FETCH_KEY_PAIR);

        foreach ($products as &$product) {
            $id = $product['variantId'];
            if (array_key_exists($id, $prices)) {
                $product['price'] = $prices[$id];
            }
        }

        return $products;
    }
}
