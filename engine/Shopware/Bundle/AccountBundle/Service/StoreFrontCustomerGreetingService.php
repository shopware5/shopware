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

namespace Shopware\Bundle\AccountBundle\Service;

use Doctrine\DBAL\Connection;
use Enlight_Components_Session_Namespace as Session;
use Shopware\Models\Customer\Customer;
use Shopware_Components_Config as Config;

class StoreFrontCustomerGreetingService implements StoreFrontCustomerGreetingServiceInterface
{
    private Session $session;

    private Connection $connection;

    private Config $config;

    public function __construct(
        Session $session,
        Connection $connection,
        Config $config
    ) {
        $this->session = $session;
        $this->connection = $connection;
        $this->config = $config;
    }

    /**
     * {@inheritdoc}
     */
    public function fetch()
    {
        if (!$this->config->get('useSltCookie')) {
            return null;
        }
        if ($this->session->get('sOneTimeAccount')) {
            return null;
        }

        if (!$this->session->get('userInfo')) {
            $this->session->set('userInfo', $this->fetchCustomerInfo());
        }

        if ($this->session->get('userInfo') && (int) $this->session->get('userInfo')['accountmode'] === Customer::ACCOUNT_MODE_FAST_LOGIN) {
            $this->session->set('sOneTimeAccount', true);
            $this->session->set('userInfo', null);
        }

        return $this->session->get('userInfo');
    }

    /**
     * @return array<string, mixed>|null
     */
    private function fetchCustomerInfo(): ?array
    {
        $userId = $this->session->offsetGet('sUserId');
        if (!$userId) {
            $userId = $this->session->offsetGet('auto-user');
        }

        if (!$userId) {
            return null;
        }

        $customerInfo = $this->connection->fetchAssociative(
            'SELECT firstname, lastname, email, salutation, title, birthday, accountmode
             FROM s_user
             WHERE id = :id',
            [':id' => $userId]
        );

        return $customerInfo ?: null;
    }
}
