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

class Migrations_Migration208 extends Shopware\Components\Migrations\AbstractMigration
{
    public function up($modus)
    {
        $sql = <<<'EOD'
        ALTER TABLE  `s_core_snippets`
        ADD  `dirty` int(1) NULL DEFAULT  '0' ;

        UPDATE `s_core_snippets`
        SET dirty = 1
        WHERE created <> updated AND updated NOT IN (
            "2010-09-28 11:54:19",
            "2010-10-06 12:28:47",
            "2010-10-07 23:31:10",
            "2010-10-07 23:31:45",
            "2010-10-09 09:50:03",
            "2010-10-12 19:45:13",
            "2010-10-14 14:45:36",
            "2010-10-15 12:58:20",
            "2010-10-15 12:58:35",
            "2010-10-15 13:18:53",
            "2010-10-15 13:22:00",
            "2010-10-15 13:23:33",
            "2010-10-15 13:24:52",
            "2010-10-15 13:25:20",
            "2010-10-15 13:26:59",
            "2010-10-15 16:59:48",
            "2010-10-15 17:02:04",
            "2010-10-15 17:02:07",
            "2010-10-16 08:52:39",
            "2010-10-16 10:37:58",
            "2010-10-16 11:26:45",
            "2010-10-16 11:26:56",
            "2010-10-16 16:47:26",
            "2010-10-16 16:47:29",
            "2010-10-16 16:47:33",
            "2010-10-16 16:47:40",
            "2010-10-16 16:51:57",
            "2010-10-17 12:08:50",
            "2010-10-17 18:49:19",
            "2010-10-17 18:49:27",
            "2010-10-17 18:54:46",
            "2010-10-17 19:01:26",
            "2010-10-17 19:02:20",
            "2010-10-18 00:56:36",
            "2010-10-18 00:57:08",
            "2010-10-18 00:57:25",
            "2010-10-18 00:57:55",
            "2010-10-18 00:58:11",
            "2011-03-31 11:48:56",
            "2011-04-01 11:36:08",
            "2011-04-01 11:36:28",
            "2011-04-01 11:36:30",
            "2011-04-01 11:39:24",
            "2011-04-01 11:39:25",
            "2011-04-01 11:39:26",
            "2011-04-01 11:39:30",
            "2011-04-01 11:39:31",
            "2011-04-01 11:40:07",
            "2011-04-01 11:40:47",
            "2011-04-01 11:41:08",
            "2011-04-01 11:42:04",
            "2011-04-01 11:42:15",
            "2011-05-24 10:31:47",
            "2011-05-24 10:33:55",
            "2011-05-24 11:26:33",
            "2011-05-24 11:26:46",
            "2011-05-24 11:51:36",
            "2011-05-24 11:51:46",
            "2011-05-24 13:51:56",
            "2011-05-24 13:52:14",
            "2011-05-24 14:22:59",
            "2011-05-24 17:13:52",
            "2012-06-25 16:54:02"
        );
EOD;
        $this->addSql($sql);
    }
}
