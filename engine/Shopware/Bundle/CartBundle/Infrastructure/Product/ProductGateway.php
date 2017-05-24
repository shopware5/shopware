<?php
declare(strict_types=1);
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

namespace Shopware\Bundle\CartBundle\Infrastructure\Product;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Query\QueryBuilder;
use Shopware\Bundle\CartBundle\Domain\Delivery\DeliveryDate;
use Shopware\Bundle\CartBundle\Domain\Delivery\DeliveryInformation;
use Shopware\Bundle\CartBundle\Domain\Product\ProductData;
use Shopware\Bundle\CartBundle\Domain\Product\ProductDataCollection;
use Shopware\Bundle\CartBundle\Domain\Product\ProductGatewayInterface;
use Shopware\Bundle\CartBundle\Domain\Rule\Container\OrRule;
use Shopware\Bundle\CartBundle\Domain\Rule\Rule;
use Shopware\Bundle\CartBundle\Infrastructure\Rule\CustomerGroupRule;
use Shopware\Bundle\CartBundle\Infrastructure\Rule\ShopRule;
use Shopware\Bundle\StoreFrontBundle\Context\ShopContextInterface;

class ProductGateway implements ProductGatewayInterface
{
    /**
     * @var ProductPriceGatewayInterface
     */
    private $priceGateway;

    /**
     * @param ProductPriceGatewayInterface $priceGateway
     */
    public function __construct(ProductPriceGatewayInterface $priceGateway)
    {
        $this->priceGateway = $priceGateway;
    }

    public function get(array $numbers, ShopContextInterface $context): ProductDataCollection
    {
        $prices = $this->priceGateway->get($numbers, $context);

        $details = $this->getDetails($numbers, $context);

        $productCollection = new ProductDataCollection();

        foreach ($numbers as $number) {
            if (!$prices->has($number)) {
                continue;
            }

            if (!array_key_exists($number, $details)) {
                continue;
            }

            $deliveryInformation = $this->buildDeliveryInformation($details[$number]);

            $rule = $this->buildRule($details[$number]);

            $productCollection->add(
                new ProductData($number, $prices->get($number), $deliveryInformation, $rule)
            );
        }

        return $productCollection;
    }

    private function getDetails(array $numbers, ShopContextInterface $context): array
    {
        /** @var QueryBuilder $query */
        $query = Shopware()->Container()->get('dbal_connection')->createQueryBuilder();

        $query->select([
            'variant.ordernumber',
            'variant.instock',
            'variant.weight',
            'variant.width',
            'variant.height',
            'variant.length',
            'variant.shippingtime',
            "GROUP_CONCAT(DISTINCT customerGroups.customergroupId SEPARATOR '|') as blocked_groups",
            "GROUP_CONCAT(DISTINCT shop.id SEPARATOR '|') AS allowed_shops",
            'article.laststock as closeout',
        ]);
        $query->from('s_articles_details', 'variant');
        $query->innerJoin('variant', 's_articles', 'article', 'article.id = variant.articleID');
        $query->leftJoin('variant', 's_articles_avoid_customergroups', 'customerGroups', 'customerGroups.articleID = variant.articleID');
        $query->leftJoin('variant', 's_articles_categories_ro', 'categories_ro', 'categories_ro.articleID = variant.articleID');
        $query->leftJoin('categories_ro', 's_core_shops', 'shop', 'shop.category_id = categories_ro.categoryID');
        $query->groupBy('variant.id');

        $query->where('variant.ordernumber IN (:numbers)');
        $query->setParameter(':numbers', $numbers, Connection::PARAM_STR_ARRAY);

        return $query->execute()->fetchAll(\PDO::FETCH_GROUP | \PDO::FETCH_UNIQUE);
    }

    private function buildDeliveryInformation(array $row)
    {
        $earliestInterval = new \DateInterval('P1D');
        $deliveryTimeInterval = new \DateInterval('P3D');
        $delayInterval = new \DateInterval('P10D');

        return new DeliveryInformation(
            (int) $row['instock'],
            (float) $row['height'],
            (float) $row['width'],
            (float) $row['length'],
            (float) $row['weight'],
            new DeliveryDate(
                (new \DateTime())
                    ->add($earliestInterval),
                (new \DateTime())
                    ->add($earliestInterval)
                    ->add($deliveryTimeInterval)
            ),
            new DeliveryDate(
                (new \DateTime())
                    ->add($delayInterval)
                    ->add($earliestInterval),
                (new \DateTime())
                    ->add($delayInterval)
                    ->add($earliestInterval)
                    ->add($deliveryTimeInterval)
            )
        );
    }

    /**
     * @param array $row
     *
     * @return Rule
     */
    private function buildRule(array $row): Rule
    {
        $rule = new OrRule();

        if (!empty($row['blocked_groups'])) {
            $ids = array_filter(explode('|', $row['blocked_groups']));
            $ids = array_map(function ($id) {
                return (int) $id;
            }, $ids);

            $rule->addRule(new CustomerGroupRule($ids));
        }

        if ($row['allowed_shops']) {
            $ids = array_filter(explode('|', $row['allowed_shops']));
            $ids = array_map(function ($id) {
                return (int) $id;
            }, $ids);

            $rule->addRule(new ShopRule($ids, Rule::OPERATOR_NEQ));
        }

        return $rule;
    }
}
