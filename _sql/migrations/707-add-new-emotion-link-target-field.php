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

class Migrations_Migration707 extends Shopware\Components\Migrations\AbstractMigration
{
    /**
     * @param string $modus
     *
     * @return void
     */
    public function up($modus)
    {
        $this->addSql("SET @componentID = (SELECT `id` FROM `s_library_component` WHERE `x_type` = 'emotion-components-banner' LIMIT 1);");

        $sql = <<<'EOD'
INSERT IGNORE INTO `s_library_component_field` (`componentID`, `name`, `x_type`, `field_label`, `allow_blank`, `position`) VALUES
(@componentID, 'banner_link_target', 'emotion-components-fields-link-target', 'Link-Ziel', 1, 48);
EOD;
        $this->addSql($sql);
    }
}
