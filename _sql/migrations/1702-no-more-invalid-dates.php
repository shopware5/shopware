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
class Migrations_Migration1702 extends Shopware\Components\Migrations\AbstractMigration
{
    public function up($modus)
    {
        $sql = [];
        $sql[] = 'ALTER TABLE `s_order_details` CHANGE `releasedate` `releasedate` DATE  NULL  DEFAULT NULL;';
        $sql[] = 'ALTER TABLE `s_core_auth` CHANGE `lastlogin` `lastlogin` DATETIME  NULL  DEFAULT NULL;';
        $sql[] = 'ALTER TABLE `s_campaigns_logs` CHANGE `datum` `datum` DATETIME  NULL  DEFAULT NULL;';
        $sql[] = 'ALTER TABLE `s_emarketing_banners` CHANGE `valid_from` `valid_from` DATETIME  NULL  DEFAULT NULL;';
        $sql[] = 'ALTER TABLE `s_emarketing_banners` CHANGE `valid_to` `valid_to` DATETIME  NULL  DEFAULT NULL;';
        $sql[] = 'ALTER TABLE `s_emarketing_lastarticles` CHANGE `time` `time` DATETIME  NULL  DEFAULT NULL;';
        $sql[] = 'ALTER TABLE `s_emarketing_tellafriend` CHANGE `datum` `datum` DATE  NULL  DEFAULT NULL;';
        $sql[] = 'ALTER TABLE `s_order_basket` CHANGE `datum` `datum` DATE  NULL  DEFAULT NULL;';
        $sql[] = 'ALTER TABLE `s_order_comparisons` CHANGE `datum` `datum` DATE  NULL  DEFAULT NULL;';
        $sql[] = 'ALTER TABLE `s_order_notes` CHANGE `datum` `datum` DATE  NULL  DEFAULT NULL;';
        $sql[] = 'ALTER TABLE `s_statistics_pool` CHANGE `datum` `datum` DATE  NULL  DEFAULT NULL;';
        $sql[] = 'ALTER TABLE `s_statistics_referer` CHANGE `datum` `datum` DATE  NULL  DEFAULT NULL;';
        $sql[] = 'ALTER TABLE `s_statistics_visitors` CHANGE `datum` `datum` DATE  NULL  DEFAULT NULL;';
        $sql[] = 'ALTER TABLE `s_user` CHANGE `firstlogin` `firstlogin` DATE  NULL  DEFAULT NULL;';
        $sql[] = 'ALTER TABLE `s_user` CHANGE `lastlogin` `lastlogin` DATETIME  NULL  DEFAULT NULL;';

        foreach($sql as $query) {
            $this->addSql($query);
        }
    }
}
