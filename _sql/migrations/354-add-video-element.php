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

class Migrations_Migration354 extends Shopware\Components\Migrations\AbstractMigration
{
    public function up($modus)
    {
        $sql = <<<'EOD'
INSERT INTO `s_library_component` (`name`, `x_type`, `convert_function`, `description`, `template`, `cls`, `pluginID`) VALUES
('HTML5 Video-Element', '', NULL, '', 'component_video', 'emotion--element-video', NULL);
SET @parent = (SELECT id FROM `s_library_component` WHERE `cls`='emotion--element-video' LIMIT 1);
INSERT INTO `s_library_component_field` (`componentID`, `name`, `x_type`, `value_type`, `field_label`, `support_text`, `help_title`, `help_text`, `store`, `display_field`, `value_field`, `default_value`, `allow_blank`) VALUES
(@parent, 'webm_video', 'mediafield', '', 'WebM-Video', 'Video für Google Chrome', '', '', '', '', '', '', 0),
(@parent, 'ogg_video', 'mediafield', '', 'Ogg Theora-Video', 'Video für Firefox', '', '', '', '', '', '', 0),
(@parent, 'h264_video', 'mediafield', '', 'H264-Video', 'H.264 Video für Safari', '', '', '', '', '', '', 0),
(@parent, 'fallback_picture', 'mediafield', '', 'Fallback-Bild', 'Fallback Bild, wenn das Video geladen wird', '', '', '', '', '', '', 0),
(@parent, 'html_text', 'tinymce', '', 'Text', 'Text, der auf dem Video angezeigt wird.', '', '', '', '', '', '', 1),
(@parent, 'autoplay', 'checkbox', '', 'Video automatisch abspielen', '', '', '', '', '', '', '1', 1),
(@parent, 'autobuffer', 'checkbox', '', 'Video automatisch vorladen', '', '', '', '', '', '', '1', 1),
(@parent, 'controls', 'checkbox', '', 'Video-Steuerung anzeigen', '', '', '', '', '', '', '1', 1),
(@parent, 'loop', 'checkbox', '', 'Video schleifen', 'Das Video wird in einer Dauerschleife angezeigt', '', '', '', '', '', '1', 1);
EOD;

        $this->addSql($sql);
    }
}
