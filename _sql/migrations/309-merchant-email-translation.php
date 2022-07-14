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

class Migrations_Migration309 extends Shopware\Components\Migrations\AbstractMigration
{
    public function up($modus)
    {
        $sql = <<<'EOD'
            UPDATE s_cms_static SET description = 'Merchant login' WHERE description = 'Reseller-Login';

            UPDATE `s_core_translations` SET `objectdata` = 'a:2:{s:7:"subject";s:39:"Your merchant account has been unlocked";s:7:"content";s:186:"Hello,\n\nYour merchant account {config name=shopName} has been unlocked.\n  \nFrom now on, we will charge you the net purchase price. \n  \nBest regards\n  \nYour team of {config name=shopName}";}'
            WHERE `objectdata` = 'a:2:{s:7:"subject";s:37:"Your trader account has been unlocked";s:7:"content";s:184:"Hello,\n\nYour trader account {config name=shopName} has been unlocked.\n  \nFrom now on, we will charge you the net purchase price. \n  \nBest regards\n  \nYour team of {config name=shopName}";}';

            UPDATE `s_core_translations` SET `objectdata` = 'a:2:{s:7:"subject";s:43:"Your merchant account has not been accepted";s:7:"content";s:309:"Dear customer,\n\nThank you for your interest in our trade prices. Unfortunately, we do not have a trading license yet so that we cannot accept you as a merchant. \n\nIn case of further questions please do not hesitate to contact us via telephone, fax or email. \n\nBest regards\n\nYour Team of {config name=shopName}";}'
            WHERE `objectdata` = 'a:2:{s:7:"subject";s:40:"Your trader acount has not been accepted";s:7:"content";s:306:"Dear customer,\n\nThank you for your interest in our trade prices. Unfortunately, we do not have a trading license yet so that we cannot accept you as a trader. \n\nIn case of further questions please do not heitate to contact us via telephone, fax or email. \n\nBest regards\n\nYour Team of {config name=shopName}";}';

EOD;
        $this->addSql($sql);
    }
}
