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

class PaymentFallbackEvent extends Enlight_Event_EventArgs
{
    public const EVENT_NAME = 'Shopware_Modules_Admin_Payment_Fallback';

    public function getId(): int
    {
        return (int) $this->get('id');
    }

    public function getPaymentName(): string
    {
        return $this->get('name');
    }

    public function getDescription(): string
    {
        return $this->get('description');
    }

    public function getTemplate(): string
    {
        return $this->get('template');
    }

    public function getClass(): string
    {
        return $this->get('class');
    }

    public function getTable(): string
    {
        return $this->get('table');
    }

    public function getHide(): bool
    {
        return (bool) $this->get('hide');
    }

    public function getAdditionalDescription(): string
    {
        return $this->get('additionaldescription');
    }

    public function getDebitPercent(): float
    {
        return (float) $this->get('debit_percent');
    }

    public function getSurcharge(): float
    {
        return (float) $this->get('surcharge');
    }

    public function getSurchargestring(): string
    {
        return $this->get('surchargestring');
    }

    public function getPosition(): int
    {
        return (int) $this->get('position');
    }

    public function isActive(): bool
    {
        return (bool) $this->get('active');
    }

    public function isEsdactive(): bool
    {
        return (bool) $this->get('esdactive');
    }

    public function getEmbediframe(): string
    {
        return $this->get('embediframe');
    }

    public function isHideprospect(): bool
    {
        return (bool) $this->get('hideprospect');
    }

    public function getAction(): ?string
    {
        return $this->get('action');
    }

    public function getPluginID(): ?int
    {
        $pluginId = $this->get('pluginID');

        return $pluginId !== null ? (int) $pluginId : null;
    }

    public function getSource(): ?int
    {
        $source = $this->get('source');

        return $source !== null ? (int) $source : null;
    }

    public function isMobileInactive(): bool
    {
        return (bool) $this->get('mobile_inactive');
    }
}
