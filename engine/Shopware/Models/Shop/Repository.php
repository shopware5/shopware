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
     * @param null $filter
     * @param null $order
     * @param null $offset
     * @param null $limit
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
     * @param null $filter
     * @param null $order
     * @return QueryBuilder
     */
    public function getLocalesListQueryBuilder($filter = null, $order = null)
    {
        $builder = $this->createQueryBuilder('l');
        $fields = array(
            'locale'
        );
        $builder->select($fields);
        $builder->from('Shopware\Models\Shop\Locale', 'locale');
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
     * @param array $filter
     * @param array $order
     * @param int $offset
     * @param int $limit
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
     * @param array $filter
     * @param array $order
     * @param int $offset
     * @param int $limit
     * @return Query
     */
    public function getShopsWithThemes($filter = null, $order = null, $offset = null, $limit = null)
    {
        $builder = $this->createQueryBuilder('shop');

        $builder->select(array('shop', 'template'))
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
     * @param array $filter
     * @param array $order
     * @return QueryBuilder
     */
    public function getBaseListQueryBuilder($filter = null, $order = null)
    {
        $builder = $this->createQueryBuilder('shop');
        $fields = array(
            'shop.id as id',
            'locale.id as localeId',
            'category.id as categoryId',
            'currency.id as currencyId',
            'shop.default as default',
            'shop.active as active',
            'shop.name as name'
        );
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
     * @param array $filterBy
     * @param array $orderBy
     * @param null $limit
     * @param null $offset
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
     * @param   array $filterBy
     * @param   array $orderBy
     * @param   null $limit
     * @param   null $offset
     * @return  QueryBuilder
     */
    public function getListQueryBuilder(array $filterBy, array $orderBy, $limit = null, $offset = null)
    {
        $builder = $this->createQueryBuilder('shop')
            ->leftJoin('shop.main', 'main')
            ->leftJoin('shop.children', 'children')
            ->select(array(
                'shop.id as id',
                'shop.name as name',
                'shop.position as position',
                'shop.mainId as mainId',
                'shop.host as host',
                'shop.basePath as basePath',
                'IFNULL(shop.title, shop.name) as title',
                'shop.default as default',
                'shop.active as active',
                'COUNT(children.id) as childrenCount'
            ))
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
     * @return Query
     */
    public function getMainListQuery()
    {
        $builder = $this->getMainListQueryBuilder();
        return $builder->getQuery();
    }

    /**
     * @return QueryBuilder
     */
    public function getActiveQueryBuilder()
    {
        /** @var $builder QueryBuilder */
        $baseBuilder = $this->createQueryBuilder('shop')
            ->leftJoin('shop.main', 'main')
            ->leftJoin('shop.locale', 'locale')
            ->leftJoin('shop.currency', 'currency')
            ->leftJoin('shop.template', 'template')
            ->leftJoin('shop.documentTemplate', 'documentTemplate')
            ->leftJoin('shop.currencies', 'currencies')
            ->leftJoin('shop.customerGroup', 'customerGroup')
            ->leftJoin('main.template', 'mainTemplate')
            ->leftJoin('main.currencies', 'mainCurrencies')
            ->select(array(
                'shop',
                'main',
                'locale',
                'currency',
                'template',
                'currencies',
                'documentTemplate',
                'customerGroup'
            ))
            ->where('shop.active = 1')
            ->orderBy('shop.main')
            ->addOrderBy('shop.position');

        return $baseBuilder;
    }

    /**
     * @param int $id
     * @return DetachedShop
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
        $shop = $builder->getQuery()->getOneOrNullResult();

        return $shop;
    }

    /**
     * Returns the active shops
     *
     * @param int $hydrationMode
     * @return array
     */
    public function getActiveShops($hydrationMode = AbstractQuery::HYDRATE_OBJECT)
    {
        $builder = $this->createQueryBuilder('shop');
        $builder->where('shop.active = 1');
        $shops = $builder->getQuery()->getResult($hydrationMode);

        return $shops;
    }

    /**
     * @param \Enlight_Controller_Request_Request $request
     * @return DetachedShop
     */
    public function getActiveByRequest($request)
    {
        $host = $request->getHttpHost();
        if (empty($host)) {
            return null;
        }

        $requestPath = $request->getRequestUri();

        $builder = $this->getActiveQueryBuilder();
        $builder->andWhere("shop.host=:host OR (shop.host IS NULL AND main.host=:host)");
        if ($request->isSecure()) {
            $builder->orWhere("shop.secureHost=:host OR (shop.secureHost IS NULL AND main.secureHost=:host)");
        }
        $builder->setParameter('host', $host);

        /** @var $shops Shop[] */
        $shops = $builder->getQuery()->getResult();

        foreach ($shops as $key => $currentShop) {
            $shops[$key] = $this->fixActive($currentShop);
        }

        //returns the right shop depending on the url
        $shop = $this->getShopByRequest($shops, $requestPath);

        if ($shop !== null) {
            return $shop;
        }

        $builder = $this->getActiveQueryBuilder();
        $builder->andWhere('shop.hosts LIKE :host1 OR shop.hosts LIKE :host2 OR shop.hosts LIKE :host3')
            ->setParameter('host1', "%\n" . $host . "\n%")
            ->setParameter('host2', $host . "\n%")
            ->setParameter('host3', "%\n" . $host);

        $shop = $builder->getQuery()->getOneOrNullResult();

        if ($shop !== null) {
            $shop = $this->fixActive($shop);
        }

        return $shop;
    }

    /**
     * @param Shop $shop
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
            $shop->setAlwaysSecure($main->getAlwaysSecure());
            $shop->setSecureHost($main->getSecureHost());
            $shop->setSecureBasePath($main->getSecureBasePath());
            $shop->setBasePath($shop->getBasePath() ?: $main->getBasePath());
            $shop->setTemplate($main->getTemplate());
            $shop->setCurrencies($main->getCurrencies());
            $shop->setChildren($main->getChildren());
            $shop->setCustomerScope($main->getCustomerScope());
        }

        $shop->setBaseUrl($shop->getBaseUrl() ?: $shop->getBasePath());
        if ($shop->getSecure()) {
            $shop->setSecureHost($shop->getSecureHost()?: $shop->getHost());
            $shop->setSecureBasePath($shop->getSecureBasePath()?: $shop->getBasePath());
            $baseUrl = $shop->getSecureBasePath();
            if ($shop->getBaseUrl() != $shop->getBasePath()) {
                if (!$shop->getBasePath()) {
                    $baseUrl .= $shop->getBaseUrl();
                } elseif (strpos($shop->getBaseUrl(), $shop->getBasePath()) === 0) {
                    $baseUrl .= substr($shop->getBaseUrl(), strlen($shop->getBasePath()));
                }
            }
            $shop->setSecureBaseUrl($baseUrl);
        }

        return DetachedShop::createFromShop($shop);
    }

    /**
     * returns the right shop depending on the request object
     *
     * @param Shop[] $shops
     * @param string $requestPath
     * @return null|Shop $shop
     */
    protected function getShopByRequest($shops, $requestPath)
    {
        $shop = null;
        foreach ($shops as $currentShop) {
            if ($currentShop->getBaseUrl() == $currentShop->getBasePath()) {
                //if the base url matches exactly the basePath we have found the main shop but the loop will continue
                if ($shop === null) {
                    $shop = $currentShop;
                }
            } elseif ($requestPath == $currentShop->getBaseUrl()
                || (strpos($requestPath, $currentShop->getBaseUrl()) === 0
                && in_array($requestPath[strlen($currentShop->getBaseUrl())], array('/', '?')))
            ) {
                /*
                 * Check if the url is the same as the (sub)shop url
                 * or if its the beginning of it, followed by / or ?
                 *
                 * f.e. this will match: localhost/en/blog/blogId=3 but this won't: localhost/entsorgung/
                 */
                if (!$shop || $currentShop->getBaseUrl() > $shop->getBaseUrl()) {
                    $shop = $currentShop;
                }
            } elseif ($currentShop->getSecure()
                && ($requestPath == $currentShop->getSecureBaseUrl()
                || (strpos($requestPath, $currentShop->getSecureBaseUrl()) === 0
                && in_array($requestPath[strlen($currentShop->getSecureBaseUrl())], array('/', '?'))))
            ) {
                /*
                 * Only if the shop is used in secure (ssl) mode
                 *
                 * Check if the url is the same as the (sub)shop url
                 * or if its the beginning of it, followed by / or ?
                 *
                 * f.e. this will match: localhost/en/blog/blogId=3 but this won't: localhost/entsorgung/
                 */
                if (!$shop || $currentShop->getSecureBaseUrl() > $shop->getSecureBaseUrl()) {
                    $shop = $currentShop;
                }
            } elseif (!$shop && $currentShop->getBasePath() . '/' == $requestPath) {
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
}
