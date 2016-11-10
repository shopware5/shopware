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

namespace Shopware\Bundle\EmotionBundle\ComponentHandler;

use Shopware\Bundle\EmotionBundle\Struct\Collection\PrepareDataCollection;
use Shopware\Bundle\EmotionBundle\Struct\Collection\ResolvedDataCollection;
use Shopware\Bundle\EmotionBundle\Struct\Element;
use Shopware\Bundle\StoreFrontBundle\Struct\ShopContextInterface;

/**
 * Class EventComponentHandler
 * @package Shopware\Bundle\EmotionBundle\ComponentHandler
 * @deprecated since 5.3, removed in 5.5. This is a legacy layer which supports an deprecated event. Implement a ComponentHandler instead.
 */
class EventComponentHandler implements ComponentHandlerInterface
{
    /**
     * @var \Enlight_Event_EventManager
     */
    private $eventManager;

    /**
     * @param \Enlight_Event_EventManager $eventManager
     */
    public function __construct(\Enlight_Event_EventManager $eventManager)
    {
        $this->eventManager = $eventManager;
    }

    /**
     * @param Element $element
     * @return boolean
     */
    public function supports(Element $element)
    {
        return false;
    }

    /**
     * @param PrepareDataCollection $collection
     * @param Element $element
     * @param ShopContextInterface $context
     */
    public function prepare(PrepareDataCollection $collection, Element $element, ShopContextInterface $context)
    {
    }

    /**
     * @param ResolvedDataCollection $collection
     * @param Element $element
     * @param ShopContextInterface $context
     */
    public function handle(ResolvedDataCollection $collection, Element $element, ShopContextInterface $context)
    {
        $elementData = json_decode(json_encode($element), true);
        $elementData['component']['xType'] = $element->getComponent()->getType();
        $elementData['component']['cls'] = $element->getComponent()->getCssClass();

        $data = array_merge($element->getConfig()->getAll(), $element->getData()->getAll());
        $data['objectId'] = md5($element->getId());

        $data = $this->eventManager->filter('Shopware_Controllers_Widgets_Emotion_AddElement', $data, ['element' => $elementData]);

        foreach ($data as $key => $value) {
            $element->getData()->set($key, $value);
        }
    }
}
