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

class Migrations_Migration219 extends Shopware\Components\Migrations\AbstractMigration
{
    public function up($modus)
    {
        $sql = <<<'EOD'
        INSERT IGNORE INTO `s_core_payment_data` (payment_mean_id, user_id, bankname, account_number, bank_code, account_holder, created_at)
        SELECT (SELECT id FROM s_core_paymentmeans WHERE name LIKE 'debit') as payment_mean_id, s_user_debit.userID as user_id,
        s_user_debit.bankname as bankname, s_user_debit.account as number,
        s_user_debit.bankcode as bank_code, s_user_debit.bankholder as account_holder,
        NOW() as created_at
        FROM s_user_debit;

        SET @plugin_id = (SELECT id FROM s_core_plugins WHERE name = 'PaymentMethods' LIMIT 1);

        INSERT IGNORE INTO `s_core_subscribes` (`id`, `subscribe`, `type`, `listener`, `pluginID`, `position`) VALUES
            (NULL, 'Enlight_Controller_Action_PostDispatchSecure_Backend_Customer', 0, 'Shopware_Plugins_Core_PaymentMethods_Bootstrap::onBackendCustomerPostDispatch', @plugin_id, 0);

EOD;
        $this->addSql($sql);
    }
}
