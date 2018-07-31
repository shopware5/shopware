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
class Migrations_Migration1420 extends Shopware\Components\Migrations\AbstractMigration
{
    public function up($modus)
    {
        $sql = <<<SQL
UPDATE s_core_config_elements
SET s_core_config_elements.value = 's:54:"www.shopware.de,
www.shopware.ag,
www.shopware-ag.de,
de.shopware.com,
en.shopware.com";'
WHERE s_core_config_elements.name = 'seobacklinkwhitelist'
AND MD5(s_core_config_elements.value) = '33909ef97c07e5d608fc4bcac93c24cb';

UPDATE s_core_config_values
INNER JOIN s_core_config_elements ON s_core_config_values.element_id = s_core_config_elements.id
SET s_core_config_values.value = 's:54:"www.shopware.de,
www.shopware.ag,
www.shopware-ag.de,
de.shopware.com,
en.shopware.com";'
WHERE s_core_config_elements.name = 'seobacklinkwhitelist'
AND MD5(s_core_config_elements.value) = '33909ef97c07e5d608fc4bcac93c24cb';
SQL;
        $this->addSql($sql);
    }
}
