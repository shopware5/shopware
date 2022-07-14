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

class Migrations_Migration450 extends Shopware\Components\Migrations\AbstractMigration
{
    public function up($modus)
    {
        $this->updateAlbumSettings();
        if ($modus === self::MODUS_INSTALL) {
            $this->updateArticleAlbum();
            $this->updateEmotionAlbum();
            $this->updateBannerAlbum();
            $this->updateBlogAlbum();
        }
    }

    private function updateAlbumSettings()
    {
        $sql = <<<'EOD'
ALTER TABLE `s_media_album_settings`
ADD `thumbnail_high_dpi` INT(1) NULL ,
ADD `thumbnail_quality` INT NULL ,
ADD `thumbnail_high_dpi_quality` INT NULL ;
EOD;
        $this->addSql($sql);

        $sql = <<<'EOD'
UPDATE `s_media_album_settings` SET `thumbnail_high_dpi` = 0, `thumbnail_quality` = 90, `thumbnail_high_dpi_quality` = 60;
EOD;
        $this->addSql($sql);
    }

    private function updateEmotionAlbum()
    {
        $sql = <<<SQL
UPDATE `s_media_album_settings`
SET `create_thumbnails` = 1, `thumbnail_size` = '800x800;1280x1280;1920x1920', `thumbnail_high_dpi` = 1
WHERE albumID = (SELECT id FROM s_media_album WHERE `name` = 'Einkaufswelten');
SQL;
        $this->addSql($sql);
    }

    private function updateBannerAlbum()
    {
        $sql = <<<SQL
UPDATE `s_media_album_settings`
SET `create_thumbnails` = 1, `thumbnail_size` = '800x800;1280x1280;1920x1920', `thumbnail_high_dpi` = 1
WHERE albumID = (SELECT id FROM s_media_album WHERE `name` = 'Banner');
SQL;
        $this->addSql($sql);
    }

    private function updateArticleAlbum()
    {
        $sql = <<<SQL
UPDATE `s_media_album_settings`
SET `create_thumbnails` = 1, `thumbnail_size` = '200x200;600x600;1280x1280', `thumbnail_high_dpi` = 1
WHERE albumID = (SELECT id FROM s_media_album WHERE `name` = 'Artikel');
SQL;
        $this->addSql($sql);
    }

    private function updateBlogAlbum()
    {
        $sql = <<<SQL
UPDATE `s_media_album_settings`
SET `create_thumbnails` = 1, `thumbnail_size` = '200x200;600x600;1280x1280', `thumbnail_high_dpi` = 1
WHERE albumID = (SELECT id FROM s_media_album WHERE `name` = 'Blog');
SQL;
        $this->addSql($sql);
    }
}
