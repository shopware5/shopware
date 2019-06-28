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

namespace Shopware\Models\Shop;

use Doctrine\ORM\AbstractQuery;
use Doctrine\ORM\Query;
use Shopware\Components\Model\ModelRepository;
use Shopware\Components\Model\QueryBuilder;

class Repository extends ModelRepository
{
    /**
     * Returns a builder-object in order to get all locales
     *
     * @param array|null        $filter
     * @param string|array|null $order
     * @param int|null          $offset
     * @param int|null          $limit
     *
     * @return Query
     */
    public function getLocalesListQuery($filter = null, $order = null, $offset = null, $limit = null)
    {
        $builder = $this->getLocalesListQueryBuilder($filter, $order);
        if ($limit !== null) {
            $builder->setFirstResult($offset)
                ->setMaxResults($limit);
        }

        return $builder->getQuery();
    }

    /**
     * Helper method to create the query builder for the "getLocalesListQuery" function.
     * This function can be hooked to modify the query builder of the query object.
     *
     * @param array|null        $filter
     * @param string|array|null $order
     *
     * @return QueryBuilder
     */
    public function getLocalesListQueryBuilder($filter = null, $order = null)
    {
        $builder = $this->createQueryBuilder('l');
        $fields = [
            'locale',
        ];
        $builder->select($fields);
        $builder->from(\Shopware\Models\Shop\Locale::class, 'locale');
        if ($filter !== null) {
            $builder->addFilter($filter);
        }
        if ($order !== null) {
            $builder->addOrderBy($order);
        }

        return $builder;
    }

    /**
     * Returns a builder-object in order to get all shops
     *
     * @param array|null        $filter
     * @param string|array|null $order
     * @param int|null          $offset
     * @param int|null          $limit
     *
     * @return Query
     */
    public function getBaseListQuery($filter = null, $order = null, $offset = null, $limit = null)
    {
        $builder = $this->getBaseListQueryBuilder($filter, $order);
        if ($limit !== null) {
            $builder->setFirstResult($offset)
                ->setMaxResults($limit);
        }

        return $builder->getQuery();
    }

    /**
     * Returns a query object for all shops with themes.
     *
     * @param array|null        $filter
     * @param string|array|null $order
     * @param int               $offset
     * @param int               $limit
     *
     * @return Query
     */
    public function getShopsWithThemes($filter = null, $order = null, $offset = null, $limit = null)
    {
        $builder = $this->createQueryBuilder('shop');

        $builder->select(['shop', 'template'])
            ->innerJoin('shop.template', 'template')
            ->where('template.version >= 3')
            ->andWhere('shop.main IS NULL')
            ->andWhere('shop.active = 1');

        if ($filter) {
            $builder->addFilter($filter);
        }
        if ($order !== null) {
            $builder->addOrderBy($order);
        }
        if ($limit !== null) {
            $builder->setFirstResult($offset)
                ->setMaxResults($limit);
        }

        return $builder->getQuery();
    }

    /**
     * Helper method to create the query builder for the "getBaseListQuery" function.
     * This function can be hooked to modify the query builder of the query object.
     *
     * @param array|null        $filter
     * @param string|array|null $order
     *
     * @return QueryBuilder
     */
    public function getBaseListQueryBuilder($filter = null, $order = null)
    {
        $builder = $this->createQueryBuilder('shop');
        $fields = [
            'shop.id as id',
            'locale.id as localeId',
            'category.id as categoryId',
            'currency.id as currencyId',
            'shop.default as default',
            'shop.active as active',
            'shop.name as name',
        ];
        $builder->select($fields);
        $builder->leftJoin('shop.locale', 'locale')
                ->leftJoin('shop.category', 'category')
                ->leftJoin('shop.currency', 'currency')
                ->orderBy('default', 'DESC')
                ->addOrderBy('name');

        if ($filter !== null) {
            $builder->addFilter($filter);
        }
        if ($order !== null) {
            $builder->addOrderBy($order);
        }

        return $builder;
    }

    /**
     * Returns the \Doctrine\ORM\Query to select all categories for example for the backend tree
     *
     * @return Query
     */
    public function getListQuery(array $filterBy, array $orderBy, $limit = null, $offset = null)
    {
        $builder = $this->getListQueryBuilder($filterBy, $orderBy, $limit, $offset);

        return $builder->getQuery();
    }

