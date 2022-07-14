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

class Migrations_Migration368 extends Shopware\Components\Migrations\AbstractMigration
{
    public function up($modus)
    {
        $sql = <<<'EOD'
        UPDATE s_cms_support_fields SET label = 'Straße ; Hausnummer' WHERE label = 'Straße / Hausnummer' AND typ = 'text2';
        UPDATE s_cms_support_fields SET label = 'PLZ ; Ort' WHERE label = 'PLZ / Ort' AND typ = 'text2';
        UPDATE s_cms_support_fields SET label = 'Street ; house number' WHERE label = 'Street / house number' AND typ = 'text2';
        UPDATE s_cms_support_fields SET label = 'Postal Code ; City' WHERE label = 'Postal Code / City' AND typ = 'text2';
EOD;
        $this->addSql($sql);
    }
}
