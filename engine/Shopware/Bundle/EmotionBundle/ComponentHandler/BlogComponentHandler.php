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
use Shopware\Bundle\StoreFrontBundle\Service\BlogServiceInterface;
use Shopware\Bundle\StoreFrontBundle\Struct\Blog\Blog;
use Shopware\Bundle\StoreFrontBundle\Struct\ShopContext;
use Shopware\Bundle\StoreFrontBundle\Struct\ShopContextInterface;

class BlogComponentHandler implements ComponentHandlerInterface
{
    const COMPONENT_NAME = 'emotion-components-blog';

    /**
     * @var BlogServiceInterface
     */
    private $blogService;

    /**
     * @var Connection
     */
    private $connection;

    public function __construct(BlogServiceInterface $blogService, Connection $connection)
    {
        $this->blogService = $blogService;
        $this->connection = $connection;
    }

    /**
     * @return bool
     */
    public function supports(Element $element)
    {
        return $element->getComponent()->getType() === self::COMPONENT_NAME;
    }

    /**
     * @param ShopContext|ShopContextInterface $context
     */
    public function prepare(PrepareDataCollection $collection, Element $element, ShopContextInterface $context)
    {
    }

    public function handle(ResolvedDataCollection $collection, Element $element, ShopContextInterface $context)
    {
        $numberOfEntries = $element->getConfig()->get('entry_amount');
        $categoryId = $element->getConfig()->get('blog_entry_selection');

        $blogEntries = $this->getRandomBlogEntries($numberOfEntries, $categoryId, $context);

        $element->getData()->set('entries', $blogEntries);
    }

    /**
     * @param int $numberOfEntries
     * @param int $categoryId
     *
     * @return Blog[]
     */
    private function getRandomBlogEntries($numberOfEntries, $categoryId, ShopContextInterface $context)
    {
        $blogIds = $this->findBlogIds($numberOfEntries, $categoryId, (int) $context->getShop()->getId());

        return $this->blogService->getList($blogIds, $context);
    }

    private function findBlogIds(int $numberOfEntries, int $categoryId, int $shopId)
    {
        $builder = $this->connection->createQueryBuilder();
        $builder->select('blog.id')
                ->from('s_blog', 'blog')
                ->leftJoin('blog', 's_categories', 'category', 'blog.category_id = category.id')
                ->where('blog.active = 1')
                ->andWhere('blog.display_date <= :displayDate')
                ->andWhere('(category.path LIKE :path OR category.id = :categoryId)')
                ->andWhere('(blog.shop_ids LIKE :shopId OR blog.shop_ids IS NULL)')
                ->orderBy('blog.display_date', 'DESC')
                ->setMaxResults($numberOfEntries)
                ->setParameter('displayDate', date('Y-m-d H:i:s'))
                ->setParameter('categoryId', $categoryId)
                ->setParameter('shopId', '%|' . $shopId . '|%')
                ->setParameter('path', '%|' . $categoryId . '|%');

        return $builder->execute()->fetchAll(\PDO::FETCH_COLUMN);
    }
}
