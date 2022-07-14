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

class Migrations_Migration393 extends Shopware\Components\Migrations\AbstractMigration
{
    public function up($modus)
    {
        $this->addSql("SET @formId = (SELECT `id` FROM `s_core_config_forms` WHERE name = 'Frontend100');");
        $this->addSql("SET @elementId = (SELECT id FROM s_core_config_elements WHERE name = 'PageNotFoundDestination' LIMIT 1);");

        $this->addSql("
            INSERT IGNORE INTO `s_core_config_element_translations` (`element_id`, `locale_id`, `label`, `description`)
            VALUES (@elementId, '2', '\"Page not found\" destination', 'When the user requests a non-existent page, he will be shown the following page.' );
        ");

        $this->addSql("SET @elementId = (SELECT id FROM s_core_config_elements WHERE name = 'PageNotFoundCode' LIMIT 1);");

        $this->addSql("
            INSERT IGNORE INTO `s_core_config_element_translations` (`element_id`, `locale_id`, `label`, `description`)
            VALUES (@elementId, '2', '\"Page not found\" error code', 'HTTP code used in \"Page not found\" responses' );
        ");
    }
}
