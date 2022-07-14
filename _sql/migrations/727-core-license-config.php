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

class Migrations_Migration727 extends Shopware\Components\Migrations\AbstractMigration
{
    public function up($modus)
    {
        $sql = <<<'EOD'
SET @parentForm = (SELECT id FROM `s_core_config_forms` WHERE `name` = 'Other' LIMIT 1);
EOD;
        $this->addSql($sql);

        $sql = <<<'EOD'
INSERT IGNORE INTO `s_core_config_forms` (`parent_id`, `name`, `label`, `description`, `position`, `scope`, `plugin_id`) VALUES
(@parentForm , 'CoreLicense', 'Shopware-Lizenz', NULL, 0, 0, NULL);
EOD;
        $this->addSql($sql);

        $sql = <<<'EOD'
SET @form = (SELECT id FROM `s_core_config_forms` WHERE `name` = 'CoreLicense' LIMIT 1);
EOD;
        $this->addSql($sql);

        $sql = <<<'EOD'
SET @localeEnGb = (SELECT id FROM `s_core_locales` WHERE `locale` = 'en_GB' LIMIT 1);
EOD;
        $this->addSql($sql);

        $sql = <<<'EOD'
INSERT IGNORE INTO `s_core_config_form_translations` (`form_id`, `locale_id`, `label`, `description`) VALUES
(@form , @localeEnGb, 'Shopware license', NULL);
EOD;
        $this->addSql($sql);
    }
}
