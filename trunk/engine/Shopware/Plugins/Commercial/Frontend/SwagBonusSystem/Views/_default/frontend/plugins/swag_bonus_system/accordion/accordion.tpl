{* this is the accordion for the right side with bonus articles and a link to the listing *}
{if $sBonusSystem.settings.bonus_articles_active}
	{if $sBonusSystem.accordion|@count}
		<h2 class="headingbox_nobg">{s namespace="frontend/bonus_system" name="BonusArticles"}Bonusartikel{/s}</h2>
		<div class="topseller bonus_articles">
			<ul class="accordion">

			{foreach from=$sBonusSystem.accordion item=sArticle}
				<li {if $sArticle@first}class="active"{/if}>
					<ul class="image">
						<li>
							<a href="{$sArticle.linkDetails|rewrite:$sArticle.articleName}" title="{$sArticle.articleName}">
								{if $sArticle.image.src}
									<img src="{$sArticle.image.src.2}" alt="{$sArticle.articleName}" title="{$sArticle.articleName}" border="0"/>
								{else}
									<img src="{link file='frontend/_resources/images/no_picture.jpg'}" alt="{s namespace="frontend/bonus_system" name='WidgetsTopsellerNoPicture'}{/s}" />
								{/if}
							</a>
						</li>
					</ul>

					<div class="detail">
						<a href="{$sArticle.linkDetails}" title="{$sArticle.articleName}">
							{$sArticle.articleName|truncate:20:"...":true}
						</a>
						<div class="points">
							{s namespace="frontend/bonus_system/accordion" name="ForXBonusPoints"}f&uuml;r {$sArticle.required_points} Punkte{/s}
						</div>
					</div>

				</li>
			{/foreach}
			</ul>
			<a class="bonus_listing_link" href="{url controller='BonusSystem'}" title="{s namespace="frontend/bonus_system" name='DisplayAllBonusArticlesTitle'}Zu den Bonusartikeln{/s}">
				{s namespace="frontend/bonus_system" namespace="frontend/bonus_system/accordion" name="DisplayAllBonusArticleLink"}Alle anzeigen{/s}
			</a>
		</div>
	{/if}
{/if}