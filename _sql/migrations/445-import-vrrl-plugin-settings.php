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

class Migrations_Migration445 extends Shopware\Components\Migrations\AbstractMigration
{
    public function up($modus)
    {
        /**
         * Get formId from Checkout section
         * Insert showEsdWarning config
         * Insert serviceAttrField config
         */
        $sql = <<<'EOD'
            SET @configFormId = (SELECT id FROM s_core_config_forms WHERE name = 'Checkout' LIMIT 1);
EOD;
        $this->addSql($sql);

        $sql = <<<'EOD'
            INSERT IGNORE INTO s_core_config_elements ( form_id, name, value, label, type, required, position, scope, options ) VALUES ( @configFormId, 'showEsdWarning', 'b:1;', 'Checkbox zum Widerrufsrecht bei ESD Artikeln anzeigen', 'boolean', 0, 0, 1, 'a:0:{}' );
            SET @formFieldId = (SELECT id FROM s_core_config_elements WHERE name = 'showEsdWarning' LIMIT 1);
            INSERT IGNORE INTO `s_core_config_element_translations` (`id`, `element_id`, `locale_id`, `label`, `description`) VALUES (NULL, @formFieldId, '2', 'Show checkbox for the right of revocations for ESD products', NULL);
EOD;
        $this->addSql($sql);

        $sql = <<<'EOD'
            INSERT IGNORE INTO s_core_config_elements ( form_id, name, value, label, type, required, position, scope, options ) VALUES ( @configFormId, 'serviceAttrField', 's:0:""', 'Artikel-Freitextfeld für Dienstleistungensartikel', 'text', 0, 0, 1, 'a:0:{}' );
            SET @formFieldId = (SELECT id FROM s_core_config_elements WHERE name = 'serviceAttrField' LIMIT 1);
            INSERT IGNORE INTO `s_core_config_element_translations` (`id`, `element_id`, `locale_id`, `label`, `description`) VALUES (NULL, @formFieldId, '2', 'Product free text field for service products', NULL);
EOD;
        $this->addSql($sql);
    }
}
