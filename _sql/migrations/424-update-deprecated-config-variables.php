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

class Migrations_Migration424 extends Shopware\Components\Migrations\AbstractMigration
{
    public function up($modus)
    {
        $sql = "UPDATE s_core_config_elements SET description = 'Betrifft nur das Emotion-Template'
            WHERE s_core_config_elements.name LIKE 'paymentEditingInCheckoutPage' AND description IS NULL";
        $this->addSql($sql);

        $sql = "UPDATE s_core_config_elements SET description = 'Betrifft nur das Emotion-Template'
            WHERE s_core_config_elements.name LIKE 'showbundlemainarticle' AND description IS NULL";
        $this->addSql($sql);

        $sql = <<<SQL
            UPDATE
                `s_core_config_element_translations` `translations`,
                `s_core_config_elements` `elements`,
                `s_core_config_forms` `forms`,
                `s_core_locales` `locales`
            SET
                `translations`.`description`= 'Only applies to the Emotion template'
            WHERE
                `translations`.`element_id` = `elements`.`id`
            AND `elements`.`name` IN ('showbundlemainarticle', 'paymentEditingInCheckoutPage')
            AND `elements`.`form_id` = `forms`.`id`
            AND `translations`.`locale_id` = `locales`.`id`
            AND `locales`.`locale` = 'en_GB'
SQL;
        $this->addSql($sql);
    }
}
