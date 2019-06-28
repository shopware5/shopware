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

use Shopware\Components\Migrations\AbstractMigration;

class Migrations_Migration1629 extends AbstractMigration
{
    public function up($modus)
    {
        if ($modus === AbstractMigration::MODUS_UPDATE) {
            return;
        }

        $sql = <<<'EOD'
INSERT INTO `s_core_translations` (`objecttype`, `objectdata`, `objectkey`, `objectlanguage`) VALUES
('config_dispatch', 'a:5:{i:14;a:2:{s:13:"dispatch_name";s:16:"Express Delivery";s:20:"dispatch_description";s:22:"Delivery within 2 days";}i:9;a:1:{s:13:"dispatch_name";s:17:"Standard delivery";}i:16;a:2:{s:13:"dispatch_name";s:31:"Standard international delivery";s:20:"dispatch_description";s:52:"Standard delivery into countries other than Germany.";}i:10;a:2:{s:13:"dispatch_name";s:29:"Shipping cost based on weight";s:20:"dispatch_description";s:128:"If the weight of a product exceeds 1 kilogram, the shipping method will switch automatically to "Shipping cost based on weight".";}i:12;a:1:{s:13:"dispatch_name";s:10:"Discounted";}}', 1, '2')
EOD;
        $this->addSql($sql);
    }
}
