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

class Migrations_Migration618 extends Shopware\Components\Migrations\AbstractMigration
{
    public function up($modus)
    {
        $sql = <<<'EOD'
INSERT INTO `s_media_album_settings` (`albumID`, `create_thumbnails`, `thumbnail_size`, `icon`, `thumbnail_high_dpi`, `thumbnail_quality`, `thumbnail_high_dpi_quality`) VALUES
(-13, 0, '', 'sprite-bin-metal-full', 0, 90, 60) ON DUPLICATE KEY UPDATE `icon` = 'sprite-bin-metal-full';
EOD;

        $this->addSql($sql);
    }
}
