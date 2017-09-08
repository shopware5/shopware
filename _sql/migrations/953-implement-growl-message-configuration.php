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

class Migrations_Migration953 extends Shopware\Components\Migrations\AbstractMigration
{
    public function up($modus)
    {
        $sql = <<<'EOF'
SET @formId = (SELECT id FROM s_core_config_forms WHERE name = 'Auth' LIMIT 1);

INSERT INTO `s_core_config_elements` (`id`, `form_id`, `name`, `value`, `label`, `description`, `type`, `required`, `position`, `scope`, `options`)
VALUES (NULL, @formId, 'growlMessageDisplayPosition', 's:9:"top-right";', 'Benachrichtigungs Position', 'Mit dieser Option können die Backend Benachrichtungen an einer anderen Stelle angezeigt werden (Backend Cache leeren und Neuladen des Backends erforderlich)', 'select', '1', '0', '0', 'a:5:{s:8:"editable";b:0;s:10:"valueField";s:8:"position";s:12:"displayField";s:11:"displayName";s:9:"queryMode";s:5:"local";s:5:"store";s:19:"base.CornerPosition";}');
EOF;
        $this->addSql($sql);

        $sql = <<<'EOF'
        SET @elementId = (SELECT id FROM s_core_config_elements WHERE name = 'growlMessageDisplayPosition' LIMIT 1);

        INSERT INTO `s_core_config_element_translations` (`id`, `element_id`, `locale_id`, `label`, `description`)
        VALUES (NULL, @elementId, '2', 'Notification position', 'With this option the backend notifications can be displayed at different positions (backend cache needs to be cleared and the backend must be reloaded)');
EOF;
        $this->addSql($sql);
    }
}
