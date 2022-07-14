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

class Migrations_Migration136 extends Shopware\Components\Migrations\AbstractMigration
{
    public function up($modus)
    {
        $sql = <<<'EOD'
        SET @parent = (SELECT id FROM s_core_config_forms WHERE name = 'Checkout' LIMIT 1);

        INSERT IGNORE INTO `s_core_config_elements` (`id`, `form_id`, `name`, `value`, `label`, `description`, `type`, `required`, `position`, `scope`, `filters`, `validators`, `options`) VALUES
        (NULL, @parent, 'sendOrderMail', 'b:1;', 'Bestell-Abschluss-eMail versenden', NULL, 'checkbox', 0, 0, 1, NULL, NULL, 'a:0:{}');

        SET @elementId = (SELECT id FROM `s_core_config_elements` WHERE `name` = 'sendOrderMail' LIMIT 1);
        INSERT IGNORE INTO `s_core_config_element_translations` (`element_id`, `locale_id`, `label`)
        VALUES (@elementId, '2', 'Send order mail');
EOD;
        $this->addSql($sql);
    }
}
