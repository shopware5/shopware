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
use Shopware\Bundle\SearchBundle\Sorting\RandomSorting;
use Shopware\Bundle\SearchBundle\Sorting\ReleaseDateSorting;
use Shopware\Bundle\SearchBundle\SortingInterface;
use Shopware\Bundle\SearchBundle\StoreFrontCriteriaFactoryInterface;
use Shopware\Bundle\StoreFrontBundle\Service\AdditionalTextServiceInterface;
use Shopware\Bundle\StoreFrontBundle\Struct\ListProduct;
use Shopware\Bundle\StoreFrontBundle\Struct\ShopContextInterface;
use Shopware_Components_Config as ShopwareConfig;

class ArticleComponentHandler implements ComponentHandlerInterface
{
    const TYPE_STATIC_PRODUCT = 'selected_article';
    const TYPE_STATIC_VARIANT = 'selected_variant';
    const TYPE_RANDOM = 'random_article';
    const TYPE_NEWCOMER = 'newcomer';
    const TYPE_TOPSELLER = 'topseller';

    const LEGACY_CONVERT_FUNCTION = 'getArticle';
    const COMPONENT_NAME = 'emotion-components-article';

    /**
     * @var StoreFrontCriteriaFactoryInterface
     */
    private $criteriaFactory;

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
        ShopwareConfig $shopwareConfig,
        AdditionalTextServiceInterface $additionalTextService
    ) {
        $this->criteriaFactory = $criteriaFactory;

        $this->shopwareConfig = $shopwareConfig;
        $this->additionalTextService = $additionalTextService;
    }

    /**
     * {@inheritdoc}
     */
    public function supports(Element $element)
    {
        return $element->getComponent()->getType() === self::COMPONENT_NAME
            || $element->getComponent()->getConvertFunction() === self::LEGACY_CONVERT_FUNCTION;
    }

    /**
     * {@inheritdoc}
     */
    public function prepare(PrepareDataCollection $collection, Element $element, ShopContextInterface $context)
    {
        $type = $element->getConfig()->get('article_type');
        $key = 'emotion-element--' . $element->getId();

        if ($type === self::TYPE_STATIC_PRODUCT) {
            $collection->getBatchRequest()->setProductNumbers($key, [$element->getConfig()->get('article')]);

            return;
        } elseif ($type === self::TYPE_STATIC_VARIANT) {
            $collection->getBatchRequest()->setProductNumbers($key, [$element->getConfig()->get('variant')]);

            return;
        }

        $criteria = $this->generateCriteria($element, $context);

        // request multiple products by criteria
        $collection->getBatchRequest()->setCriteria($key, $criteria);
    }

    /**
     * {@inheritdoc}
     */
    public function handle(ResolvedDataCollection $collection, Element $element, ShopContextInterface $context)
    {
        $key = 'emotion-element--' . $element->getId();
        $type = $element->getConfig()->get('article_type');

        /** @var ListProduct|false $product */
        $product = current($collection->getBatchResult()->get($key));
        if ($product && $type === self::TYPE_STATIC_VARIANT) {
            $this->additionalTextService->buildAdditionalText($product, $context);
            $this->switchPrice($product);
        }
        $element->getData()->set('product', $product);
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
        $categoryId = (int) $element->getConfig()->get('article_category');
        $type = $element->getConfig()->get('article_type');

        $criteria = $this->criteriaFactory->createBaseCriteria([$categoryId], $context);
        $criteria->limit(1);

        switch ($type) {
            case self::TYPE_TOPSELLER:
                $criteria->addSorting(new PopularitySorting(SortingInterface::SORT_DESC));
                break;
            case self::TYPE_NEWCOMER:
                $criteria->addSorting(new ReleaseDateSorting(SortingInterface::SORT_DESC));
                break;
            case self::TYPE_RANDOM:
                $criteria->addSorting(new RandomSorting());
        }

        return $criteria;
    }
}
