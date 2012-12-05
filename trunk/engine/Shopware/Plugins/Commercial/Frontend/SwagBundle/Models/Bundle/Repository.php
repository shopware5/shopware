<?php
/**
 * Shopware 4.0
 * Copyright © 2012 shopware AG
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

namespace Shopware\CustomModels\Bundle;
use Shopware\Components\Model\ModelRepository;

/**
 * Shopware Bundle Model
 * Contains the definition of a single shopware article bundle resource.
 *
 * @category Shopware
 * @package Shopware\Plugins\SwagBundle\Models\Bundle
 * @copyright Copyright (c) 2012, shopware AG (http://www.shopware.de)
 */
class Repository extends ModelRepository
{
    /**
     * The getArticleBundlesQuery function is the global interface of the repository to get all bundles
     * for the passed article id.
     * The function calls the internal getArticleBundlesQueryBuilder function which generates the query builder
     * with the different sql paths.
     * The getArticleBundlesQueryBuilder function can be hooked to modify the query easily over the query builder.
     *
     * @param int   $articleId
     * @param array $filter
     * @param array $orderBy
     * @param int   $offset
     * @param int   $limit
     *
     * @return \Doctrine\ORM\Query
     */
    public function getArticleBundlesQuery($articleId, $filter = array(), $orderBy = array(), $offset = null, $limit = null)
    {
        $builder = $this->getArticleBundlesQueryBuilder($articleId, $filter, $orderBy);
        if (!empty($offset)) {
            $builder->setFirstResult($offset);
        }
        if (!empty($limit)) {
            $builder->setMaxResults($limit);
        }
        return $builder->getQuery();
    }

    /**
     * The getArticleBundlesQueryBuilder function is a helper function which creates an query builder object
     * to select all bundles for the passed article id.
     * The function returns a query builder object which contains the different sql path to select all article bundles.
     * This function can be hooked to modify the query object easily over the query builder.
     *
     * @param int   $articleId
     * @param array $filter
     * @param array $orderBy
     *
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function getArticleBundlesQueryBuilder($articleId, $filter = array(), $orderBy = array())
    {
        /**@var $builder \Shopware\Components\Model\QueryBuilder*/
        $builder = $this->createQueryBuilder('bundles')
                        ->where('bundles.articleId = :articleId')
                        ->setParameters(array('articleId' => $articleId));

        if (!empty($filter)) {
            $builder->addFilter($filter);
        }

        if (!empty($orderBy)) {
            $builder->addOrderBy($orderBy);
        }

