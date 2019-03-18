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

namespace Shopware\Bundle\SearchBundleES\ConditionHandler;

use Doctrine\DBAL\Connection;
use ONGR\ElasticsearchDSL\Query\Compound\BoolQuery;
use ONGR\ElasticsearchDSL\Query\FullText\MultiMatchQuery;
use ONGR\ElasticsearchDSL\Query\TermLevel\TermQuery;
use ONGR\ElasticsearchDSL\Query\TermLevel\TermsQuery;
use ONGR\ElasticsearchDSL\Search;
use Shopware\Bundle\SearchBundle\Condition\SimilarProductCondition;
use Shopware\Bundle\SearchBundle\Criteria;
use Shopware\Bundle\SearchBundle\CriteriaPartInterface;
use Shopware\Bundle\SearchBundleES\PartialConditionHandlerInterface;
use Shopware\Bundle\StoreFrontBundle\Struct\ShopContextInterface;

class SimilarProductConditionHandler implements PartialConditionHandlerInterface
{
    /**
     * @var Connection
     */
    protected $connection;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    /**
     * {@inheritdoc}
     */
    public function supports(CriteriaPartInterface $criteriaPart)
    {
        return $criteriaPart instanceof SimilarProductCondition;
    }

    /**
     * {@inheritdoc}
     */
    public function handleFilter(
        CriteriaPartInterface $criteriaPart,
        Criteria $criteria,
        Search $search,
        ShopContextInterface $context
    ) {
        /** @var SimilarProductCondition $criteriaPart */
        $productId = $criteriaPart->getProductId();
        $productName = $criteriaPart->getProductName();
        $categories = $this->getProductCategories($productId);

        $query = new BoolQuery();

        $nameQuery = new MultiMatchQuery(['name', 'keywords'], $productName, ['boost' => 5]);
        $categoriesQuery = new TermsQuery('categoryIds', $categories, ['boost' => 0.2]);

        $query->add($nameQuery, BoolQuery::SHOULD);
        $query->add($categoriesQuery, BoolQuery::MUST);

        $not = new BoolQuery();
        $not->add(new TermQuery('id', $productId), BoolQuery::MUST_NOT);

        $search->addQuery($not, BoolQuery::FILTER);
        $search->addQuery($query);
    }

    /**
     * {@inheritdoc}
     */
    public function handlePostFilter(
        CriteriaPartInterface $criteriaPart,
        Criteria $criteria,
        Search $search,
        ShopContextInterface $context
    ) {
        $this->handleFilter($criteriaPart, $criteria, $search, $context);
    }

    /**
     * @param int $productId
     *
     * @return int[]
     */
    private function getProductCategories($productId)
    {
        $query = $this->connection->createQueryBuilder();

        return $query->select('categoryID')
            ->from('s_articles_categories', 'category')
            ->where('articleID = :productId')
            ->setParameter(':productId', $productId)
            ->execute()
            ->fetchAll(\PDO::FETCH_COLUMN);
    }
}
