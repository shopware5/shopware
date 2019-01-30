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

class Migrations_Migration1452 extends \Shopware\Components\Migrations\AbstractMigration
{
    public function up($modus)
    {
        $sql = <<<'SQL'
UPDATE s_multi_edit_filter 
SET filter_string = "DETAIL.LASTSTOCK  ISTRUE and DETAIL.INSTOCK <= \"0\" AND ISMAIN"
WHERE filter_string = "   ARTICLE.LASTSTOCK  ISTRUE and DETAIL.INSTOCK <= 0";
SQL;
        $sql2 = <<<'SQL'
UPDATE s_multi_edit_filter
SET `name` = "<b>Abverkauf-Hauptartikel</b><br><small>nicht auf Lager</small>"
WHERE `name` = "<b>Abverkauf</b><br><small>nicht auf Lager</small>";
SQL;
        $sql3 = <<<'SQL'
UPDATE s_multi_edit_filter 
SET description = "Abverkauf-Hauptartikel ohne Lagerbestand"
WHERE description = "Abverkauf-Artikel ohne Lagerbestand";
SQL;
        $sql4 = <<<'SQL'
INSERT INTO s_multi_edit_filter (`name`, `filter_string`, `description`, `created`, `is_favorite`, `is_simple`)
VALUES ('<b>Abverkauf-Variantenartikel</b><br><small>nicht auf Lager</small>','   DETAIL.LASTSTOCK  ISTRUE and DETAIL.INSTOCK <= 0','Abverkauf-Variantenartikel ohne Lagerbestand',NULL,1,0);
SQL;

        $this->addSql($sql);
        $this->addSql($sql2);
        $this->addSql($sql3);
        $this->addSql($sql4);
    }
}