    /**
     * Helper method to create the query builder for the "getListQuery" function.
     * This function can be hooked to modify the query builder of the query object.
     *
     * @return QueryBuilder
     */
    public function getListQueryBuilder(array $filterBy, array $orderBy, $limit = null, $offset = null)
    {
        $builder = $this->createQueryBuilder('shop')
            ->leftJoin('shop.main', 'main')
            ->leftJoin('shop.children', 'children')
            ->select([
                'shop.id as id',
                'shop.name as name',
                'shop.position as position',
                'shop.mainId as mainId',
                'shop.host as host',
                'shop.basePath as basePath',
                'IFNULL(shop.title, shop.name) as title',
                'shop.default as default',
                'shop.active as active',
                'COUNT(children.id) as childrenCount',
            ])
            ->groupBy('shop.id')
            ->addFilter($filterBy)
            ->addOrderBy($orderBy)
            ->addOrderBy('default', 'DESC')
            ->addOrderBy('position');

        $builder->setFirstResult($offset)
                ->setMaxResults($limit);

        return $builder;
    }

    /**
     * Helper function to create the query builder for the "getShopsQuery" function.
     * This function can be hooked to modify the query builder of the query object.
     *
     * @return QueryBuilder
     */
    public function getMainListQueryBuilder()
    {
        return $this->createQueryBuilder('s')
            ->where('s.mainId IS NULL')
            ->orderBy('s.default', 'DESC')
            ->addOrderBy('s.name');
    }

    /**
     * Returns an instance of \Doctrine\ORM\Query object which selects a list of
     * sub shops. Used for the shop combo box on the article detail page in the article backend module.
     *
     * @return Query
     */
    public function getMainListQuery()
    {
        $builder = $this->getMainListQueryBuilder();

        return $builder->getQuery();
    }

    /**
     * @param int $id
     *
     * @return DetachedShop|null
     */
    public function getActiveById($id)
    {
        $builder = $this->getActiveQueryBuilder();
        $builder->andWhere('shop.id=:shopId');
        $builder->setParameter('shopId', $id);
        $shop = $builder->getQuery()->getOneOrNullResult();

        if ($shop !== null) {
            $shop = $this->fixActive($shop);
        }

        return $shop;
    }

    /**
     * @param int $id
     *
     * @return DetachedShop|null
     */
    public function getById($id)
    {
        $builder = $this->getQueryBuilder();
        $builder->andWhere('shop.id=:shopId');
        $builder->setParameter('shopId', $id);
        $shop = $builder->getQuery()->getOneOrNullResult();

        if ($shop !== null) {
            $shop = $this->fixActive($shop);
        }

        return $shop;
    }

    /**
     * Returns the default shop with additional data
     *
     * @return DetachedShop
     */
    public function getActiveDefault()
    {
        $builder = $this->getActiveQueryBuilder();
        $builder->andWhere('shop.default = 1');
        $shop = $builder->getQuery()->getOneOrNullResult();

        if ($shop !== null) {
            $shop = $this->fixActive($shop);
        }

        return $shop;
    }

    /**
     * Returns only the default shop model
     *
     * @return Shop
     */
    public function getDefault()
    {
        $builder = $this->createQueryBuilder('shop');
        $builder->where('shop.default = 1');

        return $builder->getQuery()->getOneOrNullResult();
    }

    /**
     * Returns the active shops
     *
     * @param int $hydrationMode
     *
     * @return array
     */
    public function getActiveShops($hydrationMode = AbstractQuery::HYDRATE_OBJECT)
    {
        $builder = $this->createQueryBuilder('shop');
        $builder->where('shop.active = 1');

        return $builder->getQuery()->getResult($hydrationMode);
    }

    /**
     * Returns the active shops in fixed
     *
     * @return array
     */
    public function getActiveShopsFixed()
    {
        $shops = $this->getActiveShops();

        foreach ($shops as $key => $shop) {
            $shops[$key] = $this->fixActive($shop);
        }

        return $shops;
    }

    /**
     * @param \Enlight_Controller_Request_Request $request
     *
     * @return DetachedShop|null
     */
    public function getActiveByRequest($request)
    {
        $shop = $this->getActiveShopByRequestAsArray($request);

        if (empty($shop)) {
            return null;
        }

        return $this->fetchAndFixShop($shop);
    }

    /**
     * @return array|null
     */
    public function getActiveShopByRequestAsArray(\Enlight_Controller_Request_Request $request)
    {
        $host = $request->getHttpHost();
        if (empty($host)) {
            return null;
        }

        $requestPath = $request->getRequestUri();

        $shops = $this->getShopsArrayByHost($host);

        //returns the right shop depending on the url
        $shop = $this->findShopForRequest($shops, $requestPath);

        if ($shop !== null) {
            return $shop;
        }

        return $this->getShopArrayByHostAlias($host);
    }

