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

namespace Shopware\Tests\Functional\Bundle\EmotionBundle\ComponentHandler;

use ArrayObject;
use Enlight_Components_Test_TestCase;
use Shopware\Bundle\EmotionBundle\Service\EmotionElementService;

class EventComponentHandlerTest extends Enlight_Components_Test_TestCase
{
    public function testFallbackToEventComponentHandler()
    {
        $emotionElementIds = [1, 2, 5];

        $eventComponentHandlerMock =
            $this->getMockBuilder(\Shopware\Bundle\EmotionBundle\ComponentHandler\EventComponentHandler::class)
                ->disableOriginalConstructor()
                ->getMock();

        $eventComponentHandlerMock
                ->expects(static::atLeast(\count($emotionElementIds)))
                ->method('handle');

        $emotionElementService = new EmotionElementService(
            new ArrayObject(),
            Shopware()->Container()->get(\Shopware\Bundle\EmotionBundle\Service\Gateway\EmotionElementGateway::class),
            $eventComponentHandlerMock,
            Shopware()->Container()->get(\Shopware\Bundle\EmotionBundle\Service\DataCollectionResolverInterface::class),
            Shopware()->Container()->get('events')
        );

        $contextService = Shopware()->Container()->get(\Shopware\Bundle\StoreFrontBundle\Service\ContextServiceInterface::class);

        $emotionElementService->getList($emotionElementIds, $contextService->createShopContext(1));
    }
}