        return $builder;
    }

    /**
     * The getBundleQuery function is the global interface of the repository to get bundle data for the passed
     * bundle id.
     * The function calls the internal getBundleQueryBuilder function which generates the query builder
     * with the different sql paths.
     * The getBundleQueryBuilder function can be hooked to modify the query easily over the query builder.
     *
     * @param int $id
     *
     * @return \Doctrine\ORM\Query
     */
    public function getBundleQuery($id)
    {
        $builder = $this->getBundleQueryBuilder($id);

        $builder->setFirstResult(0)
                ->setMaxResults(1);

        return $builder->getQuery();
    }

    /**
     * The getBundleQueryBuilder function is a helper function which creates an query builder object
     * to select all bundle data for the passed bundle id.
     * The function returns a query builder object which contains the different sql path to select all bundle data.
     * This function can be hooked to modify the query object easily over the query builder.
     *
     * @param int $id
     *
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function getBundleQueryBuilder($id)
    {
        $builder = $this->getDetailQueryBuilder();

        $builder->leftJoin('bundle.article', 'article');
        $builder->leftJoin('article.tax', 'tax');
        $builder->leftJoin('article.mainDetail', 'mainDetail');
        $builder->leftJoin('bundleArticleDetail.article', 'bundleArticleDetailArticle');
        $builder->leftJoin('bundleArticleDetailArticle.tax', 'bundleArticleDetailArticleTax');
        $builder->addSelect(array('article', 'tax', 'bundleArticleDetailArticleTax', 'PARTIAL mainDetail.{id, number}', 'PARTIAL bundleArticleDetailArticle.{id, name, configuratorSetId}'));

        $builder->where('bundle.id = :id')
                ->setParameters(array('id' => $id));

        return $builder;
    }

    /**
     * Internal helper function to create an query builder for the whole bundle data.
     * Used for the backend detail page of a single bundle and on the frontend article detail page.
     *
     * @return \Doctrine\ORM\QueryBuilder|\Shopware\Components\Model\QueryBuilder
     */
    protected function getDetailQueryBuilder()
    {
        /**@var $builder \Shopware\Components\Model\QueryBuilder*/
        $builder = $this->getEntityManager()->createQueryBuilder();
        $builder->select(array(
            'bundle',
            'bundleCustomerGroups',
            'bundleArticles',
            'bundlePrices',
            'bundleLimitedDetails',
            'bundlePriceCustomerGroup',
            'bundleArticleDetail',
            'bundleArticleDetailPrices',
            'bundleArticleDetailPriceCustomerGroup',
        ));
        $builder->from('Shopware\CustomModels\Bundle\Bundle', 'bundle')
                ->leftJoin('bundle.customerGroups', 'bundleCustomerGroups')
                ->leftJoin('bundle.articles', 'bundleArticles')
                ->leftJoin('bundle.prices', 'bundlePrices')
                ->leftJoin('bundlePrices.customerGroup', 'bundlePriceCustomerGroup')
                ->leftJoin('bundle.limitedDetails', 'bundleLimitedDetails')
                ->leftJoin('bundleArticles.articleDetail', 'bundleArticleDetail')
                ->leftJoin('bundleArticleDetail.prices', 'bundleArticleDetailPrices')
                ->leftJoin('bundleArticleDetailPrices.customerGroup', 'bundleArticleDetailPriceCustomerGroup');
        return $builder;
    }

    /**
     * The getFullListQuery function is the global interface of the repository to get all bundles
     * for the passed with their full data.
     * The function calls the internal getFullListQueryBuilder function which generates the query builder
     * with the different sql paths.
     * The getFullListQueryBuilder function can be hooked to modify the query easily over the query builder.
     *
     * @param array $filter
     * @param array $orderBy
     * @param null  $offset
     * @param null  $limit
     *
     * @return \Doctrine\ORM\Query
     */
    public function getFullListQuery($filter = array(), $orderBy = array(), $offset = null, $limit = null)
    {
        $builder = $this->getFullListQueryBuilder($filter, $orderBy);
        if (!empty($offset)) {
            $builder->setFirstResult($offset);
        }
        if (!empty($limit)) {
            $builder->setMaxResults($limit);
        }
        return $builder->getQuery();
    }

    /**
     * The getFullListQueryBuilder function is a helper function which creates an query builder object
     * to select all bundles with their full data.
     * The function returns a query builder object which contains the different sql path to select all bundles.
     * This function can be hooked to modify the query object easily over the query builder.
     *
     * @param array $filter
     * @param array $orderBy
     *
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function getFullListQueryBuilder($filter = array(), $orderBy = array())
    {
        $builder = $this->getDetailQueryBuilder();

        $builder->leftJoin('bundle.article', 'article')
                ->leftJoin('article.tax', 'tax')
                ->leftJoin('article.mainDetail', 'articleMainDetail')
                ->leftJoin('bundleArticleDetail.article', 'bundleArticleDetailArticle');

        $builder->addSelect(array('PARTIAL article.{id, name}', 'PARTIAL articleMainDetail.{id, number}', 'PARTIAL bundleArticleDetailArticle.{id, name}', 'tax'));

        if (!empty($filter)) {
            $builder->addFilter($filter);
        }
        if (!empty($orderBy)) {
            $builder->addOrderBy($orderBy);
        }

        return $builder;
    }



    /**
     * The getArticleBundlesWithDetailQuery function is the global interface of the repository
     * to get all bundles with their details for the passed article id.
     * The function calls the internal getArticleBundlesWithDetailQueryBuilder function
     * which generates the query builder with the different sql paths.
     * The getArticleBundlesWithDetailQueryBuilder function can be
     * hooked to modify the query easily over the query builder.
     *
     * @param int $articleId
     * @param int $customerGroupId
     *
     * @return \Doctrine\ORM\Query
     */
    public function getArticleBundlesWithDetailQuery($articleId, $customerGroupId)
    {
        return $this->getArticleBundlesWithDetailQueryBuilder($articleId, $customerGroupId)
                    ->getQuery();
    }

    /**
     * The getArticleBundlesWithDetailQueryBuilder function is a helper function which creates an query builder object
     * to select all bundles with their details for the passed article id.
     * The function returns a query builder object which contains the different sql path to select all bundle data.
     * This function can be hooked to modify the query object easily over the query builder.
     *
     * @param int $articleId
     * @param int $customerGroupId
     *
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function getArticleBundlesWithDetailQueryBuilder($articleId, $customerGroupId)
    {
        //create helper objects
        $now = new \DateTime();
        $expression = new \Doctrine\ORM\Query\Expr();

        //creates the query builder object which selects the whole bundle data.
        $builder = $this->getDetailQueryBuilder();

        //create valid from filter for the time control.
        //First orX parameter checks the valid from date configuration.
        //Second orX parameter checks the valid from NULL configuration.
        $validFromFilter = $expression->orX(
            $expression->lte('bundle.validFrom', "'" . $now->format('Y-m-d') . " 00:00:00'"),
            $expression->isNull('bundle.validFrom')
        );

        //create valid to filter for the time control.
        //First orX parameter checks the valid to date configuration.
        //Second orX parameter checks the valid to NULL configuration.
        $validToFilter = $expression->orX(
            $expression->gte('bundle.validTo', "'" . $now->format('Y-m-d') . " 00:00:00'"),
            $expression->isNull('bundle.validTo')
        );

        //creates a filter for the active flag
        $activeFilter = $expression->eq('bundle.active', true);

        ///creates a filter for the passed article id.
        $articleFilter = $expression->eq('bundle.articleId', (int) $articleId);

        //create a filter for the customer group limitation.
        //used to select only bundles which should be displayed for the passed customer group id.
        $customerGroupFilter = $expression->eq('bundleCustomerGroups.id', (int) $customerGroupId);

        //create a filter for the bundle quantity limitation.
        //filters all bundles which limited and have no more stock left.
        $limitFilter = $expression->orX(
            $expression->eq('bundle.limited', 0),
            $expression->andX(
                $expression->eq('bundle.limited', 1),
                $expression->gt('bundle.quantity', 0)
            )
        );

        //adds the created filters.
        $builder->addFilter(array(
             $articleFilter,
             $activeFilter,
             $validToFilter,
             $validFromFilter,
             $customerGroupFilter,
             $limitFilter
        ));

        $builder->addOrderBy('bundleArticles.id', 'ASC');

        return $builder;
    }

}
