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

namespace Shopware\Tests\Unit\Components\DependencyInjection;

use Enlight_Event_EventArgs;

/**
 * Just a test service
 */
class Service
{
    private $class;

    public function __construct($class)
    {
        $this->class = $class;
    }

    public function onEvent(Enlight_Event_EventArgs $e): void
    {
        /** @var ProjectServiceContainer $container */
        $container = $e->getSubject();
        $container->set('bar', $this->class);
    }
}
