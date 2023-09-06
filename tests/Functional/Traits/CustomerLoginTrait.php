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

namespace Shopware\Tests\Functional\Traits;

use Enlight_Components_Session_Namespace;
use RuntimeException;

trait CustomerLoginTrait
{
    public function loginCustomer(
        ?string $sessionId = null,
        int $customerId = 1,
        string $email = 'test@example.com',
        ?string $passwordChangeDate = null,
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

        if ($passwordChangeDate === null) {
            $passwordChangeDate = Shopware()->Container()->get('dbal_connection')->fetchOne(
                'SELECT `password_change_date` FROM `s_user` WHERE `id` = :customerId;',
                [
                    'customerId' => $customerId,
                ]
            );
        }

        if (empty($sessionId)) {
            $sessionId = $session->getId();
        }

        $session->offsetSet('sessionId', $sessionId);
        $session->offsetSet('sUserId', $customerId);
        $session->offsetSet('sUserMail', $email);
        $session->offsetSet('sUserPasswordChangeDate', $passwordChangeDate);
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
        $session->offsetUnset('sUserPasswordChangeDate');
        $session->offsetUnset('sUserGroup');
        $session->offsetUnset('sCountry');
        $session->offsetUnset('sArea');
        $session->offsetUnset('sState');

        static::assertFalse(Shopware()->Modules()->Admin()->sCheckUser());
    }
}
