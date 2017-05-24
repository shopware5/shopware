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

namespace Shopware\Bundle\CartBundle\Infrastructure\Rule\Collector;

use Doctrine\DBAL\Connection;
use Shopware\Bundle\CartBundle\Domain\Cart\CartContainer;
use Shopware\Bundle\CartBundle\Domain\Cart\CollectorInterface;
use Shopware\Bundle\CartBundle\Domain\Product\ProductFetchDefinition;
use Shopware\Bundle\CartBundle\Domain\Rule\ProductOfManufacturerRule;
use Shopware\Bundle\CartBundle\Domain\Rule\RuleCollection;
use Shopware\Bundle\CartBundle\Domain\Rule\Validatable;
use Shopware\Bundle\CartBundle\Infrastructure\Rule\Data\ProductOfManufacturerRuleData;
use Shopware\Bundle\StoreFrontBundle\Common\StructCollection;
use Shopware\Bundle\StoreFrontBundle\Context\ShopContextInterface;

class ProductOfManufacturersRuleCollector implements CollectorInterface
{
    /**
     * @var Connection
     */
    private $connection;

    /**
     * @param Connection $connection
     */
    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    public function prepare(
        StructCollection $fetchDefinition,
        CartContainer $cartContainer,
        ShopContextInterface $context
    ): void {
    }

    public function fetch(
        StructCollection $dataCollection,
        StructCollection $fetchCollection,
        ShopContextInterface $context
    ): void {
        $rules = $dataCollection->filterInstance(Validatable::class);
        $rules = $rules->map(function (Validatable $validatable) {
            return $validatable->getRule();
        });

        $rules = new RuleCollection($rules);

        $categoryRules = $rules->filterInstance(ProductOfManufacturerRule::class);

        if ($categoryRules->count() === 0) {
            return;
        }

        $numbers = $this->getNumbers($fetchCollection);

        if (empty($numbers)) {
            return;
        }

        $categories = $this->fetchManufacturerIds($numbers);

        $dataCollection->add(
            new ProductOfManufacturerRuleData($categories),
            ProductOfManufacturerRuleData::class
        );
    }

    private function getNumbers(StructCollection $fetchDefinition): array
    {
        $definitions = $fetchDefinition->filterInstance(ProductFetchDefinition::class);
        if (0 === $definitions->count()) {
            return [];
        }

        $numbers = [];

        /** @var ProductFetchDefinition $definition */
        foreach ($definitions as $definition) {
            $numbers = array_merge($numbers, $definition->getNumbers());
        }

        //fast array unique
        return array_keys(array_flip($numbers));
    }

    private function fetchManufacturerIds($numbers)
    {
        $query = $this->connection->createQueryBuilder();

        $query->select(['variant.ordernumber', 'product.supplierID']);
        $query->innerJoin('variant', 's_articles', 'product', 'product.id = variant.articleID');
        $query->from('s_articles_details', 'variant');
        $query->andWhere('variant.ordernumber IN (:numbers)');
        $query->setParameter(':numbers', $numbers, Connection::PARAM_STR_ARRAY);

        return $query->execute()->fetchAll(\PDO::FETCH_KEY_PAIR);
    }
}
