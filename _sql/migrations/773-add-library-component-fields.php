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

class Migrations_Migration773 extends Shopware\Components\Migrations\AbstractMigration
{
    /**
     * @param string $modus
     *
     * @return void
     */
    public function up($modus)
    {
        $this->addSql('SET @componentId = (SELECT `id` FROM `s_library_component` WHERE `name` = "Youtube-Video")');
        $this->addSql('SET @maxNumberPosition = (SELECT MAX(position) FROM `s_library_component_field` WHERE `componentID`=@componentId) + 1;');
        $sql = <<<'EOD'
INSERT IGNORE INTO `s_library_component_field` (`id`, `componentID`, `name`, `x_type`, `value_type`, `field_label`, `support_text`, `help_title`, `help_text`, `store`, `display_field`, `value_field`, `default_value`, `allow_blank`, `translatable`, `position`)
VALUES
(null, @componentId, 'video_autoplay', 'checkbox', '', 'Video automatisch starten', '', '', '', '', '', '', 0, 0, 0, @maxNumberPosition),
(null, @componentId, 'video_related', 'checkbox', '', 'Empfehlungen ausblenden', '', '', '', '', '', '', 0, 0, 0, @maxNumberPosition+1),
(null, @componentId, 'video_controls', 'checkbox', '', 'Steuerung ausblenden', '', '', '', '', '', '', 0, 0, 0, @maxNumberPosition+2),
(null, @componentId, 'video_start', 'numberfield', '', 'Starten nach x-Sekunden', '', '', '', '', '', '', '', 1, 0, @maxNumberPosition+3),
(null, @componentId, 'video_end', 'numberfield', '', 'Stoppen nach x-Sekunden', '', '', '', '', '', '', '', 1, 0, @maxNumberPosition+4),
(null, @componentId, 'video_info', 'checkbox', '', 'Info ausblenden', '', '', '', '', '', '', 0, 0, 0, @maxNumberPosition+5),
(null, @componentId, 'video_branding', 'checkbox', '', 'Branding ausblenden', '', '', '', '', '', '', 0, 0, 0, @maxNumberPosition+6),
(null, @componentId, 'video_loop', 'checkbox', '', 'Loop aktivieren', '', '', 'Loop ist nicht mit Start- und Endzeiten kompatibel. Video wird wieder von Beginn abgespielt.', '', '', '', 0, 0, 0, @maxNumberPosition+7)
;
EOD;
        $this->addSql($sql);
    }
}
