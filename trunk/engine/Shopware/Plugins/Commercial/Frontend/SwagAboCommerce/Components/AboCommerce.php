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

/**
 * Shopware SwagAboCommerce Plugin - Component
 *
 * @category  Shopware
 * @package   Shopware\Plugins\SwagAboCommerce\Components
 * @copyright Copyright (c) 2012, shopware AG (http://www.shopware.de)
 *
 * @Shopware\noEncryption
 */
class Shopware_Components_AboCommerce extends Enlight_Class
{
     /**
     * Getter function for the configurator module property of this class.
     *
     * Used for the price calculation. If this property is set to null,
     * the function loads the default class "Shopware()->Modules()->Configurator()"
     *
     * @return \sConfigurator
     */
    public function getConfiguratorModule()
    {
        return Shopware()->Modules()->Configurator();
    }

    /**
     * Getter function for the session property of this class.
     *
     * Contains the current customer session.
     * The session contains for example the unique session id
     * which is used as basket identification for the current customer.
     * If the session property contains null, this function loads the
     * standard shopware session over "Shopware()->Session()".
     *
     * @return \Enlight_Components_Session_Namespace
     */
    public function getSession()
    {
        return Shopware()->Session();
    }

    /**
     * Getter function for the shop property of this class.
     *
     * Contains the current sub shop model of shopware.
     * If the class property contains null, the getter function loads
     * the active sub shop over "Shopware()->Shop()".
     *
     * @return \Shopware\Models\Shop\Shop
     */
    public function getShop()
    {
        return Shopware()->Shop();
    }

    /**
     * Getter function for the snippet namespace property.
     *
     * The snippet namespace is used for to get the translation
     * for the different basket notices and errors.
     * If the class property contains null, this function loads automatically the default snippet namespace
     * "Shopware()->Snippets()->getNamespace('frontend/checkout/cart_item')".
     *
     * @return Enlight_Components_Snippet_Namespace
     */
    public function getSnippetNamespace()
    {
        return Shopware()->Snippets()->getNamespace('frontend/checkout/cart_item');
    }

    /**
     * Getter function for the basketComponent property of this class.
     *
     * If the class property contains null, the getter function loads
     * the basket component over "Shopware()->AboCommerceBasket()"
     *
     * @return Shopware_Components_AboCommerceBasket
     */
    public function getBasketComponent()
    {
        return Shopware()->AboCommerceBasket();
    }

    /**
     * Getter function for the session id property of this class.
     *
     * Used for the customer identification.
     * If the class property contains null, the function loads the
     * session id over "Shopware()->SessionID()".
     *
     * @return string
     */
    public function getSessionId()
    {
        return Shopware()->SessionID();
    }

    /**
     * Getter function of the database property of this class.
     *
     * If the database property is set to null, this getter
     * function loads the default connection over "Shopware()->Db()".
     *
     * @return \Enlight_Components_Db_Adapter_Pdo_Mysql
     */
    public function getDatabase()
    {
        return Shopware()->Db();
    }

     /**
     * Getter function for the article module property of this class.
     *
     * Used for the price calculation. If this property is set to null,
     * the function loads the default class "Shopware()->Modules()->Articles()"
     *
     * @return \sArticles
     */
    public function getArticleModule()
    {
        return Shopware()->Modules()->Articles();
    }

    /**
     * Getter function for the entityManager property.
     *
     * @return \Shopware\Components\Model\ModelManager
     */
    public function getEntityManager()
    {
        return Shopware()->Models();
    }

    /**
     * Getter function of the customerGroupRepository property.
     *
     * @return \Shopware\Components\Model\ModelRepository
     */
    public function getCustomerGroupRepository()
    {
        return $this->getEntityManager()->getRepository('Shopware\Models\Customer\Group');
    }

    /**
     * Getter function of the articleRepository property.
     *
     * @return \Shopware\Models\Article\Repository
     */
    public function getArticleRepository()
    {
        return $this->getEntityManager()->getRepository('Shopware\Models\Article\Article');
    }

    /**
     * Getter function of the basketRepository property.
     *
     * @return \Shopware\Components\Model\ModelRepository
     */
    public function getBasketRepository()
    {
        return $this->getEntityManager()->getRepository('Shopware\Models\Order\Basket');
    }

    /**
     * Getter function of the aboCommerceRepository property.
     *
     * @return \Shopware\CustomModels\SwagAboCommerce\Repository
     */
    public function getAboCommerceRepository()
    {
        return $this->getEntityManager()->getRepository('Shopware\CustomModels\SwagAboCommerce\Article');
    }

