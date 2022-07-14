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

class Migrations_Migration1647 extends Shopware\Components\Migrations\AbstractMigration
{
    public function up($modus)
    {
        $sql = <<<SQL
        UPDATE `s_core_config_mails`
        SET `context` = 'a:15:{s:5:\"sMAIL\";s:14:\"xy@example.org\";s:7:\"sConfig\";a:0:{}s:6:\"street\";s:15:\"Musterstraße 1\";' 
            's:7:\"zipcode\";s:5:\"12345\";s:4:\"city\";s:11:\"Musterstadt\";s:7:\"country\";s:1:\"2\";s:5:\"state\";N;s:13:\"customer_type\";' 
            's:7:\"private\";s:10:\"salutation\";s:4:\"Herr\";s:5:\"title\";s:3:\"Dr.\";s:9:\"firstname\";s:3:\"Max\";s:8:\"lastname\";' 
            's:10:\"Mustermann\";s:11:\"accountmode\";s:1:\"0\";s:5:\"email\";s:14:\"xy@example.org\";s:10:\"additional\";a:1:{s:13:\"customer_type\";s:7:\"private\";}}'
        WHERE `name` = 'sREGISTERCONFIRMATION'   
SQL;
        $this->addSql($sql);
    }
}
