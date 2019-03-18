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

namespace Shopware\Bundle\CustomerSearchBundleDBAL;

use IteratorAggregate;
use Shopware\Bundle\SearchBundle\ConditionInterface;
use Shopware\Bundle\SearchBundle\SortingInterface;

class HandlerRegistry
{
    /**
     * @var ConditionHandlerInterface[]
     */
    private $conditionHandlers;

    /**
     * @var SortingHandlerInterface[]
     */
    private $sortingHandlers;

    public function __construct(
        IteratorAggregate $conditionHandlers,
        IteratorAggregate $sortingHandlers
    ) {
        $this->conditionHandlers = iterator_to_array($conditionHandlers, false);
        $this->sortingHandlers = iterator_to_array($sortingHandlers, false);
    }

    /**
     * @return ConditionHandlerInterface
     */
    public function getConditionHandler(ConditionInterface $condition)
    {
        foreach ($this->conditionHandlers as $handler) {
            if ($handler->supports($condition)) {
                return $handler;
            }
        }
        throw new \RuntimeException(sprintf('Condition class %s not supported', get_class($condition)));
    }

    /**
     * @return ConditionHandlerInterface[]
     */
    public function getConditionHandlers()
    {
        return $this->conditionHandlers;
    }

    /**
     * @return SortingHandlerInterface
     */
    public function getSortingHandler(SortingInterface $sorting)
    {
        foreach ($this->sortingHandlers as $handler) {
            if ($handler->supports($sorting)) {
                return $handler;
            }
        }
        throw new \RuntimeException(sprintf('Sorting class %s not supported', get_class($sorting)));
    }

    /**
     * @return SortingHandlerInterface[]
     */
    public function getSortingHandlers()
    {
        return $this->sortingHandlers;
    }
}
