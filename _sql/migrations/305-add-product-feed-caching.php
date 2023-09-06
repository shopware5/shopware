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

class Migrations_Migration305 extends Shopware\Components\Migrations\AbstractMigration
{
    public function up($modus)
    {
        $sql = <<<'EOD'
            ALTER TABLE  `s_export` ADD  `cache_refreshed` DATETIME NULL DEFAULT NULL ;

            UPDATE s_export
                SET body = '{strip}\n{$sArticle.articleID|escape}{#S#}\n{$sArticle.ordernumber|escape}{#S#}\n{$sArticle.name|strip_tags|strip|truncate:80:"...":true|replace:"|":""} {#S#}\n{$sArticle.tax}{#S#}\n{$sArticle.articleID|category:">"|escape},{$sArticle.supplier}{#S#}\n{$sArticle.weight}{#S#}\n{$sArticle.description|strip_tags|strip|trim|truncate:900:"...":true|html_entity_decode|replace:"|":""|escape}{#S#}\n"{$sArticle.description_long|trim|html_entity_decode|replace:"|":"|"|replace:''"'':''""''}<p>{$sArticle.attr1|regex_replace:"/^(\\d)$/":""|regex_replace:"/^0000-00-00$/":""|strip}<p>{$sArticle.attr2|regex_replace:"/^(\\d)$/":""|regex_replace:"/^0000-00-00$/":""|strip}<p>{$sArticle.attr3|regex_replace:"/^(\\d)$/":""|regex_replace:"/^0000-00-00$/":""|strip}<p>{$sArticle.attr4|regex_replace:"/^(\\d)$/":""|regex_replace:"/^0000-00-00$/":""|strip}<p>{$sArticle.attr5|regex_replace:"/^(\\d)$/":""|regex_replace:"/^0000-00-00$/":""|strip}<p>{$sArticle.attr6|regex_replace:"/^(\\d)$/":""|regex_replace:"/^0000-00-00$/":""|strip}<p>{$sArticle.attr7|regex_replace:"/^(\\d)$/":""|regex_replace:"/^0000-00-00$/":""|strip}<p>{$sArticle.attr8|regex_replace:"/^(\\d)$/":""|regex_replace:"/^0000-00-00$/":""|strip}<p>{$sArticle.attr9|regex_replace:"/^(\\d)$/":""|regex_replace:"/^0000-00-00$/":""|strip}<p>{$sArticle.attr10|regex_replace:"/^(\\d)$/":""|regex_replace:"/^0000-00-00$/":""|strip}<p>{$sArticle.attr11|regex_replace:"/^(\\d)$/":""|regex_replace:"/^0000-00-00$/":""|strip}<p>{$sArticle.attr12|regex_replace:"/^(\\d)$/":""|regex_replace:"/^0000-00-00$/":""|strip}<p>{$sArticle.attr13|regex_replace:"/^(\\d)$/":""|regex_replace:"/^0000-00-00$/":""|strip}<p>{$sArticle.attr14|regex_replace:"/^(\\d)$/":""|regex_replace:"/^0000-00-00$/":""|strip}<p>{$sArticle.attr15|regex_replace:"/^(\\d)$/":""|regex_replace:"/^0000-00-00$/":""|strip}<p>{$sArticle.attr16|regex_replace:"/^(\\d)$/":""|regex_replace:"/^0000-00-00$/":""|strip}<p>{$sArticle.attr17|regex_replace:"/^(\\d)$/":""|regex_replace:"/^0000-00-00$/":""|strip}<p>{$sArticle.attr18|regex_replace:"/^(\\d)$/":""|regex_replace:"/^0000-00-00$/":""|strip}<p>{$sArticle.attr19|regex_replace:"/^(\\d)$/":""|regex_replace:"/^0000-00-00$/":""|strip}<p>{$sArticle.attr20|regex_replace:"/^(\\d)$/":""|regex_replace:"/^0000-00-00$/":""|strip}"{#S#}\n{$sArticle.image|image:5}{#S#}\n{$sArticle.articleID|link:$sArticle.name|replace:"|":""}{#S#}\n{if $sArticle.configurator}0{else}{$sArticle.price|escape:"number"|escape}{/if}{#S#}\n{$sArticle.pseudoprice|escape}{#S#}\nLieferzeit in Tagen: {$sArticle.shippingtime|replace:"0":"sofort"}{#S#}\n{$sArticle.topseller}{#S#}\n{if $sArticle.configurator}"-1"{else}{$sArticle.instock}{/if}{#S#}\n{$sArticle.purchaseunit}{#S#}\n{$sArticle.unit_description}{#S#}\n{$sArticle.suppliernumber}{#S#}\n{$sArticle.supplier}{#S#}\n{$sArticle.active}{#S#}\n{if $sArticle.configurator}{$sArticle.articleID|escape}{else}{/if}\n{/strip}{#L#}'
            WHERE s_export.name = 'Yatego'
                AND body = '{strip}\n{$sArticle.articleID|escape}{#S#}\n{$sArticle.ordernumber|escape}{#S#}\n{$sArticle.name|strip_tags|strip|truncate:80:"...":true|replace:"|":""} {#S#}\n{$sArticle.tax}{#S#}\n{$sArticle.articleID|category:">"|escape},{$sArticle.supplier}{#S#}\n{$sArticle.weight}{#S#}\n{$sArticle.description|strip_tags|strip|trim|truncate:900:"...":true|html_entity_decode|replace:"|":""|escape}{#S#}\n"{$sArticle.description_long|trim|html_entity_decode|replace:"|":"|"|replace:''"'':''""''}<p>{$sArticle.attr1|regex_replace:"/^(\\d)$/":""|regex_replace:"/^0000-00-00$":""|strip}<p>{$sArticle.attr2|regex_replace:"/^(\\d)$/":""|regex_replace:"/^0000-00-00$":""|strip}<p>{$sArticle.attr3|regex_replace:"/^(\\d)$/":""|regex_replace:"/^0000-00-00$":""|strip}<p>{$sArticle.attr4|regex_replace:"/^(\\d)$/":""|regex_replace:"/^0000-00-00$":""|strip}<p>{$sArticle.attr5|regex_replace:"/^(\\d)$/":""|regex_replace:"/^0000-00-00$":""|strip}<p>{$sArticle.attr6|regex_replace:"/^(\\d)$/":""|regex_replace:"/^0000-00-00$":""|strip}<p>{$sArticle.attr7|regex_replace:"/^(\\d)$/":""|regex_replace:"/^0000-00-00$":""|strip}<p>{$sArticle.attr8|regex_replace:"/^(\\d)$/":""|regex_replace:"/^0000-00-00$":""|strip}<p>{$sArticle.attr9|regex_replace:"/^(\\d)$/":""|regex_replace:"/^0000-00-00$":""|strip}<p>{$sArticle.attr10|regex_replace:"/^(\\d)$/":""|regex_replace:"/^0000-00-00$":""|strip}<p>{$sArticle.attr11|regex_replace:"/^(\\d)$/":""|regex_replace:"/^0000-00-00$":""|strip}<p>{$sArticle.attr12|regex_replace:"/^(\\d)$/":""|regex_replace:"/^0000-00-00$":""|strip}<p>{$sArticle.attr13|regex_replace:"/^(\\d)$/":""|regex_replace:"/^0000-00-00$":""|strip}<p>{$sArticle.attr14|regex_replace:"/^(\\d)$/":""|regex_replace:"/^0000-00-00$":""|strip}<p>{$sArticle.attr15|regex_replace:"/^(\\d)$/":""|regex_replace:"/^0000-00-00$":""|strip}<p>{$sArticle.attr16|regex_replace:"/^(\\d)$/":""|regex_replace:"/^0000-00-00$":""|strip}<p>{$sArticle.attr17|regex_replace:"/^(\\d)$/":""|regex_replace:"/^0000-00-00$":""|strip}<p>{$sArticle.attr18|regex_replace:"/^(\\d)$/":""|regex_replace:"/^0000-00-00$":""|strip}<p>{$sArticle.attr19|regex_replace:"/^(\\d)$/":""|regex_replace:"/^0000-00-00$":""|strip}<p>{$sArticle.attr20|regex_replace:"/^(\\d)$/":""|regex_replace:"/^0000-00-00$":""|strip}"{#S#}\n{$sArticle.image|image:5}{#S#}\n{$sArticle.articleID|link:$sArticle.name|replace:"|":""}{#S#}\n{if $sArticle.configurator}0{else}{$sArticle.price|escape:"number"|escape}{/if}{#S#}\n{$sArticle.pseudoprice|escape}{#S#}\nLieferzeit in Tagen: {$sArticle.shippingtime|replace:"0":"sofort"}{#S#}\n{$sArticle.topseller}{#S#}\n{if $sArticle.configurator}"-1"{else}{$sArticle.instock}{/if}{#S#}\n{$sArticle.purchaseunit}{#S#}\n{$sArticle.unit_description}{#S#}\n{$sArticle.suppliernumber}{#S#}\n{$sArticle.supplier}{#S#}\n{$sArticle.active}{#S#}\n{if $sArticle.configurator}{$sArticle.articleID|escape}{else}{/if}\n{/strip}{#L#}'
            ;
            UPDATE s_export
                SET `interval` = 0
                WHERE `interval` = 3456
                AND name = 'Google Produktsuche';
EOD;

        $this->addSql($sql);
    }
}
