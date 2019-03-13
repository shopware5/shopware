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

namespace Shopware\Bundle\EmotionBundle\Service;

use Doctrine\Common\Collections\ArrayCollection;
use IteratorAggregate;
use Shopware\Bundle\EmotionBundle\ComponentHandler\ComponentHandlerInterface;
use Shopware\Bundle\EmotionBundle\ComponentHandler\EventComponentHandler;
use Shopware\Bundle\EmotionBundle\Service\Gateway\EmotionElementGateway;
use Shopware\Bundle\EmotionBundle\Struct\Collection\PrepareDataCollection;
use Shopware\Bundle\EmotionBundle\Struct\Collection\ResolvedDataCollection;
use Shopware\Bundle\EmotionBundle\Struct\Element;
use Shopware\Bundle\StoreFrontBundle\Struct\ShopContextInterface;

class EmotionElementService implements EmotionElementServiceInterface
{
    /**
     * @var EmotionElementGateway
     */
    private $gateway;

    /**
     * @var ComponentHandlerInterface[]
     */
    private $componentHandler;

    /**
     * @var EventComponentHandler
     */
    private $eventComponentHandler;

    /**
     * @var DataCollectionResolverInterface
     */
    private $dataCollectionResolver;

    /**
     * @var \Enlight_Event_EventManager
     */
    private $eventManager;

    public function __construct(
        IteratorAggregate $componentHandler,
        EmotionElementGateway $gateway,
        EventComponentHandler $eventComponentHandler,
        DataCollectionResolverInterface $dataCollectionResolver,
        \Enlight_Event_EventManager $eventManager
    ) {
        $this->gateway = $gateway;
        $this->eventComponentHandler = $eventComponentHandler;
        $this->dataCollectionResolver = $dataCollectionResolver;
        $this->eventManager = $eventManager;
        $this->componentHandler = $this->registerComponentHandlers(iterator_to_array($componentHandler, false));
    }

    /**
     * @param int[] $emotionIds
     *
     * @return \Shopware\Bundle\EmotionBundle\Struct\Emotion[]
     */
    public function getList(array $emotionIds, ShopContextInterface $context)
    {
        $elements = $this->gateway->getList($emotionIds, $context);

        $this->handleElements($elements, $context);

        return $elements;
    }

    private function handleElements(array $elementList, ShopContextInterface $context)
    {
        $prepareCollection = new PrepareDataCollection();

        /** @var int $emotionId */
        /** @var Element[] $elements */
        foreach ($elementList as $emotionId => $elements) {
            foreach ($elements as $element) {
                $this->prepareElement($prepareCollection, $element, $context);
            }
        }

        $resolvedCollection = $this->dataCollectionResolver->resolve($prepareCollection, $context);

        /** @var int $emotionId */
        /** @var Element[] $elements */
        foreach ($elementList as $emotionId => $elements) {
            foreach ($elements as $element) {
                $this->handleElement($resolvedCollection, $element, $context);
            }
        }
    }

    private function prepareElement(PrepareDataCollection $collection, Element $element, ShopContextInterface $context)
    {
        $handler = $this->findElementHandler($element);
        $handler->prepare($collection, $element, $context);
    }

    private function handleElement(ResolvedDataCollection $collection, Element $element, ShopContextInterface $context)
    {
        $handler = $this->findElementHandler($element);
        $handler->handle($collection, $element, $context);
    }

    /**
     * @return ComponentHandlerInterface
     */
    private function findElementHandler(Element $element)
    {
        /** @var ComponentHandlerInterface $handler */
        foreach ($this->componentHandler as $handler) {
            if ($handler->supports($element)) {
                return $handler;
            }
        }

        return $this->eventComponentHandler;
    }

    /**
     * @throws \Enlight_Event_Exception
     *
     * @return array
     */
    private function registerComponentHandlers(array $serviceComponentHandlers)
    {
        $componentHandlers = new ArrayCollection();
        $componentHandlers = $this->eventManager->collect(
            'Shopware_Emotion_Collect_Emotion_Component_Handlers',
            $componentHandlers
        );

        return array_merge($serviceComponentHandlers, $componentHandlers->toArray());
    }
}
