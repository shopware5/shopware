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

/**
 * @category  Shopware
 * @package   Shopware\Tests
 * @copyright Copyright (c) 2012, shopware AG (http://www.shopware.de)
 */
class Shopware_Tests_Components_Event_PluginTest extends Enlight_Components_Test_Plugin_TestCase
{
    public function testSubscribeEvent()
    {
        // Use debug plugin (arbitrarily chosen)
        $bootstrap = Shopware()->Plugins()->Core()->Debug();

        // Subscribe
        $bootstrap->subscribeEvent(
			'event1',
			'listener1'
		);

        $found = array_filter($bootstrap->Collection()->Subscriber()->getListeners(), function ($handler) {
            return $handler->getName() == 'event1';
        });

        $this->assertCount(1, $found);
        $this->assertEquals('listener1', array_values($found)[0]->getListener());
    }

    public function testUnsubscribeEvent()
    {
        // Use debug plugin (arbitrarily chosen)
        $bootstrap = Shopware()->Plugins()->Core()->Debug();

        // Subscribe two events
        $bootstrap->subscribeEvent(
			'event1',
			'listener1'
		);
        $bootstrap->subscribeEvent(
            'event1',
            'listener2'
        );

        // Remove first
        $bootstrap->unsubscribeEvent(
            'event1',
            'listener1'
        );

        $found = array_filter($bootstrap->Collection()->Subscriber()->getListeners(), function ($handler) {
            return $handler->getName() == 'event1';
        });

        $this->assertCount(1, $found);
        $this->assertEquals('listener2', array_values($found)[0]->getListener());
    }
}
