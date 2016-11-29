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
     * @var array
     */
    private $componentHandler = [];

    /**
     * @var EventComponentHandler
     */
    private $eventComponentHandler;

    /**
     * @var DataCollectionResolverInterface
     */
    private $dataCollectionResolver;

    /**
     * @param array $componentHandler
     * @param EmotionElementGateway $gateway
     * @param EventComponentHandler $eventComponentHandler
     * @param DataCollectionResolverInterface $dataCollectionResolver
     */
    public function __construct(array $componentHandler = [], EmotionElementGateway $gateway, EventComponentHandler $eventComponentHandler, DataCollectionResolverInterface $dataCollectionResolver)
    {
        $this->gateway = $gateway;
        $this->eventComponentHandler = $eventComponentHandler;
        $this->componentHandler = $componentHandler;
        $this->dataCollectionResolver = $dataCollectionResolver;
    }

    /**
     * @param int[] $emotionIds
     * @param ShopContextInterface $context
     * @return \Shopware\Bundle\EmotionBundle\Struct\Emotion[]
     */
    public function getList(array $emotionIds, ShopContextInterface $context)
    {
        $elements = $this->gateway->getList($emotionIds, $context);

        $this->handleElements($elements, $context);

        return $elements;
    }

    /**
     * @param array $elementList
     * @param ShopContextInterface $context
     */
    private function handleElements(array $elementList, ShopContextInterface $context)
    {
        $prepareCollection = new PrepareDataCollection();

        /**
         * @var int $emotionId
         * @var Element[] $elements
         */
        foreach ($elementList as $emotionId => $elements) {
            foreach ($elements as $element) {
                $this->prepareElement($prepareCollection, $element, $context);
            }
        }

        $resolvedCollection = $this->dataCollectionResolver->resolve($prepareCollection, $context);

        /**
         * @var int $emotionId
         * @var Element[] $elements
         */
        foreach ($elementList as $emotionId => $elements) {
            foreach ($elements as $element) {
                $this->handleElement($resolvedCollection, $element, $context);
            }
        }
    }

    /**
     * @param PrepareDataCollection $collection
     * @param Element $element
     * @param ShopContextInterface $context
     */
    private function prepareElement(PrepareDataCollection $collection, Element $element, ShopContextInterface $context)
    {
        $handler = $this->findElementHandler($element);
        $handler->prepare($collection, $element, $context);
    }

    /**
     * @param ResolvedDataCollection $collection
     * @param Element $element
     * @param ShopContextInterface $context
     */
    private function handleElement(ResolvedDataCollection $collection, Element $element, ShopContextInterface $context)
    {
        $handler = $this->findElementHandler($element);
        $handler->handle($collection, $element, $context);
    }

    /**
     * @param Element $element
     * @return mixed|null
     */
    private function findElementHandler(Element $element)
    {
        foreach ($this->componentHandler as $handler) {
            if ($handler->supports($element)) {
                return $handler;
            }
        }

        return $this->eventComponentHandler;
    }
}
