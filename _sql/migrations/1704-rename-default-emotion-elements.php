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

class Migrations_Migration1704 extends Shopware\Components\Migrations\AbstractMigration
{
    public function up($modus)
    {
        $updates = [
            ['Artikel', 'product'],
            ['Kategorie-Teaser', 'category_teaser'],
            ['Blog-Artikel', 'blog_article'],
            ['Banner', 'banner'],
            ['Banner-Slider', 'banner_slider'],
            ['Youtube-Video', 'youtube'],
            ['Hersteller-Slider', 'manufacturer_slider'],
            ['Artikel-Slider', 'product_slider'],
            ['HTML-Element', 'html_element'],
            ['iFrame-Element', 'iframe'],
            ['HTML5 Video-Element', 'html_video'],
            ['Content Type', 'content_type'],
            ['Code Element', 'code_element'],
        ];

        foreach ($updates as $update) {
            $sql = sprintf('UPDATE s_library_component component SET component.name = "%s" WHERE component.name = "%s"', $update[1], $update[0]);
            $this->addSql($sql);
        }

        $sql = 'ALTER TABLE `s_library_component` ADD UNIQUE INDEX `name_idx` (`name`, `pluginID`)';
        $this->addSql($sql);
    }
}
