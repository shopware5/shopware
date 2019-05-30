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

namespace Shopware\Components\HttpCache\UrlProvider;

use Doctrine\DBAL\Query\QueryBuilder;
use Shopware\Components\Routing\Context;

class BlogProvider extends CategoryProvider
{
    const NAME = 'blog';

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return self::NAME;
    }

    /**
     * {@inheritdoc}
     */
    public function getUrls(Context $context, $limit = null, $offset = null)
    {
        $qb = $this->getBaseQuery()
            ->addSelect(['blog.id AS blogID', 'cat.id AS catID'])
            ->setParameter(':shop', $context->getShopId());

        if ($limit !== null && $offset !== null) {
            $qb->setFirstResult($offset)
                ->setMaxResults($limit);
        }

        $result = $qb->execute()->fetchAll();

        if (!count($result)) {
            return [];
        }

        return $this->router->generateList(
            array_map(
                function ($blog) {
                    return ['sViewport' => 'blog', 'sAction' => 'detail', 'sCategory' => $blog['catID'], 'blogArticle' => $blog['blogID']];
                },
                $result
            ),
            $context
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getCount(Context $context)
    {
        return (int) $this->getBaseQuery()
            ->addSelect(['COUNT(DISTINCT blog.id, cat.id)'])
            ->setParameter(':shop', $context->getShopId())
            ->execute()
            ->fetchColumn();
    }

    /**
     * @return QueryBuilder
     */
    protected function getBaseQuery()
    {
        return $this->connection->createQueryBuilder()
            ->from('s_blog', 'blog')
            ->join(
                'blog',
                's_categories',
                'cat',
                sprintf('blog.category_id = cat.id
                        AND blog.category_id IN (%s)', $this->prepareSubQuery()->getSQL()))
            ->where('blog.active = 1');
    }

    /**
     * @return QueryBuilder
     */
    private function prepareSubQuery()
    {
        return parent::getBaseQuery()
            ->addSelect(['cat.id']);
    }
}
