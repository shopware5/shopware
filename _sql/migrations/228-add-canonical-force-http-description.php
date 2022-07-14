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

class Migrations_Migration228 extends Shopware\Components\Migrations\AbstractMigration
{
    public function up($modus)
    {
        $sql = <<<'EOD'
SET @elementId = (SELECT id FROM s_core_config_elements WHERE name ='forceCanonicalHttp' LIMIT 1);
SET @localeID = (SELECT id FROM s_core_locales WHERE locale='en_GB');

UPDATE s_core_config_elements SET description='Diese Option greift nicht, wenn die Option "Überall SSL verwenden" aktiviert ist.' WHERE id=@elementID;
UPDATE s_core_config_element_translations SET description='This option does not take effect if the option "Use always SSL" is activated.' WHERE element_id=@elementID AND locale_id=@localeID;
EOD;

        $this->addSql($sql);
    }
}
