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

class Migrations_Migration220 extends Shopware\Components\Migrations\AbstractMigration
{
    public function up($modus)
    {
        // The intermediate table t is needed to avoid a MySql error
        // see http://stackoverflow.com/questions/5816840/delete-i-cant-specify-target-table

        $sql = <<<'EOD'
        DELETE FROM s_core_payment_data WHERE id IN ( SELECT * FROM (
        SELECT s_core_payment_data.payment_mean_id
                   FROM s_core_payment_data
                  GROUP
                     BY s_core_payment_data.payment_mean_id
                      , s_core_payment_data.user_id
                 HAVING COUNT(1) > 1
        ) as t);

        ALTER TABLE `s_core_payment_data` ADD UNIQUE (
            `payment_mean_id` ,
            `user_id`
        );
EOD;
        $this->addSql($sql);
    }
}
