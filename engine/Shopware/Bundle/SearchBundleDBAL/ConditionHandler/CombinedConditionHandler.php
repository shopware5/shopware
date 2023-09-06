<?php
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

namespace Shopware\Bundle\SearchBundleDBAL\ConditionHandler;

use RuntimeException;
use Shopware\Bundle\SearchBundle\Condition\CombinedCondition;
use Shopware\Bundle\SearchBundle\ConditionInterface;
use Shopware\Bundle\SearchBundleDBAL\ConditionHandlerInterface;
use Shopware\Bundle\SearchBundleDBAL\QueryBuilder;
use Shopware\Bundle\SearchBundleDBAL\QueryBuilderFactory;
use Shopware\Bundle\StoreFrontBundle\Struct\ShopContextInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class CombinedConditionHandler implements ConditionHandlerInterface
{
    private ContainerInterface $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsCondition(ConditionInterface $condition)
    {
        return $condition instanceof CombinedCondition;
    }

    /**
     * {@inheritdoc}
     */
    public function generateCondition(
        ConditionInterface $condition,
        QueryBuilder $query,
        ShopContextInterface $context
    ) {
        $query->addState($condition->getName());

        $this->addConditions($condition, $query, $context);
    }

    private function addConditions(
        CombinedCondition $condition,
        QueryBuilder $query,
        ShopContextInterface $context
    ): void {
        foreach ($condition->getConditions() as $innerCondition) {
            $handler = $this->getConditionHandler($innerCondition);
            $handler->generateCondition($innerCondition, $query, $context);
        }
    }

    /**
     * @throws RuntimeException
     */
    private function getConditionHandler(ConditionInterface $condition): ConditionHandlerInterface
    {
        // Initialize the condition handler collection service
        $this->container->get(QueryBuilderFactory::class);

        $handlers = $this->container->get('shopware_searchdbal.condition_handlers');

        foreach ($handlers as $handler) {
            if ($handler->supportsCondition($condition)) {
                return $handler;
            }
        }

        throw new RuntimeException(sprintf('Condition %s not supported', \get_class($condition)));
    }
}
