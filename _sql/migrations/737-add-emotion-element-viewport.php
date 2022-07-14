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

class Migrations_Migration737 extends Shopware\Components\Migrations\AbstractMigration
{
    /**
     * @param string $modus
     *
     * @return void
     */
    public function up($modus)
    {
        $sql = <<<SQL
CREATE TABLE IF NOT EXISTS `s_emotion_element_viewports` (
    `id` INT(11) NOT NULL AUTO_INCREMENT,
    `elementID` INT(11) NOT NULL,
    `emotionID` INT(11) NOT NULL,
    `alias` VARCHAR(255) COLLATE utf8_unicode_ci NOT NULL,
    `start_row` INT(11) NOT NULL,
    `start_col` INT(11) NOT NULL,
    `end_row` INT(11) NOT NULL,
    `end_col` INT(11) NOT NULL,
    `visible` INT(1) NOT NULL DEFAULT 1,
    PRIMARY KEY (`id`)) ENGINE = InnoDB
    DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
SQL;

        $this->addSql($sql);
    }
}
