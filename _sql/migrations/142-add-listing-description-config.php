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

class Migrations_Migration142 extends Shopware\Components\Migrations\AbstractMigration
{
    public function up($modus)
    {
        $statement = $this->getConnection()->prepare("SELECT id FROM s_core_config_elements WHERE name = 'useShortDescriptionInListing'");
        $statement->execute();
        $data = $statement->fetchAll();

        if (empty($data)) {
            $sql = <<<'EOD'
                SET @parentId = (SELECT id FROM s_core_config_forms WHERE name = 'Other' LIMIT 1);

                INSERT IGNORE INTO `s_core_config_forms`
                (`id`, `parent_id`, `name`, `label`, `description`, `position`, `scope`, `plugin_id`)
                VALUES (NULL, @parentId, 'LegacyOptions', 'Abwärtskompatibilität', NULL, '0', '0', NULL);

                SET @formId = (SELECT id FROM s_core_config_forms WHERE name = 'LegacyOptions' LIMIT 1);

                INSERT IGNORE INTO `s_core_config_form_translations` (`id`, `form_id`, `locale_id`, `label`, `description`)
                VALUES (NULL, @formId, '2', 'Legacy options', NULL);

                INSERT IGNORE INTO `s_core_config_elements`
                (`id`, `form_id`, `name`, `value`, `label`, `description`, `type`, `required`, `position`, `scope`, `filters`, `validators`)
                VALUES (NULL, @formId, 'useShortDescriptionInListing', 'b:1;', 'In Listen-Ansichten immer die Artikel-Kurzbeschreibung anzeigen', 'Beeinflusst: Topseller, Kategorielisten, Einkaufswelten', 'checkbox', '0', '0', '0', NULL, NULL);

                SET @elementId = (SELECT id FROM s_core_config_elements WHERE name = 'useShortDescriptionInListing' LIMIT 1);

                INSERT IGNORE INTO `s_core_config_element_translations` (`id`, `element_id`, `locale_id`, `label`, `description`)
                VALUES (NULL, @elementId, '2', 'Always display item descriptions in listing views', 'Affected views: Top seller, category listings, emotions');
EOD;

            $this->addSql($sql);
        }
    }
}
