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

class Migrations_Migration956 extends Shopware\Components\Migrations\AbstractMigration
{
    public function up($modus)
    {
        $sql = <<<'SQL'
SET @formId = (SELECT id FROM `s_core_config_forms` WHERE name='Log');
SQL;

        $this->addSql("SET @localeID = (SELECT `id` FROM `s_core_locales` WHERE `locale` = 'en_GB' LIMIT 1);");
        $this->addSql($sql);

        $sql = <<<'SQL'
      INSERT IGNORE INTO `s_core_config_elements`
                (`form_id`, `name`, `value`, `label`, `description`, `type`, `required`, `position`, `scope`, `options`)
                VALUES
                (@formID, 'logMailAddress', 's:0:"";', 'Alternative E-Mail-Adresse für Fehlermeldungen', 'Wenn dieses Feld leer ist, wird die Shopbetreiber E-Mail-Adresse verwendet', 'text', 0, 0, 0, NULL)
SQL;
        $this->addSql($sql);
        $this->addSql('SET @elementID = (SELECT id FROM s_core_config_elements WHERE name = "logMailAddress")');

        $sql = <<<EOD
                INSERT IGNORE INTO `s_core_config_element_translations`
                (`element_id`, `locale_id`, `label`, `description`)
                VALUES
                (@elementID, @localeID, 'Alternative email address for errors', 'If this field is empty, the shop owners email address will be used');
EOD;
        $this->addSql($sql);
    }
}
