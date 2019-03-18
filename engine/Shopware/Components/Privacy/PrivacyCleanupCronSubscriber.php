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

namespace Shopware\Components\Privacy;

use Enlight\Event\SubscriberInterface;
use Shopware_Components_Config;

class PrivacyCleanupCronSubscriber implements SubscriberInterface
{
    /**
     * @var PrivacyServiceInterface
     */
    private $privacyService;

    /**
     * @var Shopware_Components_Config
     */
    private $config;

    public function __construct(PrivacyServiceInterface $privacyService, Shopware_Components_Config $config)
    {
        $this->privacyService = $privacyService;
        $this->config = $config;
    }

    /**
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return [
            'Shopware_CronJob_CleanupCancelledBaskets' => 'cleanupCancelledBaskets',
            'Shopware_CronJob_CleanupGuestCustomers' => 'cleanupGuestCustomers',
        ];
    }

    public function cleanupCancelledBaskets()
    {
        $this->privacyService->cleanupCanceledOrders(
            $this->config->get('privacyBasketMonths')
        );

        return true;
    }

    public function cleanupGuestCustomers()
    {
        $this->privacyService->cleanupGuestUsers(
            $this->config->get('privacyGuestCustomerMonths')
        );

        return true;
    }
}
