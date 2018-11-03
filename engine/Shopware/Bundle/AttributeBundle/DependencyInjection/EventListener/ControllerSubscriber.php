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

namespace Shopware\Bundle\AttributeBundle\DependencyInjection\EventListener;

use Enlight\Event\SubscriberInterface;

/**
 * @category  Shopware
 *
 * @copyright Copyright (c) shopware AG (http://www.shopware.com)
 */
class ControllerSubscriber implements SubscriberInterface
{
    public static function getSubscribedEvents()
    {
        return [
            'Enlight_Controller_Dispatcher_ControllerPath_Backend_EntitySearch' => 'registerEntitySearchController',
            'Enlight_Controller_Dispatcher_ControllerPath_Backend_Attributes' => 'registerAttributesController',
            'Enlight_Controller_Dispatcher_ControllerPath_Backend_AttributeData' => 'registerAttributeDataController',
        ];
    }

    public function registerEntitySearchController()
    {
        return __DIR__ . '/../../Controllers/Backend/EntitySearch.php';
    }

    public function registerAttributesController()
    {
        return __DIR__ . '/../../Controllers/Backend/Attributes.php';
    }

    public function registerAttributeDataController()
    {
        return __DIR__ . '/../../Controllers/Backend/AttributeData.php';
    }
}
