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

class Migrations_Migration731 extends Shopware\Components\Migrations\AbstractMigration
{
    /**
     * @param string $modus
     *
     * @return void
     */
    public function up($modus)
    {
        $this->addSql('ALTER TABLE `s_emotion` ADD `cell_spacing` INT NOT NULL AFTER `cols`');

        $sql = <<<'EOD'
UPDATE `s_emotion` AS e
INNER JOIN s_emotion_grid AS eg
ON e.grid_id = eg.id SET
e.cols = eg.cols,
e.rows = eg.rows,
e.cell_spacing = eg.gutter,
e.cell_height = eg.cell_height,
e.article_height = eg.article_height
EOD;

        $this->addSql($sql);

        $this->addSql('ALTER TABLE `s_emotion` DROP `grid_id`');

        $this->addSql('DROP TABLE IF EXISTS s_emotion_grid');
    }
}
