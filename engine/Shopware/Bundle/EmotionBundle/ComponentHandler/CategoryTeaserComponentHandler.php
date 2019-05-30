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

use Doctrine\DBAL\Connection;
use Shopware\Bundle\EmotionBundle\Struct\Collection\PrepareDataCollection;
use Shopware\Bundle\EmotionBundle\Struct\Collection\ResolvedDataCollection;
use Shopware\Bundle\EmotionBundle\Struct\Element;
use Shopware\Bundle\SearchBundle\Sorting\ReleaseDateSorting;
use Shopware\Bundle\SearchBundle\SortingInterface;
use Shopware\Bundle\SearchBundle\StoreFrontCriteriaFactoryInterface;
use Shopware\Bundle\StoreFrontBundle\Service\BlogServiceInterface;
use Shopware\Bundle\StoreFrontBundle\Service\CategoryServiceInterface;
use Shopware\Bundle\StoreFrontBundle\Struct\Blog\Blog;
use Shopware\Bundle\StoreFrontBundle\Struct\ListProduct;
use Shopware\Bundle\StoreFrontBundle\Struct\ShopContext;
use Shopware\Bundle\StoreFrontBundle\Struct\ShopContextInterface;

class CategoryTeaserComponentHandler implements ComponentHandlerInterface
{
    const TYPE_IMAGE = 'selected_image';
    const TYPE_ARTICLE_OR_BLOG = 'random_article_image';

    const LEGACY_CONVERT_FUNCTION = 'getCategoryTeaser';
    const COMPONENT_NAME = 'emotion-components-category-teaser';

    /**
     * @var StoreFrontCriteriaFactoryInterface
     */
    private $criteriaFactory;

    /**
     * @var CategoryServiceInterface
     */
    private $categoryService;

    /**
     * @var BlogServiceInterface
     */
    private $blogService;

    /**
     * @var Connection
     */
    private $connection;

    public function __construct(StoreFrontCriteriaFactoryInterface $criteriaFactory, CategoryServiceInterface $categoryService, Connection $connection, BlogServiceInterface $blogService)
    {
        $this->criteriaFactory = $criteriaFactory;
        $this->categoryService = $categoryService;
        $this->connection = $connection;
        $this->blogService = $blogService;
    }

    /**
     * @return bool
     */
    public function supports(Element $element)
    {
        return $element->getComponent()->getType() === self::COMPONENT_NAME
            || $element->getComponent()->getConvertFunction() === self::LEGACY_CONVERT_FUNCTION;
    }

    /**
     * @param ShopContext|ShopContextInterface $context
     */
    public function prepare(PrepareDataCollection $collection, Element $element, ShopContextInterface $context)
    {
        $imageType = $element->getConfig()->get('image_type');
        $key = 'emotion-element--' . $element->getId();

        switch ($imageType) {
            case self::TYPE_IMAGE:
                if (!empty($element->getConfig()->get('image'))) {
                    $collection->addMediaPaths([$element->getConfig()->get('image')]);
                }

                break;

            case self::TYPE_ARTICLE_OR_BLOG:
                $isBlog = (bool) $element->getConfig()->get('blog_category');
                $categoryId = (int) $element->getConfig()->get('category_selection');

                if ($isBlog) {
                    break;
                }

                $criteria = $this->criteriaFactory->createBaseCriteria([$categoryId], $context);
                $criteria->addSorting(new ReleaseDateSorting(SortingInterface::SORT_DESC));
                $criteria->limit(50);

                $collection->getBatchRequest()->setCriteria($key, $criteria);
                break;
        }
    }

    public function handle(ResolvedDataCollection $collection, Element $element, ShopContextInterface $context)
    {
        $imageType = $element->getConfig()->get('image_type');
        $key = 'emotion-element--' . $element->getId();

        switch ($imageType) {
            case self::TYPE_IMAGE:
                $media = $collection->getMediaByPath($element->getConfig()->get('image'));
                if (!$media) {
                    break;
                }

                $element->getData()->set('media', $media);
                break;

            case self::TYPE_ARTICLE_OR_BLOG:
                $isBlog = (bool) $element->getConfig()->get('blog_category');
                $categoryId = (int) $element->getConfig()->get('category_selection');

                if ($isBlog) {
                    $blog = $this->getRandomBlog($categoryId, $context);
                    if (!$blog) {
                        break;
                    }
                    $medias = $blog->getMedias();
                    $media = array_shift($medias);

                    $element->getData()->set('blog', $blog);
                    $element->getData()->set('image', $media);
                    $element->getData()->set('images', $media->getThumbnails());
                    break;
                }

                $products = $collection->getBatchResult()->get($key);
                shuffle($products);

                /** @var ListProduct|null $product */
                $product = reset($products);

                if (!$product || !$product->getCover()) {
                    break;
                }

                $element->getData()->set('image', $product->getCover());
                $element->getData()->set('images', $product->getCover()->getThumbnails());
                break;
        }

        $this->fetchCategory($element, $context);
    }

    private function fetchCategory(Element $element, ShopContextInterface $context)
    {
        $categoryId = (int) $element->getConfig()->get('category_selection');
        $category = $this->categoryService->getList([$categoryId], $context);

        if (!$category) {
            return;
        }

        $element->getData()->set('category', reset($category));
    }

    /**
     * @param int $categoryId
     *
     * @return Blog|null
     */
    private function getRandomBlog($categoryId, ShopContextInterface $context)
    {
        $blogId = $this->findBlogIdByCategoryId($categoryId);
        $blog = $this->blogService->getList([$blogId], $context);

        return array_shift($blog);
    }

    /**
     * @param int $categoryId
     *
     * @return int
     */
    private function findBlogIdByCategoryId($categoryId)
    {
        $builder = $this->connection->createQueryBuilder();
        $builder->select('blog.id')
            ->from('s_blog', 'blog')
            ->leftJoin('blog', 's_categories', 'category', 'blog.category_id = category.id')
            ->where('blog.active = 1')
            ->andWhere('blog.display_date <= :displayDate')
            ->andWhere('(category.path LIKE :path OR category.id = :categoryId)')
            ->orderBy('blog.display_date', 'DESC')
            ->setMaxResults(50)
            ->setParameter('displayDate', date('Y-m-d H:i:s'))
            ->setParameter('categoryId', $categoryId)
            ->setParameter('path', '%|' . $categoryId . '|%');

        $blogIds = $builder->execute()->fetchAll(\PDO::FETCH_COLUMN);
        shuffle($blogIds);

        return (int) reset($blogIds);
    }
}
