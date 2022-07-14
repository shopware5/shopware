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

class Migrations_Migration353 extends Shopware\Components\Migrations\AbstractMigration
{
    public function up($modus)
    {
        // Add template
        $sql = <<<'EOD'
INSERT INTO `s_emotion_templates` (`name`, `file`) VALUES
('Horizontales Scrolling', 'horizontal_scrolling.tpl');
INSERT INTO `s_emotion_grid` (`name`, `cols`, `rows`, `cell_height`, `article_height`, `gutter`) VALUES
('Horizontales Scrolling', 40, 8, 185, 2, 10);
EOD;
        $this->addSql($sql);
    }
}
