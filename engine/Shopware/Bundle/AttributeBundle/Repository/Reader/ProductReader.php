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

use Shopware\Bundle\StoreFrontBundle\Service\AdditionalTextServiceInterface;
use Shopware\Bundle\StoreFrontBundle\Service\ContextServiceInterface;
use Shopware\Bundle\StoreFrontBundle\Service\Core\ContextService;
use Shopware\Bundle\StoreFrontBundle\Struct\ListProduct;
use Shopware\Components\Model\ModelManager;
use Shopware\Models\Article\Detail;
use Shopware\Models\Shop\Repository;
use Shopware\Models\Shop\Shop;

/**
 * @category  Shopware
 * @package   Shopware\Bundle\AttributeBundle\Repository\Reader
 * @copyright Copyright (c) shopware AG (http://www.shopware.com)
 */
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
     * ProductReader constructor.
     * @param string $entity
     * @param ModelManager $entityManager
     * @param ContextServiceInterface $contextService
     * @param AdditionalTextServiceInterface $additionalTextService
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
     * @return array[]
     */
    public function getList($identifiers)
    {
        $products = parent::getList($identifiers);
        $products = $this->assignAdditionalText($products);
        return $products;
    }

    /**
     * @param array[] $articles
     * @return array[]
     */
    private function assignAdditionalText(array $articles)
    {
        /** @var Repository $shopRepo */
        $shopRepo = $this->entityManager->getRepository('Shopware\Models\Shop\Shop');

        /** @var Shop $shop */
        $shop = $shopRepo->getActiveDefault();

        $context = $this->contextService->createShopContext(
            $shop->getId(),
            $shop->getCurrency()->getId(),
            ContextService::FALLBACK_CUSTOMER_GROUP
        );

        $products = $this->buildListProducts($articles);
        $products = $this->additionalTextService->buildAdditionalTextLists($products, $context);

        foreach ($products as $product) {
            $number = $product->getNumber();
            if (!isset($articles[$number])) {
                continue;
            }
            $articles[$number]['additionalText'] = $product->getAdditional();
        }

        return $articles;
    }

    /**
     * @param array[] $articles
     * @return ListProduct[]
     */
    private function buildListProducts(array $articles)
    {
        $products = [];
        foreach ($articles as $article) {
            $product = new ListProduct($article['articleId'], $article['variantId'], $article['number']);
            $product->setAdditional($article['additionalText']);
            $products[$article['number']] = $product;
        }
        return $products;
    }

    /**
     * @inheritdoc
     */
    protected function createListQuery()
    {
        $query = $this->entityManager->createQueryBuilder();
        $query->select([
            'variant.id as variantId',
            'article.id as articleId',
            'article.name',
            'variant.number',
            'variant.inStock',
            'variant.additionalText',
            'article.active as articleActive',
            'variant.active as variantActive',
        ]);
        $query->from(Detail::class, 'variant', $this->getIdentifierField());
        $query->innerJoin('variant.article', 'article');
        return $query;
    }

    /**
     * @inheritdoc
     */
    protected function getIdentifierField()
    {
        return 'variant.number';
    }
}
