{extends file='parent:frontend/listing/box_article.tpl'}

{* Unit price *}
{block name='frontend_listing_box_article_unit'}
{/block}

{* Article Price *}
{block name='frontend_listing_box_article_price'}
	<p class="points"{if $sBonusSystem.points.remaining >= $sArticle.required_points}{else} style="bottom: 60px"{/if}>
		<span class="{if $sBonusSystem.points.remaining >= $sArticle.required_points}enough{else}not_enough{/if}">{s namespace="frontend/bonus_system" name="ForXBonusPoints"}f&uuml;r <strong>{$sArticle.required_points}</strong> Punkte{/s}</span>
		{if $sBonusSystem.points.remaining < $sArticle.required_points}
			<br><span class="further">{s namespace="frontend/bonus_system"  name="FurtherBonusPoints"}(noch {$sArticle.required_points - $sBonusSystem.points.remaining} Punkte){/s}</span>
		{/if}
	</p>
{/block}

{* Compare and more *}
{block name='frontend_listing_box_article_actions'}
	<div class="actions">
		{* Buy now button *}
		{if $sBonusSystem.points.remaining >= $sArticle.required_points}
			{if !$sArticle.sConfigurator && !$sArticle.variants && !$sArticle.sVariantArticle && !$sArticle.laststock == 1}
				<a href="{url controller='checkout' action='addArticle' sAdd=$sArticle.ordernumber buy_for='points' points_per_unit=$sArticle.required_points}" title="{s namespace="frontend/bonus_system" name='ListingBoxLinkBuyBonusArticle'}{/s}" class="buynow">{s namespace="frontend/bonus_system" name='ListingBoxLinkBuyBonusArticle'}Jetzt Bonusartikel sichern{/s}</a>
			{/if}
		{/if}
		{* More informations button *}
		<a href="{$sArticle.linkDetails|rewrite:$sArticle.articleName}" title="{$sArticle.articleName}" class="more">{s namespace="frontend/bonus_system" name='ListingBoxLinkDetails'}Zum Produkt{/s}</a>
	</div>
{/block}
