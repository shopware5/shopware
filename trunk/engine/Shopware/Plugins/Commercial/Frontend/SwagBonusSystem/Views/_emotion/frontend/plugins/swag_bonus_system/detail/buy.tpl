{* display the radio boxes "buy for euro" and "buy for points" *}
{block name='frontend_detail_buy_button' prepend}
	<div class="buy_for article_details_bottom">
		{if $sDisplayRadios && $sBonusSystem.settings.bonus_articles_active}
			<div class="item">
				<input type="radio" name="buy_for" id="money" class="radio" value="euro" checked="checked" />
				<label class="description" for="money">{s namespace="frontend/bonus_system" name="BuyForEuro"}F&uuml;r je <strong><span class="price-holder"></span></strong> in den Warenkorb{/s}</label>
				<div class="clear"></div>
			</div>
			<div class="item">
				<input type="radio" name="buy_for" id="points" class="radio" value="points"/>
				<label class="description" for="points">{s namespace="frontend/bonus_system" name="BuyForPoints"}F&uuml;r je <strong style="color: #000;">{$sArticle.required_points} Bonuspunkte</strong> in den Warenkorb{/s}</label>
				<div class="clear"></div>
			</div>
		{/if}
		<input type="hidden" id="user_points" value="{$sBonusSystem.points.remaining}" />
		<input type="hidden" name="points_per_unit" id="points_per_unit" value="{$sArticle.required_points}" />
		<input type="hidden" name="earning_points_per_unit" id="earning_points_per_unit" value="{$sArticle.earning_points_per_unit}" />
	</div>
{/block}

{* display how much points the user would earn for this article *}
{block name='frontend_detail_buy' append}
	{if $sBonusSystem.settings.bonus_articles_active}
		<div class="points_for_article">
			<div class="before">{s namespace="frontend/bonus_system" name="DetailPointsForArticleBefore"}Jetzt{/s}</div>
			<div class="image">{$sArticle.earning_points_current}</div>
			<div class="after">{s namespace="frontend/bonus_system" name="DetailPointsForArticleAfter"}<strong><a href="{url controller='BonusSystem'}">Bonuspunkte</a></strong> sichern{/s}</div>
			<div class="clear"></div>
		</div>
	{/if}
{/block}
