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

namespace Shopware\Bundle\EmotionBundle\ComponentHandler;

use Shopware\Bundle\EmotionBundle\Struct\Collection\PrepareDataCollection;
use Shopware\Bundle\EmotionBundle\Struct\Collection\ResolvedDataCollection;
use Shopware\Bundle\EmotionBundle\Struct\Element;
use Shopware\Bundle\SearchBundle\Sorting\PopularitySorting;
use Shopware\Bundle\SearchBundle\Sorting\PriceSorting;
use Shopware\Bundle\SearchBundle\Sorting\RandomSorting;
use Shopware\Bundle\SearchBundle\Sorting\ReleaseDateSorting;
use Shopware\Bundle\SearchBundle\SortingInterface;
use Shopware\Bundle\SearchBundle\StoreFrontCriteriaFactoryInterface;
use Shopware\Bundle\StoreFrontBundle\Service\AdditionalTextServiceInterface;
use Shopware\Bundle\StoreFrontBundle\Struct\ListProduct;
use Shopware\Bundle\StoreFrontBundle\Struct\ShopContextInterface;
use Shopware\Components\ProductStream\RepositoryInterface;
use Shopware_Components_Config as ShopwareConfig;

class ArticleSliderComponentHandler implements ComponentHandlerInterface
{
    const TYPE_PRODUCT_STREAM = 'product_stream';
    const TYPE_STATIC_PRODUCT = 'selected_article';
    const TYPE_STATIC_VARIANT = 'selected_variant';
    const TYPE_RANDOM = 'random_product';
    const TYPE_NEWCOMER = 'newcomer';
    const TYPE_TOPSELLER = 'topseller';
    const TYPE_LOWEST_PRICE = 'price_asc';
    const TYPE_HIGHEST_PRICE = 'price_desc';

    const LEGACY_CONVERT_FUNCTION = 'getArticleSlider';
    const COMPONENT_NAME = 'emotion-components-article-slider';

    /**
     * @var StoreFrontCriteriaFactoryInterface
     */
    private $criteriaFactory;

    /**
     * @var RepositoryInterface
     */
    private $productStreamRepository;

    /**
     * @var ShopwareConfig
     */
    private $shopwareConfig;

    /**
     * @var AdditionalTextServiceInterface
     */
    private $additionalTextService;

    public function __construct(
        StoreFrontCriteriaFactoryInterface $criteriaFactory,
        RepositoryInterface $productStreamRepository,
        ShopwareConfig $shopwareConfig,
        AdditionalTextServiceInterface $additionalTextService
    ) {
        $this->criteriaFactory = $criteriaFactory;
        $this->productStreamRepository = $productStreamRepository;
        $this->shopwareConfig = $shopwareConfig;
        $this->additionalTextService = $additionalTextService;
    }

    /**
     * {@inheritdoc}
     */
    public function supports(Element $element)
    {
        $component = $element->getComponent();

        return $component->getType() === self::COMPONENT_NAME
            || $component->getConvertFunction() === self::LEGACY_CONVERT_FUNCTION;
    }

