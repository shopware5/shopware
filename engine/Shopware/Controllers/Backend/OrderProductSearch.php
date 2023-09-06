<?php

declare(strict_types=1);
/**
 * Shopware 5
 * Copyright (c) shopware AG
 *
 * According to our licensing model, this program can be used
 * under the terms of the GNU Affero General Public License, version 3.
 *
 * The texts of the GNU Affero General Public License with an additional
 * permission can be found at and in the LICENSE file you have received
 * along with this program.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 * See the GNU Affero General Public License for more details.
 *
 * "Shopware" is a registered trademark of shopware AG.
 * The licensing of the program under the AGPLv3 does not imply a
 * trademark license. Therefore, any rights, title and interest in
 * our trademarks remain entirely with the shopware AG.
 */

namespace Shopware\Controllers\Backend;

use Doctrine\DBAL\Connection;
use RuntimeException;
use Shopware\Bundle\StoreFrontBundle\Struct\Tax as TaxStruct;
use Shopware\Components\Model\Exception\ModelNotFoundException;
use Shopware\Components\Model\ModelManager;
use Shopware\Models\Customer\Customer;
use Shopware\Models\Customer\Group;
use Shopware\Models\Order\Order;
use Shopware\Models\Order\Shipping;
use Shopware\Models\Shop\Shop;
use Shopware_Controllers_Backend_Base;

class OrderProductSearch extends Shopware_Controllers_Backend_Base
{
    private const DEFAULT_CUSTOMER_GROUP = 'EK';
    private const DEFAULT_PRODUCT_QUANTITY = 1;
    private const PLACEHOLDER_UP_TO = 'beliebig';

    public function getProductVariantsAction(): void
    {
        $orderId = (int) $this->Request()->getParam('orderId');
        if ($orderId === 0) {
            throw new RuntimeException('The parameter orderId is not set');
        }

        $order = $this->container->get(ModelManager::class)->find(Order::class, $orderId);
        if (!$order instanceof Order) {
            throw new ModelNotFoundException(Order::class, $orderId);
        }

        $customer = $order->getCustomer();
        if (!$customer instanceof Customer) {
            throw new ModelNotFoundException(Customer::class, $orderId);
        }

        $orderShippingAddress = $order->getShipping();
        if (!$orderShippingAddress instanceof Shipping) {
            throw new ModelNotFoundException(Shipping::class, $orderId);
        }

        $shop = $order->getShop();
        if (!$shop instanceof Shop) {
            throw new ModelNotFoundException(Shop::class, $orderId);
        }

        $customerGroup = $customer->getGroup() ?? $this->container->get(ModelManager::class)->getRepository(Group::class)->findOneBy(['key' => self::DEFAULT_CUSTOMER_GROUP]);
        if (!$customerGroup instanceof Group) {
            throw new ModelNotFoundException(Group::class, $orderId);
        }
        $customerGroupKey = $customerGroup->getKey();

        $area = $orderShippingAddress->getCountry()->getArea();
        $state = $orderShippingAddress->getState();
        $shopContext = $this->container->get('shopware_storefront.shop_context_factory')->create(
            $shop->getBaseUrl() ?? '',
            $shop->getId(),
            null,
            $customerGroupKey,
            $area ? $area->getId() : null,
            $orderShippingAddress->getCountry()->getId(),
            $state ? $state->getId() : null
        );

        $builder = $this->container->get(Connection::class)->createQueryBuilder();

        $fields = [
            'details.id',
            'product.name',
            'product.active',
            'product.taxId',
            'details.ordernumber',
            'product.id as articleId',
            'details.inStock',
            'supplier.name as supplierName',
            'supplier.id as supplierId',
            'details.additionalText',
            'COALESCE(customer_group_prices.price,default_prices.price) AS price',
        ];

        $builder->select($fields);
        $builder->from('s_articles_details', 'details');
        $builder->innerJoin('details', 's_articles', 'product', 'details.articleID = product.id');
        $builder->innerJoin('product', 's_articles_supplier', 'supplier', 'supplier.id = product.supplierID');
        $builder->leftJoin('details', 's_articles_prices', 'default_prices',
            'details.id = default_prices.articledetailsID
            AND default_prices.pricegroup = :defaultCustomerGroup
            AND (:quantity >= default_prices.from AND (default_prices.to = :placeholderUpTo OR :quantity <= default_prices.to))');
        $builder->leftJoin('details', 's_articles_prices', 'customer_group_prices',
            'details.id = customer_group_prices.articledetailsID
            AND customer_group_prices.pricegroup = :customerGroup
            AND (:quantity >= customer_group_prices.from AND (customer_group_prices.to = :placeholderUpTo OR :quantity <= customer_group_prices.to))');
        $builder->setParameters([
            'defaultCustomerGroup' => self::DEFAULT_CUSTOMER_GROUP,
            'customerGroup' => $customerGroupKey,
            'quantity' => self::DEFAULT_PRODUCT_QUANTITY,
            'placeholderUpTo' => self::PLACEHOLDER_UP_TO,
        ]);

        foreach ($this->Request()->getParam('filter', []) as $filter) {
            if ($filter['property'] === 'free') {
                $builder->andWhere(
                    $builder->expr()->or(
                        'details.ordernumber LIKE :free',
                        'product.name LIKE :free',
                        'supplier.name LIKE :free'
                    )
                );
                $builder->setParameter(':free', $filter['value']);
            }
        }

        $builder->setFirstResult($this->Request()->getParam('start'))
            ->setMaxResults($this->Request()->getParam('limit'))
            ->orderBy('details.id', 'ASC');

        $result = $builder->execute()->fetchAllAssociative();

        $total = (int) $builder->getConnection()->fetchOne('SELECT FOUND_ROWS()');

        $result = $this->addAdditionalTextForVariant($result);

        foreach ($result as $index => $variant) {
            $taxRule = $shopContext->getTaxRule($variant['taxId']);
            if ($taxRule instanceof TaxStruct) {
                $result[$index]['tax'] = (float) $taxRule->getTax();
            }

            $result[$index]['price'] = $customerGroup->getTax() === true ? round((float) $variant['price'] / 100 * (100 + $result[$index]['tax']), 2) : round((float) $variant['price'], 2);
        }

        $this->View()->assign(['success' => true, 'data' => $result, 'total' => $total]);
    }
}
