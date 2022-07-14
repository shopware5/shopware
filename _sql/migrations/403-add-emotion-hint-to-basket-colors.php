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

class Migrations_Migration403 extends Shopware\Components\Migrations\AbstractMigration
{
    public function up($modus)
    {
        $sql = <<<'EOD'
            UPDATE
                `s_core_config_element_translations` `translations`,
                `s_core_config_elements` `elements`,
                `s_core_config_forms` `forms`,
                `s_core_locales` `locales`
            SET
                `translations`.`description`= '(Hex-Code, only applies to the Emotion template)',
                `elements`.`description`= '(Hex-Code, betrifft nur das Emotion-Template)'
            WHERE
                `translations`.`element_id` = `elements`.`id`
            AND `elements`.`label` LIKE 'Warenkorb%farbe'
            AND `elements`.`form_id` = `forms`.`id`
            AND `forms`.`label` = 'Bestellabschluss'
            AND `translations`.`locale_id` = `locales`.`id`
            AND `locales`.`locale` = 'en_GB'
EOD;

        $this->addSql($sql);
    }
}
