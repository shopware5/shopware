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

namespace Shopware\Bundle\SearchBundleDBAL;

use Doctrine\Common\Collections\ArrayCollection;
use Enlight\Event\SubscriberInterface;
use Shopware\Bundle\SearchBundle\CriteriaRequestHandlerInterface;

/**
 * @category  Shopware
 * @package   Shopware\Bundle\SearchBundleDBAL
 * @copyright Copyright (c) shopware AG (http://www.shopware.de)
 */
class SearchBundleDBALSubscriber implements SubscriberInterface
{
    /**
     * @var SortingHandlerInterface[]
     */
    private $sortingHandlers = [];

    /**
     * @var ConditionHandlerInterface[]
     */
    private $conditionHandlers = [];

    /**
     * @var FacetHandlerInterface[]|PartialFacetHandlerInterface[]
     */
    private $facetHandlers = [];

    /**
     * @var CriteriaRequestHandlerInterface[]
     */
    private $criteriaRequestHandlers = [];

    /**
     * @param array $handlers contains different CriteriaRequestHandlerInterface, SortingHandlerInterface, ConditionHandlerInterface and FacetHandlerInterface
     */
    public function __construct(array $handlers)
    {
        $this->validateHandlers($handlers);

        $this->sortingHandlers = $this->getHandlersByClass($handlers, SortingHandlerInterface::class);
        $this->conditionHandlers = $this->getHandlersByClass($handlers, ConditionHandlerInterface::class);
        $this->criteriaRequestHandlers = $this->getHandlersByClass($handlers, CriteriaRequestHandlerInterface::class);

        $this->facetHandlers = $this->getHandlersByClass($handlers, FacetHandlerInterface::class);
        $partial = $this->getHandlersByClass($handlers, PartialFacetHandlerInterface::class);
        foreach ($partial as $handler) {
            $this->facetHandlers->add($handler);
        }
    }

    /**
     * @inheritdoc
     */
    public static function getSubscribedEvents()
    {
        return [
            'Shopware_SearchBundleDBAL_Collect_Facet_Handlers' => 'registerFacetHandlers',
            'Shopware_SearchBundleDBAL_Collect_Sorting_Handlers' => 'registerSortingHandlers',
            'Shopware_SearchBundleDBAL_Collect_Condition_Handlers' => 'registerConditionHandlers',
            'Shopware_SearchBundle_Collect_Criteria_Request_Handlers' => 'registerRequestHandlers',
        ];
    }

    /**
     * @return FacetHandlerInterface[]
     */
    public function registerFacetHandlers()
    {
        return $this->facetHandlers;
    }

    /**
     * @return SortingHandlerInterface[]
     */
    public function registerSortingHandlers()
    {
        return $this->sortingHandlers;
    }

    /**
     * @return ConditionHandlerInterface[]
     */
    public function registerConditionHandlers()
    {
        return $this->conditionHandlers;
    }

    /**
     * @return CriteriaRequestHandlerInterface[]
     */
    public function registerRequestHandlers()
    {
        return $this->criteriaRequestHandlers;
    }

    /**
     * @param array $handlers
     */
    private function validateHandlers(array $handlers)
    {
        if (empty($handlers)) {
            throw new \RuntimeException('No handlers provided in \Shopware\Bundle\SearchBundleDBAL\SearchBundleDBALSubscriber');
        }

        foreach ($handlers as $handler) {
            if ($handler instanceof SortingHandlerInterface
                || $handler instanceof ConditionHandlerInterface
                || $handler instanceof FacetHandlerInterface
                || $handler instanceof PartialFacetHandlerInterface
                || $handler instanceof CriteriaRequestHandlerInterface
            ) {
                continue;
            }

            throw new \RuntimeException(
                sprintf('Unknown handler class %s detected', get_class($handler))
            );
        }
    }

    /**
     * @param array $handlers
     * @param string $class
     * @return ArrayCollection
     */
    private function getHandlersByClass(array $handlers, $class)
    {
        $elements = array_filter(
            $handlers,
            function ($handler) use ($class) {
                return ($handler instanceof $class);
            }
        );

        return new ArrayCollection(array_values($elements));
    }
}