    /**
     * @param Shopware\Models\Article\Detail $variant
     * @param array $aboArticle
     * @param array $basketItem
     * @param int   $quantity
     * @param int   $duration
     * @param int   $deliveryInterval
     *
     * @throws Exception
     * @return void
     */
    public function getDiscountForArticle(\Shopware\Models\Article\Detail $variant, $aboArticle, $basketItem, $quantity, $duration, $deliveryInterval)
    {
        $basePrice = $variant->getPrices()->first()->getPrice();

        $activeDiscount = null;
        foreach ($aboArticle['prices'] as $discount) {
            if ($discount['durationFrom'] <= $duration) {
                $activeDiscount = $discount;
            }
        }

        if (!empty($activeDiscount['dicountPercent'])) {
            $dicountPrice    = $basePrice / 100 * (100 - $activeDiscount['dicountPercent']);
        } elseif (!empty($activeDiscount['dicountAbsolute'])) {
            $dicountPrice    = $basePrice - $activeDiscount['dicountAbsolute'];
        } else {
            throw new Exception('Price not found');
        }

        $data = array(
            'sessionID' => Shopware()->SessionID(),
            'articlename' => $variant->getNumber() . ' ABO_DISCOUNT',
            'articleID' => 0,
            'ordernumber' => $aboArticle['ordernumber'],
            'shippingfree' => 0,
            'quantity' => 1,
            'price' => $dicountPrice * -1,
            'netprice' => $dicountPrice * -1,
            'datum' => 'NOW()',
            'modus' => 10,
            'currencyFactor' => Shopware()->Shop()->getCurrency()->getFactor()
        );

        $this->getDatabase()->insert('s_order_basket', $data);

        $basketId = $this->getDatabase()->lastInsertId('s_order_basket');

        $data = array(
            'basketID' => $basketId,
            'swag_abo_commerce_id' => $basketItem['id'],
        );

        $this->getDatabase()->insert('s_order_basket_attributes', $data);
    }

    /**
     * @param int $articleId
     * @return array
     */
    public function getAboCommerceDataByIdArticleId($articleId)
    {
        $articleData = $this->getAboCommerceRepository()
                ->getDetailQueryBuilder($articleId)
                ->getQuery()
                ->getOneOrNullResult(\Doctrine\ORM\AbstractQuery::HYDRATE_ARRAY);


        if (empty($articleData)) {
            return array();
        }

        $article = $this->getArticleRepository()->find($articleId);
        $basePrice = $article->getMainDetail()->getPrices()->first()->getPrice();

        $aboCommerceData = array(
            'maxQuantityPerWeek' => $articleData['maxUnitsPerWeek'],
            'isExclusive'        => $articleData['exclusive'],
            'isActive'           => $articleData['active'],
            'prices'             => array(),

            'deliveryIntervalUnit'  => $articleData['deliveryIntervalUnit'],
            'minDeliveryInterval'   => $articleData['minDeliveryInterval'],
            'maxDeliveryInterval'   => $articleData['maxDeliveryInterval'],

            'durationUnit'       => $articleData['durationUnit'],
            'minDuration'        => $articleData['minDuration'],
            'maxDuration'        => $articleData['maxDuration'],

            'description'        => $articleData['description'],
        );

        foreach ($articleData['prices'] as $discount) {
            $dicountAbsolute = 0;
            $dicountPercent  = 0;
            $dicountPrice    = 0;

            if (!empty($discount['dicountPercent'])) {
                $dicountPrice    = $basePrice / 100 * (100 - $discount['dicountPercent']);
                $dicountAbsolute = $basePrice / 100 * $discount['dicountPercent'];
                $dicountPercent  = $discount['dicountPercent'];
            } elseif (!empty($discount['dicountAbsolute'])) {
                $dicountPrice    = $basePrice - $discount['dicountAbsolute'];
                $dicountAbsolute = $discount['dicountAbsolute'];
                $dicountPercent  = $discount['dicountAbsolute'] * 100 / $basePrice;
            }

            $aboCommerceData['prices'][] = array(
                'duration'           => $discount['durationFrom'],
                'discountPrice'      => $dicountPrice,
                'discountAbsolute'   => $dicountAbsolute,
                'descountPercentage' => $dicountPercent
            );
        }

        return $aboCommerceData;
    }

    /**
     * Search an article variant (\Shopware\Models\Article\Detail) with the passed
     * article order number and returns it.
     *
     * @param $orderNumber
     * @return null|\Shopware\Models\Article\Detail
     */
    public function getVariantByOrderNumber($orderNumber)
    {
        $builder = $this->getEntityManager()->createQueryBuilder();
        $builder->select(array('variant', 'article'))
                ->from('Shopware\Models\Article\Detail', 'variant')
                ->innerJoin('variant.article', 'article')
                ->where('variant.number = :orderNumber')
                ->andWhere('variant.active = :active')
                ->andWhere('article.active = :active')
                ->setFirstResult(0)
                ->setMaxResults(1)
                ->setParameters(array('orderNumber' => $orderNumber, 'active' => true));

        return $builder->getQuery()->getOneOrNullResult(
            \Doctrine\ORM\AbstractQuery::HYDRATE_OBJECT
        );
    }

