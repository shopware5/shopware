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

namespace Shopware\Core\Events\Admin;

use Enlight_Event_EventArgs;
use sAdmin;

class ExecuteRiskRuleEvent extends Enlight_Event_EventArgs
{
    public const EVENT_NAME = 'Shopware_Modules_Admin_Execute_Risk_Rule_';

    public function getSubject(): sAdmin
    {
        return $this->get('subject');
    }

    public function getRule(): string
    {
        return $this->get('rule');
    }

    public function getUser(): array
    {
        return $this->get('user');
    }

    public function getBasket(): array
    {
        return $this->get('basket');
    }

    public function getValue(): string
    {
        return $this->get('value');
    }

    public function getPaymentId(): ?int
    {
        $paymentId = $this->get('paymentID');

        return $paymentId !== null ? (int) $paymentId : null;
    }
}
