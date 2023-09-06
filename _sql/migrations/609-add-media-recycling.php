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

class Migrations_Migration609 extends Shopware\Components\Migrations\AbstractMigration
{
    public function up($modus)
    {
        $this->addAlbum();
        $this->createCronJob();
    }

    private function createCronJob()
    {
        $sql = <<<SQL
            INSERT INTO s_crontab (`name`, `action`, `next`, `start`, `interval`, `active`, `end`, `pluginID`)
            VALUES ('Media Garbage Collector', 'MediaCrawler', now(), NULL, 86400, 0, now(), NULL)
SQL;

        $this->addSql($sql);
    }

    private function addAlbum()
    {
        $sql = <<<SQL
            INSERT INTO `s_media_album` (`id`, `name`, `parentID`, `position`) VALUES
            (-13, 'Papierkorb', NULL, 12);
SQL;

        $this->addSql($sql);
    }
}
