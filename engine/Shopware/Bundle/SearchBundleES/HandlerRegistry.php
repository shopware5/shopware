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

namespace Shopware\Bundle\SearchBundleES;

use Doctrine\Common\Collections\ArrayCollection;
use Enlight_Event_EventManager;
use IteratorAggregate;
use RuntimeException;
use Shopware\Bundle\SearchBundle\CriteriaPartInterface;

class HandlerRegistry
{
    /**
     * @var HandlerInterface[]
     */
    private $handlers;

    public function __construct(
        IteratorAggregate $handlers,
        Enlight_Event_EventManager $events
    ) {
        $eventHandlers = $events->collect(
            'Shopware_SearchBundleES_Collect_Handlers',
            new ArrayCollection()
        );

        $this->handlers = array_merge($eventHandlers->toArray(), iterator_to_array($handlers, false));
    }

    public function getHandler(CriteriaPartInterface $condition)
    {
        foreach ($this->handlers as $handler) {
            if ($handler->supports($condition)) {
                return $handler;
            }
        }

        throw new RuntimeException(sprintf('%s class not supported', \get_class($condition)));
    }

    /**
     * @return HandlerInterface[]
     */
    public function getHandlers(): iterable
    {
        return $this->handlers;
    }
}
