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

class VariantSwitchProvider extends ProductProvider
{
    const NAME = 'variantswitch';

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
            ->addSelect(['details.articleID', 'details.ordernumber', 'details.kind', "GROUP_CONCAT(
                  'group[', opt.group_id, ']=', opt_rel.option_id
                  ORDER BY opt.group_id ASC
                  SEPARATOR '&')
              AS 'link'"])
            ->where(sprintf('article.id IN (%s)', $this->prepareSubQuery()->getSQL()))
            ->groupBy('details.ordernumber')
            ->orderBy('details.ordernumber', 'ASC')
            ->setParameter(':shop', $context->getShopId());

        if ($limit !== null && $offset !== null) {
            $qb->setFirstResult($offset)
                ->setMaxResults($limit);
        }

        $resultArray = $qb->execute()->fetchAll();

        if (!count($resultArray)) {
            return [];
        }

        $variantUrls = $this->router->generateList(
            array_map(
                function ($variant) {
                    $urlElements = ['controller' => 'detail', 'action' => 'index', 'sArticle' => $variant['articleID'], 'template' => 'ajax'];

                    return $urlElements;
                },
                $resultArray
            ),
            $context
        );

        $urls = [];
        foreach ($variantUrls as $key => $url) {
            $urls[] = $url . '&' . $resultArray[$key]['link'];
        }

        return $urls;
    }

    /**
     * {@inheritdoc}
     */
    public function getCount(Context $context)
    {
        return (int) $this->getBaseQuery()
            ->addSelect(['COUNT(DISTINCT details.ordernumber)'])
            ->where(sprintf('article.id IN (%s)', $this->prepareSubQuery()->getSQL()))
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
            ->from('s_article_configurator_option_relations', 'opt_rel')
            ->join('opt_rel', 's_articles_details', 'details', 'opt_rel.article_id = details.id')
            ->join('opt_rel', 's_article_configurator_options', 'opt', 'opt_rel.option_id = opt.id')
            ->join('details', 's_articles', 'article', 'article.id = details.articleID');
    }

    /**
     * @return QueryBuilder
     */
    private function prepareSubQuery()
    {
        return parent::getBaseQuery()
            ->addSelect(['DISTINCT details.articleID'])
            ->andWhere('details.kind = 1')
            ->orderBy('details.articleID', 'ASC');
    }
}
