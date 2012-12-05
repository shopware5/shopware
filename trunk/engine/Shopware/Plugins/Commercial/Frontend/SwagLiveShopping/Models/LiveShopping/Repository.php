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

namespace Shopware\CustomModels\LiveShopping;
use Shopware\Components\Model\ModelRepository;

/**
 * Repository of the SwagLiveShopping plugin.
 *
 * @category Shopware
 * @package Shopware\Plugins\SwagLiveShopping\Models\LiveShopping
 * @copyright Copyright (c) 2012, shopware AG (http://www.shopware.de)
 */
class Repository extends ModelRepository
{
    /**
     * Listing query builder.
     *
     * Returns an query builder object for a LiveShopping record list.
     * The listing query builder of this repository is used for the backend listing module
     * of this plugin or for the frontend listing.
     *
     * @param       $articleId
     * @param array $filter
     * @param array $sort
     * @param int   $offset
     * @param int   $limit
     *
     * @return \Shopware\Components\Model\QueryBuilder
     */
    public function getListQueryBuilder($articleId, $filter, $sort, $offset = null, $limit = null)
    {
        /**@var $builder \Shopware\Components\Model\QueryBuilder*/
        $builder = $this->createQueryBuilder('LiveShopping');

        $builder->andWhere('LiveShopping.articleId = :articleId');
        $builder->setParameters(array('articleId' => $articleId));

        if (!empty($filter)) {
            $builder->addFilter($filter);
        }

        if (!empty($sort)) {
            $builder->addOrderBy($sort);
        }

        if ($limit !== null && $offset !== null) {
            $builder->setFirstResult($offset)
                    ->setMaxResults($limit);
        }

        return $builder;
    }

    /**
     * Detail query builder.
     *
     * Returns an query builder object which selects a single LiveShopping record with
     * the minimum stack of the LiveShopping associations to prevent an stack overflow
     * in the query.
     * Info: Don't join N:M associations, this woluld blast the query result and doctrine
     * has to iterate milions of records and the function runs into a timeout.
     *
     * @param $id
     *
     * @return \Shopware\Components\Model\QueryBuilder
     */
    public function getDetailQueryBuilder($id)
    {
        /**@var $builder \Shopware\Components\Model\QueryBuilder*/
        $builder = $this->createQueryBuilder('LiveShopping');

        $builder->leftJoin('LiveShopping.limitedVariants', 'limitedVariants')
                ->leftJoin('LiveShopping.prices', 'prices')
                ->leftJoin('LiveShopping.article', 'article')
                ->leftJoin('article.tax', 'tax')
                ->leftJoin('prices.customerGroup', 'customerGroup');

        $builder->addSelect(array('prices', 'limitedVariants', 'customerGroup', 'article', 'tax'));

        $builder->andWhere('LiveShopping.id = :id')
                ->setParameters(array('id' => $id));

        return $builder;
    }

    /**
     * Customer groups query builder.
     *
     * Returns an query builder object which selects the customer groups assocation of a single LiveShopping record.
     *
     * Info: Don't join N:M associations, this woluld blast the query result and doctrine
     * has to iterate milions of records and the function runs into a timeout.
     *
     * @param $id
     *
     * @return \Shopware\Components\Model\QueryBuilder
     */
    public function getCustomerGroupsQueryBuilder($id)
    {
        $builder = $this->createQueryBuilder('liveShopping');
        $builder->leftJoin('liveShopping.customerGroups', 'customerGroups');
        $builder->addSelect(array('customerGroups'));
        $builder->where('liveShopping.id = :id');
        $builder->setParameters(array('id' => $id));
        return $builder;
    }

    /**
     * Shops query builder.
     *
     * Returns an query builder object which selects the shops assocation of a single LiveShopping record.
     *
     * Info: Don't join N:M associations, this woluld blast the query result and doctrine
     * has to iterate milions of records and the function runs into a timeout.
     *
     * @param $id
     *
     * @return \Shopware\Components\Model\QueryBuilder
     */
    public function getShopsQueryBuilder($id)
    {
        $builder = $this->createQueryBuilder('liveShopping');
        $builder->leftJoin('liveShopping.shops', 'shops');
        $builder->addSelect(array('shops'));
        $builder->andWhere('liveShopping.id = :id');
        $builder->setParameters(array('id' => $id));
        return $builder;
    }

