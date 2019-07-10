<?php declare(strict_types=1);
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

namespace ShopwarePlugins\Notification\Events;

use Enlight_Event_EventArgs;
use Shopware_Plugins_Frontend_Notification_Bootstrap;

class NotificationSavedEvent extends Enlight_Event_EventArgs
{
    public const EVENT_NAME = 'Shopware_Notification_Notification_Saved';

    public function getSubject(): Shopware_Plugins_Frontend_Notification_Bootstrap
    {
        return $this->get('subject');
    }

    public function getNotificationId(): int
    {
        return (int) $this->get('notificationId');
    }

    public function getData(): array
    {
        return $this->get('data');
    }

    /**
     * @deprecated use @see \ShopwarePlugins\Notification\Events\NotificationSavedEvent::getNotificationId
     */
    public function getId(): int
    {
        return (int) $this->get('id');
    }
}
