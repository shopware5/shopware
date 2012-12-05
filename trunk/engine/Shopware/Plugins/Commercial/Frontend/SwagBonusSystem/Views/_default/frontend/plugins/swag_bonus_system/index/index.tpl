{* display the user point score *}
{block name="frontend_index_checkout_actions_notepad" append}
	<div class="clear">&nbsp;</div>
	{if $sUserLoggedIn}
		<div class="user_points">
			{block name="frontend_index_header_user_points"}
				<p class="current_points"> {s namespace="frontend/bonus_system" name="HeaderYourBonusPoints"}Sie besitzen<strong> {$sBonusSystem.points.remaining} Bonuspunkte </strong>{/s}
			{/block}
			<ul class="link_container">
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
	{/if}
{/block}

{* display the earning points for the current basket *}
{block name="frontend_index_checkout_actions_inner" prepend}
	<div class="basket_points">
		{s namespace="frontend/bonus_system" name="HeaderEarningBonusPoints" force}Aktuell: <strong><span class="bonus-system-display">{$sBonusSystem.points.earning}</span> Bonuspunkte</strong>{/s}
	</div>
{/block}
