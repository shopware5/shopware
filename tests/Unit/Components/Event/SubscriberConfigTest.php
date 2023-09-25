<?php

declare(strict_types=1);
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

namespace Shopware\Tests\Unit\Components\Event;

use Enlight_Event_EventArgs;
use Enlight_Event_Handler_Default;
use Enlight_Event_Subscriber_Config;
use PHPUnit\Framework\TestCase;

class SubscriberConfigTest extends TestCase
{
    protected Enlight_Event_Subscriber_Config $eventManager;

    public function setUp(): void
    {
        // Giving "test" as parameter sets up a test storage. Even if it is not a valid value
        $this->eventManager = new Enlight_Event_Subscriber_Config('test');
    }

    public function testAddSubscriber(): void
    {
        // Add to subscribers
        $handler0 = new Enlight_Event_Handler_Default(
            'Example',
            function () {
                return 'foo';
            }
        );
        $this->eventManager->registerListener($handler0);

        $handler1 = new Enlight_Event_Handler_Default(
            'Example',
            function () {
                return 'bar';
            }
        );
        $this->eventManager->registerListener($handler1);

        $result = $this->eventManager->getListeners();

        static::assertCount(2, $result);
        static::assertEquals('foo', $result[0]->execute(new Enlight_Event_EventArgs()));
        static::assertEquals('bar', $result[1]->execute(new Enlight_Event_EventArgs()));
    }

    public function testRemoveSubscriber(): void
    {
        // Add to subscribers
        $handler0 = new Enlight_Event_Handler_Default(
            'Example',
            function () {
                return 'foo';
            }
        );
        $this->eventManager->registerListener($handler0);

        $handler1 = new Enlight_Event_Handler_Default(
            'Example',
            function () {
                return 'bar';
            }
        );
        $this->eventManager->registerListener($handler1);

        // Remove first subscriber
        $this->eventManager->removeListener($handler0);

        $result = $this->eventManager->getListeners();

        // Only the second one should be left
        static::assertCount(1, $result);
        static::assertEquals('bar', $result[0]->execute(new Enlight_Event_EventArgs()));
    }
}
