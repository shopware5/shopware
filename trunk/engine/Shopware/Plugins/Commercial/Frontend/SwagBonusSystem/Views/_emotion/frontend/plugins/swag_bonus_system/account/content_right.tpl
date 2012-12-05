{* extend the right navigation in the account *}
{block name="frontend_account_content_right_end" append}
{* Close the "My Account" ul *}
</div>
{if $sBonusSystem.settings.bonus_system_active==1}
	<div class="space"></div>
	{* /Close the "My Account" ul *}

	<h2 class="headingbox largesize">{s namespace="frontend/bonus_system/account" name="BonusPoints"}Bonuspunkte{/s}</h2>
	<div class="adminbox bonus">
		<p class="bonus-point-balance"><span class="snippet">{s namespace="frontend/bonus_system" name="AccountBonusPointsBalance"}Aktuell:{/s}</span> <span class="value">{if $sBonusSystem.points.remaining}{$sBonusSystem.points.remaining}{else}0{/if}</span></p>
		<ul>
			{* My bonus point account *}
			<li style="border-top:1px solid #E3E3E3; ">
				<a href="{url controller='BonusSystem' action='points'}">
					{s namespace="frontend/bonus_system" name="AccountLinkMyBonusPointAccount"}Mein Punktekonto{/s}
				</a>
			</li>
		</ul>
{/if}
{/block}