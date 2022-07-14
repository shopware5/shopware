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

class Migrations_Migration726 extends Shopware\Components\Migrations\AbstractMigration
{
    public function up($modus)
    {
        if ($modus === self::MODUS_INSTALL) {
            return;
        }

        // move snippets to new namespace
        $moveSnippets = [
            'ConfirmAddressSelectButton',
            'ConfirmAddressSelectLink',
            'ConfirmHeaderBilling',
            'ConfirmHeaderPayment',
            'ConfirmHeaderPaymentShipping',
            'ConfirmHeaderShipping',
            'ConfirmInfoInstantDownload',
            'ConfirmInfoPaymentMethod',
            'ConfirmLinkChangePayment',
            'ConfirmSalutationMr',
            'ConfirmSalutationMs',
        ];

        $moveSnippets = implode('","', $moveSnippets);
        $this->addSql('UPDATE s_core_snippets SET `namespace` = "frontend/checkout/confirm" WHERE `namespace` = "frontend/checkout/confirm_left" AND `name` IN ("' . $moveSnippets . '")');

        // delete orphan snippets
        $this->addSql('DELETE FROM `s_core_snippets` WHERE `namespace` = "frontend/account/select_address"');
        $this->addSql('DELETE FROM `s_core_snippets` WHERE `namespace` = "frontend/account/select_billing"');
        $this->addSql('DELETE FROM `s_core_snippets` WHERE `namespace` = "frontend/account/select_shipping"');
        $this->addSql('DELETE FROM `s_core_snippets` WHERE `namespace` = "frontend/account/confirm_left"');
    }
}
