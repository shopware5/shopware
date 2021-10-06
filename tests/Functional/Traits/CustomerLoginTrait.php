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

namespace Shopware\Tests\Functional\Traits;

use Enlight_Components_Session_Namespace;
use RuntimeException;

trait CustomerLoginTrait
{
    /**
     * Logged in a customer
     */
    public function loginCustomer(
        string $sessionId = 'sessionId',
        int $customerId = 1,
        string $email = 'test@example.com',
        int $countryId = 2,
        int $areaId = 3,
        string $customerGroupKey = 'EK',
        ?int $stateId = null
    ): void {
        Shopware()->Container()->reset('modules');
        $session = Shopware()->Container()->get('session');
        if (!$session instanceof Enlight_Components_Session_Namespace) {
            throw new RuntimeException('Cannot initialize session');
        }

        $session->offsetSet('sessionId', $sessionId);
        $session->offsetSet('sUserId', $customerId);
        $session->offsetSet('sUserMail', $email);
        $session->offsetSet('sCountry', $countryId);
        $session->offsetSet('sArea', $areaId);
        $session->offsetSet('sUserGroup', $customerGroupKey);
        $session->offsetSet('sState', $stateId);

        Shopware()->Container()->get('dbal_connection')->executeQuery(
            'UPDATE s_user SET sessionID = :sessionId, lastlogin = now() WHERE id=:userId',
            [
                ':sessionId' => $sessionId,
                ':userId' => $customerId,
            ]
        );

        static::assertTrue(Shopware()->Modules()->Admin()->sCheckUser());
    }

    public function logOutCustomer(): void
    {
        $session = Shopware()->Container()->get('session');
        $session->offsetUnset('sessionId');
        $session->offsetUnset('sUserId');
        $session->offsetUnset('sUserMail');
        $session->offsetUnset('sUserGroup');
        $session->offsetUnset('sCountry');
        $session->offsetUnset('sArea');
        $session->offsetUnset('sState');

        static::assertFalse(Shopware()->Modules()->Admin()->sCheckUser());
    }
}
