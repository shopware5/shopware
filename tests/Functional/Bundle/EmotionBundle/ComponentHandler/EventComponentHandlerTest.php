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

namespace Shopware\Tests\Functional\Bundle\EmotionBundle;

use Shopware\Bundle\EmotionBundle\Service\EmotionElementService;

class EventComponentHandlerTest extends \Enlight_Components_Test_TestCase
{
    public function testFallbackToEventComponentHandler()
    {
        $emotionElementIds = [1, 2, 5];

        $eventComponentHandlerMock =
            $this->getMockBuilder(\Shopware\Bundle\EmotionBundle\ComponentHandler\EventComponentHandler::class)
                ->disableOriginalConstructor()
                ->getMock();

        $eventComponentHandlerMock
                ->expects(static::atLeast(count($emotionElementIds)))
                ->method('handle');

        $emotionElementService = new EmotionElementService(
            new \ArrayObject(),
            Shopware()->Container()->get('shopware_emotion.emotion_element_gateway'),
            $eventComponentHandlerMock,
            Shopware()->Container()->get('shopware_emotion.data_collection_resolver'),
            Shopware()->Container()->get('events')
        );

        $contextService = Shopware()->Container()->get('shopware_storefront.context_service');

        $emotionElementService->getList($emotionElementIds, $contextService->createShopContext(1));
    }
}
