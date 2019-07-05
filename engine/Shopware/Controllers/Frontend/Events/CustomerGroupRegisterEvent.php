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

namespace Shopware\Controllers\Frontend\Events;

use Enlight_Event_EventArgs;
use Shopware_Controllers_Frontend_Register;

class CustomerGroupRegisterEvent extends Enlight_Event_EventArgs
{
    public const EVENT_NAME = 'Shopware_Controllers_Frontend_Register_CustomerGroupRegister';

    public function getSubject(): Shopware_Controllers_Frontend_Register
    {
        return $this->get('subject');
    }

    /**
     * @deprecated use @see \Shopware\Controllers\Frontend\Events\CustomerGroupRegister::getCustomerGroupId
     */
    public function getValidation(): string
    {
        return $this->get('sValidation');
    }

    public function getCustomerGroupId(): int
    {
        return (int) $this->get('customerGroupId');
    }
}
