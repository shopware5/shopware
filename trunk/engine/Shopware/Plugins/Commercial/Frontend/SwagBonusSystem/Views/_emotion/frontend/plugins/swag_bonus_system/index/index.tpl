{* display the earning points for the current basket *}
{block name="frontend_index_checkout_actions" prepend}
	<div class="basket_points">
		{s namespace="frontend/bonus_system" name="HeaderEarningBonusPoints" force}Bonuspunkte: <strong style="float:right;"><span class="bonus-system-display">{$sBonusSystem.points.earning}</span></strong>{/s}
	</div>
	<div class="clear">&nbsp;</div>

{/block}

{block name="frontend_index_checkout_actions_account" append}
{if $sUserLoggedIn}
<div class="user_points">
		<div class="user_points_inner">
			{block name="frontend_index_header_user_points"}
				<p class="current_points"><strong>{$sBonusSystem.points.remaining}</strong>
			{/block}
			<ul class="link_container">
				<span class="arrow"></span>

				<li class="first">
					{s name="HeaderRemainingBonusPoints" force}<span class="bonus_points">Sie besitzen {$sBonusSystem.points.remaining} Bonuspunkte</span>{/s}
				</li>
				<li>
					<a href="{url controller='BonusSystem' action='points'}" title="{s namespace="frontend/bonus_system" name='DisplayUserPointAccount'}Zu Ihrem Punktekonto{/s}">
						{s namespace="frontend/bonus_system" name='DisplayUserPointAccount'}Zu Ihrem Punktekonto{/s}
					</a>
				</li>
				{if $sBonusSystem.settings.bonus_articles_active}
					<li class="last">
						<a href="{url controller='BonusSystem'}" title="{s namespace="frontend/bonus_system" name='DisplayAllBonusArticlesTitle'}Zu den Bonusartikeln{/s}">
							{s namespace="frontend/bonus_system" name='DisplayAllBonusArticlesTitle'}Zu den Bonusartikeln{/s}
						</a>
					</li>
				{/if}
			</ul>
		</div>
	</div>
{/if}
{/block}
