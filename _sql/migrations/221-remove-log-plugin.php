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

class Migrations_Migration221 extends Shopware\Components\Migrations\AbstractMigration
{
    public function up($modus)
    {
        $this->deletePluginByName('Log');
    }

    public function deletePluginByName($name)
    {
        $sql = <<<EOD
DELETE p, s, cf, ce, cev, cet
FROM s_core_plugins p
LEFT JOIN s_core_subscribes s
    ON p.id = s.pluginID
LEFT JOIN s_core_config_forms cf
    ON p.id = cf.plugin_id
LEFT JOIN s_core_config_elements ce
    on cf.id = ce.form_id
LEFT JOIN s_core_config_values cev
    on ce.id = cev.element_id
LEFT JOIN s_core_config_element_translations cet
    on ce.id = cet.element_id
WHERE p.name LIKE '$name'
EOD;

        $this->addSql($sql);
    }
}