    /**
     * @throws Exception
     */
    public function updateBasketDiscount()
    {
        $this->getEntityManager()->clear();
        $builder = Shopware()->Models()->createQueryBuilder();
        $builder->select(array('basket', 'attribute'))
                ->from('Shopware\Models\Order\Basket', 'basket')
                ->innerJoin('basket.attribute', 'attribute')
                ->where('basket.mode = :mode')
                ->andWhere('basket.sessionId = :sessionId')
                ->andWhere('attribute.swagAboCommerceId IS NOT NULL')
                ->setParameters(array('mode' => 10, 'sessionId' => $this->getSessionId()));

        $discountBasketItems = $builder->getQuery()->getResult();

        /** @var $discountBasketItem \Shopware\Models\Order\Basket */
        foreach ($discountBasketItems as $discountBasketItem) {
            $aboBasketItemId = $discountBasketItem->getAttribute()->getSwagAboCommerceId();
            $aboBasketItem   = $this->getBasketComponent()
                                    ->getItem($aboBasketItemId, \Doctrine\ORM\AbstractQuery::HYDRATE_OBJECT);

            if ($aboBasketItem === null) {
                $this->getBasketComponent()->removeItem($discountBasketItem);
                continue;
            }

            $quantity = $aboBasketItem->getQuantity();
            $price    = $aboBasketItem->getPrice();

            $aboArticleId = $aboBasketItem->getArticleId();
            $aboArticle = $this->getAboCommerceRepository()->findOneBy(array('articleId' => $aboArticleId));

            if (empty($aboArticle)) {
                throw new \Exception("Article not found");
            }

            $discount = ($price * $quantity) / 100 * 10 * -1;

            $discountBasketItem->setPrice($discount);
            $this->getEntityManager()->flush($discountBasketItem);
            continue;

            $netPrice = $discountBasketItem->getNetPrice();

            if ($this->useNetPrices()) {
                $basketRow->setPrice($netPrice);
            } else {
                $grossPrice = $netPrice / 100 * (100 + $aboArticle->getArticle()->getTax()->getTax());
                $basketRow->setPrice($grossPrice);
            }

            $this->getEntityManager()->flush($basketRow);
        }
    }

    /**
     * Global interface to check if the passed variant number is already as bundle article position
     * in the basket.
     */
    public function isAboCommerceArticleInBasket()
    {
        $builder = $this->getEntityManager()->createQueryBuilder();
        $builder->select($this->getEntityManager()->getExpressionBuilder()->count('basket.id'))
                ->from('Shopware\Models\Order\Basket', 'basket')
                ->innerJoin('basket.attribute', 'attribute')
                ->where('basket.sessionId = :sessionId')
                ->andWhere('attribute.swagAboCommerceDeliveryInterval IS NOT NULL')
                ->setParameters(array('sessionId' => Shopware()->SessionID()));

        $count = $builder->getQuery()->getSingleScalarResult();

        return (bool) $count;
    }

    /**
     * Global interface to check if the passed variant number is already as bundle article position
     * in the basket.
     */
    public function isStandardArticleInBasket()
    {
        $builder = $this->getEntityManager()->createQueryBuilder();
        $builder->select($this->getEntityManager()->getExpressionBuilder()->count('basket.id'))
                ->from('Shopware\Models\Order\Basket', 'basket')
                ->leftJoin('basket.attribute', 'attribute')
                ->where('basket.sessionId = :sessionId')
                ->andWhere('attribute.swagAboCommerceDeliveryInterval IS NULL OR attribute.swagAboCommerceDeliveryInterval = 0')
                ->andWhere('attribute.swagAboCommerceId IS NULL OR attribute.swagAboCommerceId = 0')
                ->setParameters(array('sessionId' => Shopware()->SessionID()));

        $count = $builder->getQuery()->getSingleScalarResult();

        return (bool) $count;
    }

    /**
     * Global interface to check if the passed variant number is already as bundle article position
     * in the basket.
     */
    public function isAboCommerceConfgigurationInBasket($ordernumber, $duration, $interval)
    {
        $builder = $this->getEntityManager()->createQueryBuilder();
        $builder->select(array('basket'))
                ->from('Shopware\Models\Order\Basket', 'basket')
                ->innerJoin('basket.attribute', 'attribute')
                ->where('basket.orderNumber LIKE :ordernumber')
                ->andWhere('basket.sessionId = :sessionId')
                ->andWhere('attribute.swagAboCommerceDuration = :duration')
                ->andWhere('attribute.swagAboCommerceDeliveryInterval = :interval')
                ->setFirstResult(0)
                ->setMaxResults(1)
                ->setParameters(array(
                    'ordernumber' => $ordernumber,
                    'sessionId'   => $this->getSessionId(),
                    'duration'    => $duration,
                    'interval'    => $interval,
                ));

        $basket = $builder->getQuery()->getOneOrNullResult(\Doctrine\ORM\AbstractQuery::HYDRATE_OBJECT);

        if ($basket instanceof \Shopware\Models\Order\Basket) {
            return $basket->getId();
        } else {
            return null;
        }
    }
}
