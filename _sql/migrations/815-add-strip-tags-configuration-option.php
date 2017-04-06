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

class Migrations_Migration815 extends Shopware\Components\Migrations\AbstractMigration
{
    public function up($modus)
    {
        $sql = <<<'SQL'
SET @formId = (SELECT id FROM `s_core_config_forms` WHERE name='InputFilter');
SQL;
        $this->addSql($sql);

        $sql = <<<'SQL'
INSERT INTO `s_core_config_elements` SET
`form_id` = @formId,
`name` = 'strip_tags',
`value` = 'b:1;',
`label` = 'Global strip_tags verwenden',
`description` = 'Wenn aktiviert wird jeder Formularinput im Frontend mittels strip_tags gefiltert.',
`type` = 'checkbox',
`required` = 1,
`position` = 0,
`scope` = 0,
`options` = NULL;
SQL;
        $this->addSql($sql);

        $sql = <<<'SQL'
SET @stripTagsId = (SELECT id FROM s_core_config_elements WHERE form_id = @formId AND `name` = 'strip_tags');
SQL;
        $this->addSql($sql);

        $sql = <<<'SQL'
INSERT IGNORE INTO `s_core_config_element_translations` SET 
`element_id` = @stripTagsId,
`locale_id` = 2,
`label` = 'Use strip_tags globally',
`description` = 'When activated, each form input in the frontend is filtered using strip_tags.';
SQL;
        $this->addSql($sql);
    }
}
