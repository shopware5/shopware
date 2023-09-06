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

class Migrations_Migration410 extends Shopware\Components\Migrations\AbstractMigration
{
    public function up($modus)
    {
        $this->addFullscreenField();
        $this->addModeField();
        $this->addVideoElementFields();
    }

    /**
     * @return string
     */
    protected function addFullscreenField()
    {
        $sql = <<<'EOD'
       ALTER TABLE `s_emotion` ADD `fullscreen` INT NOT NULL DEFAULT '0' ;
EOD;
        $this->addSql($sql);

        return $sql;
    }

    /**
     * @return string
     */
    protected function addModeField()
    {
        $sql = <<<'EOD'
       ALTER TABLE `s_emotion` ADD `mode` VARCHAR(255) NOT NULL DEFAULT 'masonry';
EOD;
        $this->addSql($sql);

        return $sql;
    }

    protected function addVideoElementFields()
    {
        $sql = <<<'EOD'
SET @parent = (SELECT id FROM `s_library_component` WHERE `cls`='emotion--element-video' LIMIT 1);
INSERT IGNORE INTO `s_library_component_field` (`componentID`, `name`, `x_type`, `value_type`, `field_label`, `support_text`, `help_title`, `help_text`, `store`, `display_field`, `value_field`, `default_value`, `allow_blank`, `position`) VALUES
(@parent, 'muted', 'checkbox', '', 'Video stumm schalten', 'Die Ton-Spur des Videos wird stumm geschaltet', '', '', '', '', '', '1', 1, '60'),
(@parent, 'scale', 'numberfield', '', 'Skalierungsfaktor', 'Legen Sie den Skalierungsfaktor für das Video fest', '', '', '', '', '', '1.0', 1, '49'),
(@parent, 'originLeft', 'numberfield', '', 'Linker Ausgangspunkt', 'Legt den linken Ausgangspunkt des Videos fest. Die Angabe erfolgt in Prozent', '', '', '', '', '', '50', 1, '48'),
(@parent, 'originTop', 'numberfield', '', 'Oberer Ausgangspunkt', 'Legt den oberen Ausgangspunkt des Videos fest. Die Angabe erfolgt in Prozent', '', '', '', '', '', '50', 1, '47'),
(@parent, 'overlay', 'textfield', '', 'Video-Overlay Farbe', 'Legen Sie den Overlay für das Video fest. Ein RGBA-Wert wird empfohlen.', '', '', '', '', '', 'rgba(0, 0, 0, .2)', 1, '46');
EOD;
        $this->addSql($sql);
    }
}