    /**
     * Active query builder.
     *
     * Creates an query builder objects which searchs an active and valid
     * live shopping article for the passed aritlce id.
     * This query is used for the shop frontend to find live shopping
     * articles which has to be displayed.
     *
     * @param      $articleId
     * @param      $customerGroup \Shopware\Models\Customer\Group
     * @param null $shop \Shopware\Models\Shop\Shop
     *
     * @return \Shopware\Components\Model\QueryBuilder
     */
    public function getActiveLiveShoppingForArticleQueryBuilder($articleId, $customerGroup = null, $shop = null)
    {
        $builder = $this->getActiveLiveShoppingQueryBuilder($customerGroup, $shop);

        $expression = new \Doctrine\ORM\Query\Expr();

        ///creates a filter for the passed article id.
        $articleFilter = $expression->eq('liveShopping.articleId', (int) $articleId);

        //adds the created filters.
        $builder->addFilter(array($articleFilter));

        return $builder;
    }

    /**
     * Active query builder.
     *
     * Creates an query builder objects which returns the live shopping article
     * data for the passed live shopping id.
     * This query is used for the shop frontend to refresh the live shopping data
     * of a displayed live shopping article on the article detail page.
     *
     * @param      $liveShoppingId
     * @param      $customerGroup \Shopware\Models\Customer\Group
     * @param null $shop \Shopware\Models\Shop\Shop
     *
     * @return \Shopware\Components\Model\QueryBuilder
     */
    public function getActiveLiveShoppingByIdQueryBuilder($liveShoppingId, $customerGroup = null, $shop = null)
    {
        $builder = $this->getActiveLiveShoppingQueryBuilder($customerGroup, $shop);

        $expression = new \Doctrine\ORM\Query\Expr();

        ///creates a filter for the passed article id.
        $idFilter = $expression->eq('liveShopping.id', (int) $liveShoppingId);

        //adds the created filters.
        $builder->addFilter(array($idFilter));

        return $builder;
    }

    /**
     * Active live shopping query builder.
     *
     * This function returns the query builder for all active live shopping articles
     * The function is used for the getActiveLiveShoppingForArticleQueryBuilder or
     *
     * @param      $customerGroup
     *
     * @param null $shop
     *
     * @return \Shopware\Components\Model\QueryBuilder
     */
    public function getActiveLiveShoppingQueryBuilder($customerGroup = null, $shop = null)
    {
        //create helper objects
        $now = new \DateTime();
        $expression = new \Doctrine\ORM\Query\Expr();

        /**@var $builder \Shopware\Components\Model\QueryBuilder*/
        $builder = $this->getEntityManager()->createQueryBuilder();
        $builder->select(array('liveShopping', 'prices', 'customerGroup'))
                ->from('Shopware\CustomModels\LiveShopping\LiveShopping', 'liveShopping')
                ->leftJoin('liveShopping.prices', 'prices')
                ->leftJoin('prices.customerGroup', 'customerGroup')
                ->leftJoin('liveShopping.shops', 'shops')
                ->leftJoin('liveShopping.customerGroups', 'liveShoppingCustomerGroups');

        //creates a filter for the active flag
        $activeFilter = $expression->eq('liveShopping.active', true);

        //create a filter for the bundle quantity limitation.
        //filters all bundles which limited and have no more stock left.
        $limitFilter = $expression->orX(
            $expression->eq('liveShopping.limited', 0),
            $expression->andX(
                $expression->eq('liveShopping.limited', 1),
                $expression->gt('liveShopping.quantity', 0)
            )
        );

        //create valid to filter for the time control.
        $validToFilter = $expression->gte('liveShopping.validTo', "'" . $now->format('Y-m-d H:i:00') . "'");

        //create valid from filter for the time control.
        $validFromFilter = $expression->lte('liveShopping.validFrom', "'" . $now->format('Y-m-d H:i:00') . "'");

        if ($customerGroup instanceof \Shopware\Models\Customer\Group) {

            //create a filter for the customer group limitation.
            //used to select only bundles which should be displayed for the passed customer group id.
            $customerGroupFilter = $expression->eq('liveShoppingCustomerGroups.id', (int) $customerGroup->getId());

            $builder->addFilter(array($customerGroupFilter));

            $customerGroupFilter = $expression->eq('customerGroup.id', (int) $customerGroup->getId());

            $builder->addFilter(array($customerGroupFilter));
        }

        if ($shop instanceof \Shopware\Models\Shop\Shop) {

            //create a filter for the shop limitation.
            //used to select only bundles which should be displayed for the passed shop id.
            $shopFilter = $expression->eq('shops.id', (int) $shop->getId());

            $builder->addFilter(array($shopFilter));
        }

        //adds the created filters.
        $builder->addFilter(array(
            $activeFilter,
            $limitFilter,
            $validToFilter,
            $validFromFilter
        ));

        return $builder;
    }