    /**
     * {@inheritdoc}
     */
    public function prepare(PrepareDataCollection $collection, Element $element, ShopContextInterface $context)
    {
        $type = $element->getConfig()->get('article_slider_type', self::TYPE_STATIC_PRODUCT);
        $key = 'emotion-element--' . $element->getId();

        switch ($type) {
            case self::TYPE_PRODUCT_STREAM:
                $criteria = $this->generateCriteria($element, $context);

                $productStreamId = $element->getConfig()->get('article_slider_stream');
                $this->productStreamRepository->prepareCriteria($criteria, $productStreamId);

                // request multiple products by criteria
                $collection->getBatchRequest()->setCriteria($key, $criteria);
                break;

            case self::TYPE_TOPSELLER:
            case self::TYPE_NEWCOMER:
            case self::TYPE_RANDOM:
            case self::TYPE_LOWEST_PRICE:
            case self::TYPE_HIGHEST_PRICE:
                $criteria = $this->generateCriteria($element, $context);

                // request multiple products by criteria
                $collection->getBatchRequest()->setCriteria($key, $criteria);
                break;

            case self::TYPE_STATIC_PRODUCT:
                $products = $element->getConfig()->get('selected_articles', []);
                $productNumbers = array_filter(explode('|', $products));
                if (empty($productNumbers)) {
                    $productNumbers = [];
                }

                $collection->getBatchRequest()->setProductNumbers($key, $productNumbers);
                break;
            case self::TYPE_STATIC_VARIANT:
                $productVariants = $element->getConfig()->get('selected_variants', []);
                $productNumbers = array_filter(explode('|', $productVariants));
                if (empty($productNumbers)) {
                    $productNumbers = [];
                }

                $collection->getBatchRequest()->setProductNumbers($key, $productNumbers);
                break;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function handle(ResolvedDataCollection $collection, Element $element, ShopContextInterface $context)
    {
        $type = $element->getConfig()->get('article_slider_type', self::TYPE_STATIC_PRODUCT);
        $key = 'emotion-element--' . $element->getId();

        switch ($type) {
            case self::TYPE_PRODUCT_STREAM:
            case self::TYPE_NEWCOMER:
            case self::TYPE_RANDOM:
            case self::TYPE_TOPSELLER:
            case self::TYPE_HIGHEST_PRICE:
            case self::TYPE_LOWEST_PRICE:
                $requestedProducts = $collection->getBatchResult()->get($key);
                $element->getData()->set('products', $requestedProducts);
                break;

            case self::TYPE_STATIC_PRODUCT:
                $products = $element->getConfig()->get('selected_articles', []);
                $productNumbers = array_filter(explode('|', $products));
                $listProducts = $collection->getBatchResult()->get($key);

                $products = [];
                foreach ($productNumbers as $productNumber) {
                    if (!$listProducts[$productNumber]) {
                        continue;
                    }
                    $products[$productNumber] = $listProducts[$productNumber];
                }

                $element->getData()->set('products', $products);
                break;
            case self::TYPE_STATIC_VARIANT:
                $products = $element->getConfig()->get('selected_variants', []);
                $productNumbers = array_filter(explode('|', $products));
                $listProducts = $collection->getBatchResult()->get($key);
                $listProducts = $this->additionalTextService->buildAdditionalTextLists($listProducts, $context);

                $products = [];
                foreach ($productNumbers as $productNumber) {
                    /** @var ListProduct|null $product */
                    $product = $listProducts[$productNumber];
                    if (!$product) {
                        continue;
                    }
                    $this->switchPrice($product);
                    $products[$productNumber] = $product;
                }

                $element->getData()->set('products', $products);
                break;
        }
    }

    private function switchPrice(ListProduct $product)
    {
        $prices = array_values($product->getPrices());
        $product->setListingPrice($prices[0]);

        $product->setDisplayFromPrice(count($product->getPrices()) > 1);

        if ($this->shopwareConfig->get('useLastGraduationForCheapestPrice')) {
            $product->setListingPrice(
                $prices[count($prices) - 1]
            );
        }
    }

    /**
     * @return \Shopware\Bundle\SearchBundle\Criteria
     */
    private function generateCriteria(Element $element, ShopContextInterface $context)
    {
        $type = $element->getConfig()->get('article_slider_type');
        $limit = (int) $element->getConfig()->get('article_slider_max_number');
        $categoryId = (int) $element->getConfig()->get('article_slider_category');

        if ($type === self::TYPE_PRODUCT_STREAM) {
            $categoryId = $context->getShop()->getCategory()->getId();
        }

        $criteria = $this->criteriaFactory->createBaseCriteria([$categoryId], $context);
        $criteria->limit($limit);

        switch ($type) {
            case self::TYPE_LOWEST_PRICE:
                $criteria->addSorting(new PriceSorting(SortingInterface::SORT_ASC));
                break;
            case self::TYPE_HIGHEST_PRICE:
                $criteria->addSorting(new PriceSorting(SortingInterface::SORT_DESC));
                break;
            case self::TYPE_TOPSELLER:
                $criteria->addSorting(new PopularitySorting(SortingInterface::SORT_DESC));
                break;
            case self::TYPE_NEWCOMER:
                $criteria->addSorting(new ReleaseDateSorting(SortingInterface::SORT_DESC));
                break;
            case self::TYPE_RANDOM:
                $criteria->addSorting(new RandomSorting());
                break;
        }

        return $criteria;
    }
}
