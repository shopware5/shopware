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

class Migrations_Migration481 extends Shopware\Components\Migrations\AbstractMigration
{
    public function up($modus)
    {
        $sql = <<<SQL
UPDATE s_core_config_elements
SET form_id = (SELECT id FROM `s_core_config_forms` WHERE `name`='Frontend33' LIMIT 1)
WHERE name = 'vatcheckrequired';
SQL;
        $this->addSql($sql);

        $sql = <<<SQL
DELETE form, form_trans, elem, elem_trans
FROM s_core_config_forms form
LEFT JOIN s_core_config_form_translations form_trans ON form.id = form_trans.form_id
LEFT JOIN s_core_config_elements elem ON form.id = elem.form_id
LEFT JOIN s_core_config_element_translations elem_trans ON elem.id = elem_trans.element_id
WHERE form.name = 'Frontend101';
SQL;
        $this->addSql($sql);
    }
}