    /**
     * Returns the variants for the passed configuration.
     *
     * This function is used to check which article variant is selected on the
     * article detail page.
     * In case thtat the article is no configurator article, the function returns the
     * main variant.
     * The configuration parameter has to be passed in the following array format:
     * <pre>
     *      [groupId] => optionId
     *      ...
     * </pre>
     * @param $article \Shopware\Models\Article\Article
     * @param $configuration
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function getVariantForArticleConfigurationQueryBuilder($article, $configuration) {
        $builder = $this->getEntityManager()->createQueryBuilder();

        $builder->select(array('variant'))
                ->from('Shopware\Models\Article\Detail', 'variant')
                ->where('variant.articleId = :articleId');

        $paramaters = array('articleId' => $article->getId());

        foreach($configuration as $group => $option) {
            if (!empty($group) && !empty($option)) {
                $alias = 'o' . $option;
                $builder->innerJoin('variant.configuratorOptions', $alias);
                $builder->andWhere($alias . '.id = :' . $alias);
                $paramaters[$alias] = (int) $option;
            }
        }
        $builder->setParameters($paramaters);
        $builder->orderBy('variant.kind', 'ASC');
        return $builder;
    }

    /**
     * Basket live shoppings.
     *
     * This query builder is used to get the last added live shopping article in the basket.
     * The function query builder is used to return the article data in the afterAddArticle hook.
     *
     * @param $sessionId
     *
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function getLastBasketLiveShoppingQueryBuilder($sessionId)
    {
        $builder = $this->getEntityManager()->createQueryBuilder();
        $builder->select(array('basket', 'attribute'))
                ->from('Shopware\Models\Order\Basket', 'basket')
                ->innerJoin('basket.attribute', 'attribute')
                ->where('basket.sessionId = :sessionId')
                ->andWhere('attribute.swagLiveShoppingId IS NOT NULL')
                ->setParameters(array('sessionId' => $sessionId))
                ->orderBy('basket.id', 'DESC')
                ->setFirstResult(0)
                ->setMaxResults(1);

        return $builder;
    }

    /**
     * Basket live shoppings.
     *
     * This query builder is used to get all live shopping articles of the basket
     * for the passed session id. Used to validate and update basket live shopping articles.
     *
     * @param $sessionId
     *
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function getBasketAttribtuesWithLiveShoppingFlagQueryBuilder($sessionId)
    {
        $builder = $this->getEntityManager()->createQueryBuilder();
        $builder->select(array('attribute'))
                ->from('Shopware\Models\Attribute\OrderBasket', 'attribute')
                ->innerJoin('attribute.orderBasket', 'basket')
                ->where('basket.sessionId = :sessionId')
                ->andWhere('attribute.swagLiveShoppingId IS NOT NULL')
                ->setParameters(array('sessionId' => $sessionId));

        return $builder;
    }

    /**
     * Backend variant listing query builder.
     *
     * This query builder is used for the "limited variants" tab panel in the live shopping
     * backend extension in the article module. The query builder selects an limited offset
     * of article variants for the passed article id.
     * @param $articleId
     * @param $offset
     * @param $limit
     *
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function getArticleVariantsQueryBuilder($articleId, $offset, $limit)
    {
        $builder = $this->getEntityManager()->createQueryBuilder();

        $builder->select(array('details'))
                ->from('Shopware\Models\Article\Detail', 'details')
                ->where('details.articleId = :articleId')
                ->setParameters(array('articleId' => $articleId))
                ->setFirstResult($offset)
                ->setMaxResults($limit);

        return $builder;
    }
}