    /**
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function getQueryBuilder()
    {
        /* @var QueryBuilder $builder */
        return $this->createQueryBuilder('shop')
            ->addSelect('shop')

            ->addSelect('main')
            ->leftJoin('shop.main', 'main')

            ->addSelect('locale')
            ->leftJoin('shop.locale', 'locale')

            ->addSelect('currency')
            ->leftJoin('shop.currency', 'currency')

            ->addSelect('template')
            ->leftJoin('shop.template', 'template')

            ->addSelect('documentTemplate')
            ->leftJoin('shop.documentTemplate', 'documentTemplate')

            ->addSelect('currencies')
            ->leftJoin('shop.currencies', 'currencies')

            ->addSelect('customerGroup')
            ->leftJoin('shop.customerGroup', 'customerGroup')

            ->addSelect('mainTemplate')
            ->leftJoin('main.template', 'mainTemplate')
            ->leftJoin('main.currencies', 'mainCurrencies')

            ->orderBy('shop.main')
            ->addOrderBy('shop.position');
    }

    /**
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function getActiveQueryBuilder()
    {
        /* @var QueryBuilder $builder */
        return $this->getQueryBuilder()
            ->where('shop.active = 1');
    }

    /**
     * @return DetachedShop
     */
    protected function fixActive(Shop $shop)
    {
        $shop = DetachedShop::createFromShop($shop);

        $main = $shop->getMain();
        if ($main !== null) {
            $main = DetachedShop::createFromShop($main);
            $shop->setHost($main->getHost());
            $shop->setSecure($main->getSecure());
            $shop->setBasePath($shop->getBasePath() ?: $main->getBasePath());
            $shop->setTemplate($main->getTemplate());
            $shop->setCurrencies($main->getCurrencies());
            $shop->setChildren($main->getChildren());
            $shop->setCustomerScope($main->getCustomerScope());
        }

        $shop->setBaseUrl($shop->getBaseUrl() ?: $shop->getBasePath());

        return DetachedShop::createFromShop($shop);
    }

    /**
     * returns the right shop depending on the request object
     *
     * @param array[] $shops
     * @param string  $requestPath
     *
     * @return array
     */
    protected function findShopForRequest($shops, $requestPath)
    {
        $shop = null;
        foreach ($shops as $currentShop) {
            //if the base url matches exactly the basePath we have found the main shop but the loop will continue
            if ($currentShop['base_url'] === $currentShop['base_path']) {
                if ($shop === null) {
                    $shop = $currentShop;
                }
            } elseif ($requestPath === $currentShop['base_url']
                || (strpos($requestPath, $currentShop['base_url']) === 0
                    && in_array($requestPath[strlen($currentShop['base_url'])], ['/', '?']))
            ) {
                /*
                 * Check if the url is the same as the (sub)shop url
                 * or if its the beginning of it, followed by / or ?
                 *
                 * f.e. this will match: localhost/en/blog/blogId=3 but this won't: localhost/entsorgung/
                 */
                if (!$shop || $currentShop['base_url'] > $shop['base_url']) {
                    $shop = $currentShop;
                }
            } elseif (!$shop && $requestPath === $currentShop['base_path'] . '/') {
                /*
                 * If no shop was found, use the one which basePath equals the requestPath
                 *
                 * This is mainly for shops with virtual aliases, which are requested on the baseBath instead
                 * of the virtual alias.
                 *
                 * f.e. basePath: www.subshop1.com      virtual alias: /subshop1
                 *      if you navigate to www.subshop1.com you would have been redirected
                 *      to the main shop on www.mainshop.com. Now you get to your subshop.
                 *
                 */
                $shop = $currentShop;
            }
        }

        return $shop;
    }

    /**
     * @param array $shop
     *
     * @return DetachedShop
     */
    private function fetchAndFixShop($shop)
    {
        if ($shop['is_main']) {
            $query = $this->getActiveMainShopQueryBuilder();
        } else {
            $query = $this->getActiveSubShopQueryBuilder();
        }

        $query->where('shop.id = :id');
        $query->setParameter(':id', $shop['id']);
        $shop = $query->getQuery()->getOneOrNullResult(AbstractQuery::HYDRATE_OBJECT);

        return $this->fixActive($shop);
    }

    /**
     * @param string $host
     *
     * @return array
     */
    private function getShopsArrayByHost($host)
    {
        $query = $this->getDbalShopsQuery();
        $query->andWhere('shop.active = 1');
        $query->andWhere('(shop.host = :host OR (shop.host IS NULL AND main_shop.host = :host))');
        $query->setParameter(':host', $host);
        $shops = $query->execute()->fetchAll(\PDO::FETCH_ASSOC);

        usort($shops, function ($a, $b) {
            if ($a['is_main'] && !$b['is_main']) {
                return -1;
            }

            if (!$a['is_main'] && $b['is_main']) {
                return 1;
            }

            if ($a['is_main'] === $b['is_main']) {
                return $a['position'] > $b['position'];
            }

            return 0;
        });

        return array_map(function ($shop) {
            $shop['base_url'] = $shop['base_url'] ?: $shop['base_path'];

            return $shop;
        }, $shops);
    }

    /**
     * @param string $host
     *
     * @return array|false
     */
    private function getShopArrayByHostAlias($host)
    {
        $query = $this->getDbalShopsQuery();
        $query->where('(shop.hosts LIKE :host1 OR shop.hosts LIKE :host2 OR shop.hosts LIKE :host3 OR shop.hosts LIKE :host4)');
        $query->andWhere('shop.active = 1');
        $query->setParameter('host1', "%\n" . $host . "\n%");
        $query->setParameter('host2', $host . "\n%");
        $query->setParameter('host3', "%\n" . $host);
        $query->setParameter('host4', $host);
        $query->orderBy('shop.main_id');
        $query->addOrderBy('shop.position');
        $query->setMaxResults(1);

        return $query->execute()->fetch(\PDO::FETCH_ASSOC);
    }

    /**
     * @return \Doctrine\DBAL\Query\QueryBuilder
     */
    private function getDbalShopsQuery()
    {
        $query = $this->getEntityManager()->getConnection()->createQueryBuilder();
        $query->select([
            'shop.id',
            'shop.name',
            'shop.base_url',
            'shop.position',
            'IF(main_shop.id IS NULL, 1, 0) is_main',
            'IFNULL(main_shop.host, shop.host) as host',
            'IFNULL(main_shop.hosts, shop.hosts) as hosts',
            'IFNULL(main_shop.secure, shop.secure) as secure',
            'IFNULL(main_shop.base_path, shop.base_path) as base_path',
            'IFNULL(main_shop.template_id, shop.template_id) as template_id',
            'IFNULL(main_shop.customer_scope, shop.customer_scope) as customer_scope',
        ]);
        $query->from('s_core_shops', 'shop');
        $query->leftJoin('shop', 's_core_shops', 'main_shop', 'shop.main_id = main_shop.id');

        return $query;
    }

    /**
     * @return \Doctrine\ORM\QueryBuilder
     */
    private function getActiveMainShopQueryBuilder()
    {
        /* @var QueryBuilder $builder */
        return $this->createQueryBuilder('shop')
            ->addSelect('shop')

            ->addSelect('locale')
            ->leftJoin('shop.locale', 'locale')

            ->addSelect('currency')
            ->leftJoin('shop.currency', 'currency')

            ->addSelect('template')
            ->leftJoin('shop.template', 'template')

            ->addSelect('documentTemplate')
            ->leftJoin('shop.documentTemplate', 'documentTemplate')

            ->addSelect('currencies')
            ->leftJoin('shop.currencies', 'currencies')

            ->addSelect('customerGroup')
            ->leftJoin('shop.customerGroup', 'customerGroup')

            ->where('shop.active = 1');
    }

    /**
     * @return \Doctrine\ORM\QueryBuilder
     */
    private function getActiveSubShopQueryBuilder()
    {
        /* @var QueryBuilder $builder */
        return $this->createQueryBuilder('shop')
            ->addSelect('shop')

            ->addSelect('main')
            ->leftJoin('shop.main', 'main')

            ->addSelect('locale')
            ->leftJoin('shop.locale', 'locale')

            ->addSelect('currency')
            ->leftJoin('shop.currency', 'currency')

            ->addSelect('customerGroup')
            ->leftJoin('shop.customerGroup', 'customerGroup')

            ->addSelect('template')
            ->leftJoin('main.template', 'template')

            ->addSelect('documentTemplate')
            ->leftJoin('main.documentTemplate', 'documentTemplate')

            ->addSelect('currencies')
            ->leftJoin('main.currencies', 'currencies')

            ->where('shop.active = 1');
    }
}
