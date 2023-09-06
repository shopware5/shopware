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

namespace Shopware\Bundle\SearchBundleDBAL;

use Doctrine\Common\Collections\ArrayCollection;
use Enlight\Event\SubscriberInterface;
use RuntimeException;
use Shopware\Bundle\SearchBundle\CriteriaRequestHandlerInterface;

/**
 * @deprecated - Not used. The events are emitted in other classes. This class will be removed without replacement in 5.8
 */
class SearchBundleDBALSubscriber implements SubscriberInterface
{
    /**
     * @var ArrayCollection<SortingHandlerInterface>
     */
    private $sortingHandlers;

    /**
     * @var ArrayCollection<ConditionHandlerInterface>
     */
    private $conditionHandlers;

    /**
     * @var ArrayCollection<FacetHandlerInterface|PartialFacetHandlerInterface>
     */
    private $facetHandlers;

    /**
     * @var ArrayCollection<CriteriaRequestHandlerInterface>
     */
    private $criteriaRequestHandlers;

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
     * {@inheritdoc}
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
     * @return ArrayCollection<FacetHandlerInterface|PartialFacetHandlerInterface>
     */
    public function registerFacetHandlers()
    {
        return $this->facetHandlers;
    }

    /**
     * @return ArrayCollection<SortingHandlerInterface>
     */
    public function registerSortingHandlers()
    {
        return $this->sortingHandlers;
    }

    /**
     * @return ArrayCollection<ConditionHandlerInterface>
     */
    public function registerConditionHandlers()
    {
        return $this->conditionHandlers;
    }

    /**
     * @return ArrayCollection<CriteriaRequestHandlerInterface>
     */
    public function registerRequestHandlers()
    {
        return $this->criteriaRequestHandlers;
    }

    private function validateHandlers(array $handlers)
    {
        if (empty($handlers)) {
            throw new RuntimeException(sprintf('No handlers provided in %s', __CLASS__));
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

            throw new RuntimeException(sprintf('Unknown handler class %s detected', \is_object($handler) ? \get_class($handler) : \gettype($handler)));
        }
    }

    /**
     * @param string $class
     *
     * @return ArrayCollection
     */
    private function getHandlersByClass(array $handlers, $class)
    {
        $elements = array_filter(
            $handlers,
            function ($handler) use ($class) {
                return $handler instanceof $class;
            }
        );

        return new ArrayCollection(array_values($elements));
    }
}
