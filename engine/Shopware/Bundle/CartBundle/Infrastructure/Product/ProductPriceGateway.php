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
use Shopware\Bundle\CartBundle\Domain\LineItem\LineItemCollection;
use Shopware\Bundle\CartBundle\Domain\LineItem\LineItemInterface;
use Shopware\Bundle\CartBundle\Domain\Price\PriceDefinition;
use Shopware\Bundle\CartBundle\Domain\Price\PriceDefinitionCollection;
use Shopware\Bundle\CartBundle\Domain\Product\ProductPriceGatewayInterface;
use Shopware\Bundle\CartBundle\Domain\Tax\TaxRule;
use Shopware\Bundle\CartBundle\Domain\Tax\TaxRuleCollection;
use Shopware\Bundle\StoreFrontBundle\Gateway\FieldHelper;
use Shopware\Bundle\StoreFrontBundle\Struct\ShopContextInterface;

class ProductPriceGateway implements ProductPriceGatewayInterface
{
    /**
     * @var Connection
     */
    private $connection;

    /**
     * @var FieldHelper
     */
    private $fieldHelper;

    /**
     * @param Connection  $connection
     * @param FieldHelper $fieldHelper
     */
    public function __construct(
        Connection $connection,
        FieldHelper $fieldHelper
    ) {
        $this->connection = $connection;
        $this->fieldHelper = $fieldHelper;
    }

    public function get(LineItemCollection $collection, ShopContextInterface $context): PriceDefinitionCollection
    {
        $query = $this->buildQuery($collection->getIdentifiers(), $context);

        $data = $query->execute()->fetchAll(\PDO::FETCH_GROUP);
        $prices = new PriceDefinitionCollection();

        /** @var LineItemInterface $lineItem */
        foreach ($collection as $lineItem) {
            $number = $lineItem->getIdentifier();

            if (!array_key_exists($number, $data)) {
                continue;
            }

            $price = $this->findCustomerGroupPrice(
                $lineItem->getQuantity(),
                $data[$lineItem->getIdentifier()],
                $context->getCurrentCustomerGroup()->getKey(),
                $context->getFallbackCustomerGroup()->getKey()
            );

            if (!$price) {
                continue;
            }

            $taxRule = new TaxRule((float) $price['__tax_tax']);

            $prices->add(
                $number,
                new PriceDefinition(
                    (float) $price['price_net'],
                    new TaxRuleCollection([$taxRule]),
                    $lineItem->getQuantity()
                )
            );
        }

        return $prices;
    }

    private function findCustomerGroupPrice(int $quantity, array $prices, string $currentKey, string $fallbackKey): array
    {
        $filtered = $this->filterCustomerGroupPrices($prices, $currentKey);
        if (0 === count($filtered)) {
            $filtered = $this->filterCustomerGroupPrices($prices, $fallbackKey);
        }

        return $this->getQuantityPrice($filtered, $quantity);
    }

    private function filterCustomerGroupPrices(array $prices, string $key): array
    {
        return array_filter($prices, function ($price) use ($key) {
            return $price['price_customer_group_key'] === $key;
        });
    }

    private function getQuantityPrice(array $prices, int $quantity): ? array
    {
        foreach ($prices as $price) {
            $to = (float) $price['price_to_quantity'];
            $from = $price['price_from_quantity'];

            if ($from <= $quantity && $to >= $quantity) {
                return $price;
            }

            if ($from <= $quantity && (int) $to === 0) {
                return $price;
            }
        }

        return null;
    }

    private function buildQuery(array $numbers, ShopContextInterface $context): QueryBuilder
    {
        $query = $this->connection->createQueryBuilder();

        $query->select('variant.ordernumber as arrayKey');

        $query->addSelect([
            'price.pricegroup as price_customer_group_key',
            'price.from as price_from_quantity',
            'price.to as price_to_quantity',
            'price.price as price_net',
        ]);

        $query->addSelect($this->fieldHelper->getTaxFields());

        $query->from('s_articles_prices', 'price');
        $query->innerJoin('price', 's_articles_details', 'variant', 'variant.id = price.articledetailsID');
        $query->innerJoin('variant', 's_articles', 'product', 'product.id = variant.articleID');
        $query->innerJoin('variant', 's_core_tax', 'tax', 'tax.id = product.taxID');
        $query->where('variant.ordernumber IN (:numbers)');
        $query->setParameter(':numbers', $numbers, Connection::PARAM_STR_ARRAY);

        $customerGroups = array_unique([
            $context->getCurrentCustomerGroup()->getKey(),
            $context->getFallbackCustomerGroup()->getKey(),
        ]);
        $query->andWhere('price.pricegroup IN (:customerGroups)');
        $query->setParameter(':customerGroups', $customerGroups, Connection::PARAM_STR_ARRAY);

        return $query;
    }
}
