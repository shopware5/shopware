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

namespace Shopware\Tests\Functional\Bundle\AccountBundle\Service;

use Doctrine\DBAL\Connection;
use PHPUnit\Framework\TestCase;
use Shopware\Bundle\AccountBundle\Service\CustomerUnlockServiceInterface;
use Shopware\Tests\Functional\Traits\ContainerTrait;
use Shopware\Tests\Functional\Traits\DatabaseTransactionBehaviour;

class CustomerUnlockServiceTest extends TestCase
{
    use DatabaseTransactionBehaviour;
    use ContainerTrait;

    public function testUnlockCustomer(): void
    {
        $this->getContainer()->get(Connection::class)->executeQuery("INSERT INTO `s_user` (`id`, `password`, `encoder`, `email`, `active`, `accountmode`, `confirmationkey`, `paymentID`, `firstlogin`, `lastlogin`, `sessionID`, `newsletter`, `validation`, `affiliate`, `customergroup`, `paymentpreset`, `language`, `subshopID`, `referer`, `pricegroupID`, `internalcomment`, `failedlogins`, `lockeduntil`, `default_billing_address_id`, `default_shipping_address_id`, `title`, `salutation`, `firstname`, `lastname`, `birthday`, `customernumber`, `login_token`) VALUES
            (2048, 'FooBar', 'bcrypt', 'foo@bar.com', 1, 0, '', 5, '2018-05-24', '2018-05-24 15:55:32', '3pj2eudm344a5904fe3hp6nvf3', 0, '', 0, 'EK', 0, '1', 1, '', NULL, '', 0, '2018-01-01 00:00:00', 5, 5, NULL, 'mr', 'Foo', 'Bar', NULL, '20005', 'token');");

        /** @var CustomerUnlockServiceInterface $unlockService */
        $unlockService = Shopware()->Container()->get('shopware_account.customer_unlock_service');
        $unlockService->unlock(2048);

        $lockedUntil = $this->getContainer()->get(Connection::class)->fetchOne('SELECT lockeduntil FROM s_user WHERE id = 2048');

        static::assertIsNotString($lockedUntil);
    }
}
