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

namespace Shopware\Bundle\CartBundle\Infrastructure\Validator\Collector;

use Doctrine\DBAL\Connection;
use Shopware\Bundle\CartBundle\Domain\Cart\CalculatedCart;
use Shopware\Bundle\CartBundle\Domain\Validator\Collector\RuleDataCollectorInterface;
use Shopware\Bundle\CartBundle\Domain\Validator\Data\RuleDataCollection;
use Shopware\Bundle\CartBundle\Domain\Validator\Rule\RuleCollection;
use Shopware\Bundle\CartBundle\Infrastructure\Validator\Data\LastOrderRuleData;
use Shopware\Bundle\CartBundle\Infrastructure\Validator\Rule\LastOrderRule;
use Shopware\Bundle\StoreFrontBundle\Struct\ShopContextInterface;

class LastOrderRuleCollector implements RuleDataCollectorInterface
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

    public function collect(
        RuleCollection $rules,
        CalculatedCart $calculatedCart,
        ShopContextInterface $context,
        RuleDataCollection $collection
    ) {
        if (!$rules->has(LastOrderRule::class)) {
            return;
        }

        if (!$customer = $context->getCustomer()) {
            return;
        }

        $time = $this->connection->fetchColumn(
            'SELECT MAX(ordertime) FROM s_order WHERE userID = :userId',
            [':userId' => $customer->getId()]
        );

        if ($time) {
            $time = new \DateTime($time);
        }
        $collection->add(new LastOrderRuleData($time));
    }
}
