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
 *
 * @category   Shopware
 * @package    Shopware_Models
 * @subpackage Shop
 * @copyright  Copyright (c) 2012, shopware AG (http://www.shopware.de)
 * @version    $Id$
 * @author     $Author$
 */

namespace Shopware\Models\Shop;
use Shopware\Components\Model\ModelRepository;

/**
 * todo@all: Documentation
 */
class Repository extends ModelRepository
{

    /**
     * Returns a builder-object in order to get all locales
     *
     * @param null $filter
     * @param null $order
     * @param null $offset
     * @param null $limit
     * @return \Doctrine\ORM\Query
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
     * @return \Doctrine\ORM\QueryBuilder
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
        if($order !== null) {
            $builder->addOrderBy($order);
        }

        return $builder;
    }

    /**
     * Returns a builder-object in order to get all shops
     *
     * @param null $filter
     * @param null $order
     * @param null $offset
     * @param null $limit
     * @return \Doctrine\ORM\Query
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
     * Helper method to create the query builder for the "getBaseListQuery" function.
     * This function can be hooked to modify the query builder of the query object.
     *
     * @param null $filter
     * @param null $order
     * @return \Doctrine\ORM\QueryBuilder
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
        if($order !== null) {
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
     * @return \Doctrine\ORM\Query
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
     * @return  \Shopware\Components\Model\QueryBuilder
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
     * @return \Doctrine\ORM\QueryBuilder
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
     * @return \Doctrine\ORM\Query
     */
    public function getMainListQuery()
    {
        $builder = $this->getMainListQueryBuilder();
        return $builder->getQuery();
    }

    /**
     * @return \Shopware\Components\Model\QueryBuilder
     */
    public function getActiveQueryBuilder()
    {
        /** @var $builder \Shopware\Components\Model\QueryBuilder */
        $baseBuilder = $this->createQueryBuilder('shop')
            ->leftJoin('shop.main', 'main')
            ->leftJoin('shop.locale', 'locale')
            ->leftJoin('shop.currency', 'currency')
            ->leftJoin('shop.template', 'template')
            ->leftJoin('shop.currencies', 'currencies')
            ->leftJoin('shop.pages', 'pages')
            ->leftJoin('shop.customerGroup', 'customerGroup')
            ->leftJoin('main.template', 'mainTemplate')
            ->leftJoin('main.currencies', 'mainCurrencies')
            ->select(array(
                'shop', 'main',
                'locale', 'currency',
                'template', 'currencies'
            ))
            ->where('shop.active = 1')
            ->orderBy('shop.main')
            ->addOrderBy('shop.position');
        return $baseBuilder;
    }

    /**
     * @param $id
     * @return \Shopware\Models\Shop\Shop
     */
    public function getActiveById($id)
    {
        $builder = $this->getActiveQueryBuilder();
        $builder->andWhere('shop.id=:shopId');
        $builder->setParameter('shopId', $id);
        $shop = $builder->getQuery()->getOneOrNullResult();

        if($shop !== null) {
            $this->fixActive($shop);
        }

        return $shop;
    }

    /**
     * Returns the default shop
     *
     * @return \Shopware\Models\Shop\Shop
     */
    public function getActiveDefault()
    {
        $builder = $this->getActiveQueryBuilder();
        $builder->where('shop.default = 1');
        $shop = $builder->getQuery()->getOneOrNullResult();

        if($shop !== null) {
            $this->fixActive($shop);
        }

        return $shop;
    }

    /**
     * @param \Enlight_Controller_Request_RequestHttp $request
     * @return \Shopware\Models\Shop\Shop
     */
    public function getActiveByRequest($request)
    {
        /** @var $shop \Shopware\Models\Shop\Shop */
        $shop = null;
        $host = $request->getHttpHost();
        if(empty($host)) {
            return $shop;
        }
        $requestPath = $request->getRequestUri();

        $builder = $this->getActiveQueryBuilder();
        $builder->andWhere("shop.host=:host OR (shop.host IS NULL AND main.host=:host)");
        if($request->isSecure()) {
            $builder->orWhere("shop.secureHost=:host OR (shop.secureHost IS NULL AND main.secureHost=:host)");
        }
        $builder->setParameter('host', $host);

        /** @var $shops \Shopware\Models\Shop\Shop[] */
        $shops = $builder->getQuery()->getResult();

        foreach ($shops as $currentShop) {
            $this->fixActive($currentShop);
        }
        foreach ($shops as $currentShop) {
            if ($currentShop->getBaseUrl() == $currentShop->getBasePath()) {
                if ($shop === null) {
                    $shop = $currentShop;
                }
            } elseif (strpos($requestPath, $currentShop->getBaseUrl()) === 0) {
                $shop = $currentShop;
                break;
            } elseif ($currentShop->getSecure() && strpos($requestPath, $currentShop->getSecureBaseUrl()) === 0) {
                $shop = $currentShop;
                break;
            }
        }

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
            $this->fixActive($shop);
        }

        return $shop;
    }

    /**
     * @param \Shopware\Models\Shop\Shop $shop
     */
    protected function fixActive($shop)
    {
        $this->getEntityManager()->detach($shop);
        $main = $shop->getMain();
        if ($main !== null) {
            $this->getEntityManager()->detach($main);
            $shop->setHost($main->getHost());
            $shop->setSecure($main->getSecure());
            $shop->setSecureHost($main->getSecureHost());
            $shop->setSecureBasePath($main->getSecureBasePath());
            $shop->setBasePath($shop->getBasePath() ?: $main->getBasePath());
            $shop->setBaseUrl($shop->getBaseUrl() ?: $main->getBaseUrl());
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
            if($shop->getBaseUrl() != $shop->getBasePath()) {
                if(!$shop->getBasePath()) {
                    $baseUrl .= $shop->getBaseUrl();
                } elseif(strpos($shop->getBaseUrl(), $shop->getBasePath()) === 0) {
                    $baseUrl .= substr($shop->getBaseUrl(), strlen($shop->getBasePath()));
                }
            }
            $shop->setSecureBaseUrl($baseUrl);
        }
    }
}
