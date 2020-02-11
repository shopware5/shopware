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

use Shopware\Components\Migrations\AbstractMigration;

class Migrations_Migration1457 extends AbstractMigration
{
    /**
     * {@inheritdoc}
     */
    public function up($modus)
    {
        $sql = <<<SQL
        SET @form_id = (SELECT form.id FROM s_core_config_elements element JOIN s_core_config_forms form ON form.id = element.`form_id` WHERE form.`name` = "Search" AND element.name = "minsearchlenght" LIMIT 1);
INSERT INTO `s_core_config_elements` (`form_id`, `name`, `value`, `label`, `description`, `type`, `required`, `position`, `scope`, `options`)
VALUES
    (@form_id, 'minSearchIndexLength', 'i:3;', 'Minimale Keyword-Länge für die Indexierung', 'Diese Einstellung bestimmt die minimale Keyword-Länge für die Indexierung. <b>Standard: 3 Zeichen</b>', 'number', 0, 0, 0, NULL);

SET @element_id = (SELECT element.id FROM s_core_config_elements element WHERE element.name = "minSearchIndexLength" LIMIT 1);
INSERT INTO `s_core_config_element_translations` (`element_id`, `locale_id`, `label`, `description`)
VALUES
    (@element_id, 2, 'Minimal keyword length for indexation', 'This setting defines the minimal keyword length for indexation. <b>Default: 3 characters</b>')
    ;
SQL;
        $this->addSql($sql);

        if ($modus === self::MODUS_UPDATE) {
            $sql = "INSERT INTO `s_core_config_values` (`element_id`, `shop_id`, `value`)
                    SELECT
                      @element_id,
                      `id`,
                      'i:0;'
                    FROM s_core_shops
                    WHERE id NOT IN (SELECT `shop_id` FROM `s_core_config_values` WHERE `element_id` = @element_id)";
            $this->addSql($sql);
        }
    }
}
