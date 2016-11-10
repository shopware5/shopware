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
use Shopware\Bundle\EmotionBundle\Struct\Library\Component;
use Shopware\Bundle\SearchBundle\Sorting\PopularitySorting;
use Shopware\Bundle\SearchBundle\Sorting\ReleaseDateSorting;
use Shopware\Bundle\SearchBundle\SortingInterface;
use Shopware\Bundle\SearchBundle\StoreFrontCriteriaFactoryInterface;
use Shopware\Bundle\StoreFrontBundle\Struct\ShopContext;
use Shopware\Bundle\StoreFrontBundle\Struct\ShopContextInterface;

class ArticleComponentHandler implements ComponentHandlerInterface
{
    const TYPE_RANDOM = 'random_article';
    const TYPE_STATIC = 'selected_article';
    const TYPE_NEWCOMER = 'newcomer';
    const TYPE_TOPSELLER = 'topseller';

    const LEGACY_CONVERT_FUNCTION = 'getArticle';
    const COMPONENT_NAME = 'emotion-components-article';

    /**
     * @var StoreFrontCriteriaFactoryInterface
     */
    private $criteriaFactory;

    /**
     * ArticleComponentHandler constructor.
     * @param StoreFrontCriteriaFactoryInterface $criteriaFactory
     */
    public function __construct(StoreFrontCriteriaFactoryInterface $criteriaFactory)
    {
        $this->criteriaFactory = $criteriaFactory;
    }

    /**
     * @param Element $element
     * @return bool
     */
    public function supports(Element $element)
    {
        return $element->getComponent()->getType() === self::COMPONENT_NAME
            || $element->getComponent()->getConvertFunction() === self::LEGACY_CONVERT_FUNCTION;
    }

    /**
     * @param PrepareDataCollection $collection
     * @param Element $element
     * @param ShopContext|ShopContextInterface $context
     */
    public function prepare(PrepareDataCollection $collection, Element $element, ShopContextInterface $context)
    {
        $type = $element->getConfig()->get('article_type');
        $key = 'emotion-element--' . $element->getId();

        if ($type === self::TYPE_STATIC) {
            // request a single product
            $collection->getBatchRequest()->setProductNumbers($key, [$element->getConfig()->get('article')]);
            return;
        }

        $criteria = $this->generateCriteria($element, $context);

        // request multiple products by criteria
        $collection->getBatchRequest()->setCriteria($key, $criteria);
    }

    /**
     * @param ResolvedDataCollection $collection
     * @param Element $element
     * @param ShopContextInterface $context
     */
    public function handle(ResolvedDataCollection $collection, Element $element, ShopContextInterface $context)
    {
        $key = 'emotion-element--' . $element->getId();

        $product = current($collection->getBatchResult()->get($key));
        $element->getData()->set('product', $product);
    }

    /**
     * @param Element $element
     * @param ShopContextInterface $context
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
        }

        return $criteria;
    }
}
