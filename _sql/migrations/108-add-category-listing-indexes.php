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

class Migrations_Migration108 extends Shopware\Components\Migrations\AbstractMigration
{
    public function up($modus)
    {
        $sql = <<<'EOD'
ALTER TABLE  `s_categories` ADD INDEX  `active_query_builder`
(  `parent` ,  `position` ,  `id` );


ALTER TABLE  `s_articles` ADD INDEX  `articles_by_category_sort_release`
( `datum` ,  `id` );


ALTER TABLE  `s_articles` ADD INDEX  `articles_by_category_sort_name`
(  `name` ,  `id` );


ALTER TABLE  `s_articles_vote` ADD INDEX  `get_articles_votes`
(  `articleID` ,  `active` ,  `datum` );


ALTER TABLE  `s_articles_img` ADD INDEX  `article_images_query`
(  `articleID` ,  `position` );


ALTER TABLE  `s_articles_details` ADD INDEX  `articles_by_category_sort_popularity`
( `sales` ,  `impressions` ,  `articleID` );


ALTER TABLE  `s_core_tax` ADD INDEX  `tax`
( `tax` );


ALTER TABLE  `s_core_tax_rules` ADD INDEX  `tax_rate_by_conditions`
( `customer_groupID` ,  `areaID` ,  `countryID` ,  `stateID` );


ALTER TABLE  `s_cms_static` ADD INDEX  `get_menu`
(  `position` ,  `description` );


ALTER TABLE  `s_core_subscribes` ADD INDEX  `plugin_namespace_init_storage`
(  `type` ,  `subscribe` ,  `position` );


ALTER TABLE  `s_order_notes` ADD INDEX  `basket_count_notes`
(  `sUniqueID` ,  `userID` );


ALTER TABLE  `s_articles_img` ADD INDEX  `article_cover_image_query`
(  `articleID` ,  `main` ,  `position` );

ALTER TABLE  `s_order_basket` ADD INDEX  `get_basket`
(  `sessionID` ,  `id` ,  `datum` );
EOD;

        $this->addSql($sql);
    }
}
