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

class Migrations_Migration457 extends Shopware\Components\Migrations\AbstractMigration
{
    public function up($modus)
    {
        // add new config form media
        $sql = <<<'EOD'
        SET @parent = (SELECT id FROM s_core_config_forms WHERE name = 'Frontend' AND label = 'Storefront' LIMIT 1);
EOD;
        $this->addSql($sql);

        $sql = <<<'EOD'
            INSERT IGNORE INTO `s_core_config_forms` (`parent_id`, `name`, `label`, `description`, `position`, `scope`, `plugin_id`)
            VALUES (@parent, 'Media', 'Medien', NULL, '13', '', NULL);
EOD;
        $this->addSql($sql);

        $sql = <<<'EOD'
        SET @parent = (SELECT id FROM s_core_config_forms WHERE name = 'Media' AND label = 'Medien' LIMIT 1);
EOD;
        $this->addSql($sql);

        $sql = <<<'EOD'
            INSERT IGNORE INTO `s_core_config_form_translations` (`form_id`, `locale_id`, `label`, `description`)
            VALUES (@parent, 2, 'Media', '');
EOD;
        $this->addSql($sql);

        // add a new config field
        $sql = <<<'EOD'
            INSERT IGNORE INTO `s_core_config_elements` (`form_id`, `name`, `value`, `label`, `description`, `type`, `required`, `position`, `scope`, `filters`, `validators`, `options`) VALUES
            (@parent, 'thumbnailNoiseFilter', 'b:0;', 'Rauschfilterung bei Thumbnails', 'Filtert beim Generieren der Thumbnails Bildfehler heraus. Achtung! Bei aktivierter Option kann das Generieren der Thumbnails wesentlich länger dauern', 'checkbox', 0, 0, 0, NULL, NULL, 'a:0:{}');
EOD;
        $this->addSql($sql);

        $sql = <<<'EOD'
        SET @elementId = (SELECT id FROM `s_core_config_elements` WHERE `name` = 'thumbnailNoiseFilter' LIMIT 1);
EOD;
        $this->addSql($sql);

        $sql = <<<'EOD'
        INSERT IGNORE INTO `s_core_config_element_translations` (`element_id`, `locale_id`, `label`, `description`)
        VALUES (@elementId, '2', 'Thumbnail noise filter', 'Produces clearer thumbnails. May increase thumbnail generation time.');
EOD;
        $this->addSql($sql);
    }
}
