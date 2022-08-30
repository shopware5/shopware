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

namespace Shopware\Tests\Unit\Plugins\Backend\Auth;

use Enlight_Controller_Action;
use Enlight_Controller_ActionEventArgs;
use Enlight_Controller_Request_RequestTestCase;
use PHPUnit\Framework\TestCase;
use Shopware\Tests\Functional\Helper\Utils;
use Shopware_Plugins_Backend_Auth_Bootstrap;

class BootstrapTest extends TestCase
{
    public function testValidatesAlsoSnakeCaseControllers(): void
    {
        /** @var Shopware_Plugins_Backend_Auth_Bootstrap $authPlugin */
        $authPlugin = $this->createPartialMock(Shopware_Plugins_Backend_Auth_Bootstrap::class, ['initLocale', 'checkAuth']);
        $authPlugin->setNoAcl(true);

        $action = $this->getMockBuilder(Enlight_Controller_Action::class)->disableOriginalConstructor()->getMock();
        $testRequest = new Enlight_Controller_Request_RequestTestCase();
        $testRequest->setControllerName('user_manager');
        $testRequest->setModuleName('Backend');
        $action->method('Request')->willReturn($testRequest);
        $eventArgs = new Enlight_Controller_ActionEventArgs();
        $eventArgs->set('subject', $action);

        $authPlugin->onPreDispatchBackend($eventArgs);

        static::assertEquals('usermanager', Utils::hijackAndReadProperty($authPlugin, 'aclResource'));
    }
}
