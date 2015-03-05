<?php
class Migrations_Migration464 Extends Shopware\Components\Migrations\AbstractMigration
{
    public function up($modus)
    {
        if ($modus !== self::MODUS_INSTALL) {
            return;
        }
        
        $sql = <<<'SQL'
            UPDATE `s_export` 
            SET `body` = '{strip}\n{$sArticle.articleID|link:$sArticle.name|escape}{#S#}\n{$sArticle.name|escape|truncate:70}{#S#}\n{$sArticle.description_long|strip_tags|strip|trim|truncate:150:"...":true|html_entity_decode|escape}{#S#}\n{$sArticle.price|escape:"number"}{#S#}\n{$sArticle.ordernumber}{#S#}\n{$sArticle.image|image:1|escape}{#S#}\n{if $sArticle.instock}001{else}002{/if}{#S#}\n{$sArticle|@shippingcost:"prepayment":"de":"Deutsche Post Standard"|escape:"number"}\n{/strip}{#L#}'
            WHERE `name` = 'Kelkoo';
SQL;
        $this->addSql($sql);

        $sql = <<<'SQL'
            UPDATE `s_export` 
            SET `body` = '{strip}\n{$sArticle.ordernumber}{#S#}\n{$sArticle.supplier|escape}{#S#}\n{$sArticle.suppliernumber|escape}{#S#}\n{$sArticle.ean|escape}{#S#}\n{$sArticle.name|strip_tags|strip|truncate:80:"...":true|escape}{#S#}\n{$sArticle.description_long|strip_tags|strip|trim|truncate:900:"...":true|html_entity_decode|escape}{#S#}\n{$sArticle.articleID|category:">"|escape}{#S#}\n{$sArticle.price|escape:number}{#S#}\n{if $sArticle.purchaseunit}{$sArticle.price/$sArticle.purchaseunit*$sArticle.referenceunit|escape:number} {"\\x80"} / {$sArticle.referenceunit} {$sArticle.unit}{/if}{#S#}\n{$sArticle.articleID|link:$sArticle.name|escape}{#S#}\n{$sArticle.image|image:1}{#S#}\n{if $sArticle.instock}2 Tage{elseif $sArticle.shippingtime}{$sArticle.shippingtime} Tage{else}10 Tage{/if}{#S#}\n{$sArticle|@shippingcost:"prepayment":"de"|escape:number}{#S#}\n\n{/strip}{#L#}'
            WHERE `name` = 'billiger.de';
SQL;
        $this->addSql($sql);

        $sql = <<<'SQL'
            UPDATE `s_export` 
            SET `body` = '{strip}\n{$sArticle.ordernumber|escape}{#S#}\n{$sArticle.supplier|escape}{#S#}\n{$sArticle.name|strip_tags|strip|truncate:80:"...":true|escape}{#S#}\n{$sArticle.articleID|category:">"|escape}{#S#}\n{$sArticle.description_long|strip_tags|strip|trim|truncate:900:"...":true|html_entity_decode|escape}{#S#}\n{$sArticle.image|image:0}{#S#}\n{$sArticle.articleID|link:$sArticle.name|escape}{#S#}\n{if $sArticle.instock}2 Tage{elseif $sArticle.shippingtime}{$sArticle.shippingtime} Tage{else}10 Tage{/if}{#S#}\n{$sArticle|@shippingcost:"prepayment":"de"|escape:"number"}{#S#}\n{$sArticle.price|escape:"number"}{#S#}\n{#S#}\n{/strip}{#L#}'
            WHERE `name` = 'Ciao';
SQL;
        $this->addSql($sql);
        
        $sql = <<<'SQL'
            UPDATE `s_export` 
            SET `body` = '{strip}\n{$sArticle.ordernumber|escape}{#S#}\n{$sArticle.supplier|escape}{#S#}\n{$sArticle.name|strip_tags|strip|truncate:80:"...":true|escape}{#S#}\n{$sArticle.articleID|category:">"|escape}{#S#}\n{$sArticle.description_long|strip_tags|strip|trim|truncate:900:"...":true|html_entity_decode|escape}{#S#}\n{$sArticle.image|image:0|escape}{#S#}\n{$sArticle.articleID|link:$sArticle.name|escape}{#S#}\n{if $sArticle.instock}2 Tage{elseif $sArticle.shippingtime}{$sArticle.shippingtime} Tage{else}10 Tage{/if}{#S#}\n{$sArticle.releasedate|escape}{#S#}\n{$sArticle|@shippingcost:"prepayment":"de":"Deutsche Post Standard"|escape:"number"}{#S#}\n{$sArticle.price|escape:"number"}{#S#}\n{#S#}\n{/strip}{#L#}\n\n'
            WHERE `name` = 'Pangora';
SQL;
        $this->addSql($sql);

        $sql = <<<'SQL'
            UPDATE `s_export` 
            SET `body` = '<item> \n    <g:id>{$sArticle.articleID|escape}</g:id>\n	<title>{$sArticle.name|strip_tags|strip|truncate:80:"...":true|escape}</title>\n	<description>{$sArticle.description_long|strip_tags|strip|truncate:900:"..."|escape}</description>\n	<g:google_product_category>Wählen Sie hier Ihre Google Produkt-Kategorie</g:google_product_category>\n	<g:product_type>{$sArticle.articleID|category:" > "|escape}</g:product_type>\n	<link>{$sArticle.articleID|link:$sArticle.name|escape}</link>\n	<g:image_link>{$sArticle.image|image:0}</g:image_link>\n	<g:condition>neu</g:condition>\n	<g:availability>{if $sArticle.esd}bestellbar{elseif $sArticle.instock>0}bestellbar{elseif $sArticle.releasedate && $sArticle.releasedate|strtotime > $smarty.now}vorbestellt{elseif $sArticle.shippingtime}bestellbar{else}nicht auf lager{/if}</g:availability>\n	<g:price>{$sArticle.price|format:"number"}</g:price>\n	<g:brand>{$sArticle.supplier|escape}</g:brand>\n	<g:gtin>{$sArticle.suppliernumber|replace:"|":""}</g:gtin>\n	<g:mpn>{$sArticle.suppliernumber|escape}</g:mpn>\n	<g:shipping>\n       <g:country>DE</g:country>\n       <g:service>Standard</g:service>\n       <g:price>{$sArticle|@shippingcost:"prepayment":"de"|escape:number}</g:price>\n    </g:shipping>\n  {if $sArticle.changed}<pubDate>{$sArticle.changed|date_format:"%a, %d %b %Y %T %Z"}</pubDate>{/if}		\n</item>'
            WHERE `name` = 'Google Produktsuche XML';
SQL;
        $this->addSql($sql);

        $sql = <<<'SQL'
            UPDATE `s_export` 
            SET `body` = '<item> \n	<title>{$sArticle.name|strip_tags|htmlspecialchars_decode|strip|escape}</title>\n	<guid>{$sArticle.articleID|link:$sArticle.name|escape}</guid>\n	<link>{$sArticle.articleID|link:$sArticle.name}</link>\n	<description>{if $sArticle.image}\n		<a href="{$sArticle.articleID|link:$sArticle.name}" style="border:0 none;">\n			<img src="{$sArticle.image|image:0}" align="right" style="padding: 0pt 0pt 12px 12px; float: right;" />\n		</a>\n{/if}\n		{$sArticle.description_long|strip_tags|regex_replace:"/[^\\wöäüÖÄÜß .?!,&:%;\\-\\"'']/i":""|trim|truncate:900:"..."|escape}\n	</description>\n	<category>{$sArticle.articleID|category:">"|htmlspecialchars_decode|escape}</category>\n{if $sArticle.changed} 	{assign var="sArticleChanged" value=$sArticle.changed|strtotime}<pubDate>{"r"|date:$sArticleChanged}</pubDate>{"rn"}{/if}\n</item>{#L#}'
            WHERE `name` = 'RSS Feed-Template';
SQL;
        $this->addSql($sql);
    }
}


