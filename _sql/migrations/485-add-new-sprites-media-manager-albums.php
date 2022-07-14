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

class Migrations_Migration485 extends Shopware\Components\Migrations\AbstractMigration
{
    public function up($modus)
    {
        $this->updateAlbumIcon(-12, 'sprite-hard-hat');
        $this->updateAlbumIcon(-11, 'sprite-leaf');
        $this->updateAlbumIcon(-5, 'sprite-inbox-document-text');
        $this->updateAlbumIcon(-4, 'sprite-target');
        $this->updateAlbumIcon(-3, 'sprite-target');
        $this->updateAlbumIcon(-2, 'sprite-pictures');
        $this->updateAlbumIcon(-1, 'sprite-inbox');
    }

    private function updateAlbumIcon($albumId, $icon)
    {
        $sql = <<< SQL
            UPDATE s_media_album_settings
            SET icon = '$icon'
            WHERE albumID = $albumId
            AND icon = 'sprite-blue-folder';
SQL;
        $this->addSql($sql);
    }
}
