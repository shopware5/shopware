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

class StoreFrontCustomerGreetingService implements StoreFrontCustomerGreetingServiceInterface
{
    /**
     * @var \Enlight_Components_Session_Namespace
     */
    private $session;

    /**
     * @var Connection
     */
    private $connection;

    /**
     * @var \Shopware_Components_Config
     */
    private $config;

    public function __construct(
        \Enlight_Components_Session_Namespace $session,
        Connection $connection,
        \Shopware_Components_Config $config
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
        if ($this->session->sOneTimeAccount) {
            return null;
        }

        if (!isset($this->session->userInfo)) {
            $this->session->userInfo = $this->fetchUserInfo();
        }

        if ($this->session->userInfo['accountmode'] == 1) {
            $this->session->sOneTimeAccount = true;
            $this->session->userInfo = null;
        }

        return $this->session->userInfo;
    }

    /**
     * @return array|null
     */
    private function fetchUserInfo()
    {
        $userId = $this->session->offsetGet('sUserId');
        if (!$userId) {
            $userId = $this->session->offsetGet('auto-user');
        }

        if (!$userId) {
            return null;
        }

        return $this->connection->fetchAssoc(
            'SELECT firstname, lastname, email, salutation, title, birthday, accountmode FROM s_user WHERE id = :id',
            [':id' => $userId]
        );
    }
}
