<?php
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

class Migrations_Migration214 extends Shopware\Components\Migrations\AbstractMigration
{
    public function up($modus)
    {
        $sql = <<<'EOD'
        ALTER TABLE  `s_core_payment_data`
            ADD  `account_number` VARCHAR( 50 ) NULL DEFAULT NULL AFTER  `iban` ,
            ADD  `bank_code` VARCHAR( 50 ) NULL DEFAULT NULL AFTER  `account_number` ,
            ADD  `account_holder` VARCHAR( 50 ) NULL DEFAULT NULL AFTER  `bank_code` ;

        UPDATE IGNORE s_core_snippets
        SET namespace = 'frontend/plugins/payment/sepa'
        WHERE namespace = 'engine/Shopware/Plugins/Default/Core/PaymentMethods/Views/frontend/plugins/payment/sepa';

        UPDATE IGNORE s_core_snippets
        SET namespace = 'frontend/plugins/payment/debit'
        WHERE namespace = 'engine/Shopware/Plugins/Default/Core/PaymentMethods/Views/frontend/plugins/payment/debit';

        UPDATE IGNORE s_core_snippets
        SET namespace = 'frontend/plugins/payment/sepaemail'
        WHERE namespace = 'engine/Shopware/Plugins/Default/Core/PaymentMethods/Views/frontend/plugins/sepa/email';

        INSERT IGNORE INTO `s_core_payment_instance` (order_id, user_id, amount, account_number,
        bank_code, bank_name, account_holder, payment_mean_id,
        firstname, lastname, address, zipcode, city )
        SELECT s_order.id as order_id, s_order.userID as user_id, s_order.invoice_amount_net as amount ,
        s_user_debit.account as account_number, s_user_debit.bankcode as bank_code,
        s_user_debit.bankname as bank_name, s_user_debit.bankholder as account_holder,
        s_order.paymentID as payment_mean_id,
        s_order_billingaddress.firstname as firstname, s_order_billingaddress.lastname as lastname,
        CONCAT(s_order_billingaddress.street, ' ', s_order_billingaddress.streetnumber) as address,
        s_order_billingaddress.zipcode as zipcode, s_order_billingaddress.city as city
        FROM s_order LEFT JOIN s_user_debit ON s_order.userID = s_user_debit.userID
        LEFT JOIN s_order_billingaddress ON s_order.id = s_order_billingaddress.orderID
        WHERE paymentID = (SELECT id FROM s_core_paymentmeans WHERE name LIKE 'debit')
        AND s_order.id NOT IN (SELECT DISTINCT(order_id) FROM s_core_payment_instance);

        INSERT IGNORE INTO `s_core_payment_instance` (order_id, user_id, amount, payment_mean_id,
        firstname, lastname, address, zipcode, city )
        SELECT s_order.id as order_id, s_order.userID as user_id, s_order.invoice_amount_net as amount ,
        s_order.paymentID as payment_mean_id,
        s_order_billingaddress.firstname as firstname, s_order_billingaddress.lastname as lastname,
        CONCAT(s_order_billingaddress.street, ' ', s_order_billingaddress.streetnumber) as address,
        s_order_billingaddress.zipcode as zipcode, s_order_billingaddress.city as city
        FROM s_order
        LEFT JOIN s_order_billingaddress ON s_order.id = s_order_billingaddress.orderID
        WHERE paymentID NOT IN (SELECT id FROM s_core_paymentmeans WHERE name LIKE 'debit' OR name like 'sepa')
        AND s_order.id NOT IN (SELECT DISTINCT(order_id) FROM s_core_payment_instance);
EOD;
        $this->addSql($sql);
    }
}
