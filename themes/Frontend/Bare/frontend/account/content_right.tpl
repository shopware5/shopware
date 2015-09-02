<h2 class="headingbox largesize">{s name="AccountHeaderNavigation"}{/s}</h2>
<div class="adminbox">
	{block name="frontend_account_content_right_start"}
	{/block}
	<ul>
	{block name="frontend_account_content_right_overview"}
		{* Overview *}
		<li>
			<a href="{url controller='account'}">
				{s name="AccountLinkOverview"}{/s}
			</a>
		</li>
	{/block}
	{block name="frontend_account_content_right_orders"}
		{* My orders *}
		<li>
			<a href="{url controller='account' action='orders'}">
				{s name="AccountLinkPreviousOrders"}{/s}
			</a>
		</li>
	{/block}
	{block name="frontend_account_content_right_downloads"}
		{* My esd articles *}
		<li>
			<a href="{url controller='account' action='downloads'}">
				{s name="AccountLinkDownloads"}{/s}
			</a>
		</li>
	{/block}
	{block name="frontend_account_content_right_billing"}
		{* Change billing address *}
		<li>
			<a href="{url controller='account' action='billing'}">
				{s name="AccountLinkBillingAddress"}{/s}
			</a>
		</li>
	{/block}
	{block name="frontend_account_content_right_shipping"}
		{* Change shipping address *}
		<li>
			<a href="{url controller='account' action='shipping'}">
				{s name="AccountLinkShippingAddress"}{/s}
			</a>
		</li>
	{/block}
	{block name="frontend_account_content_right_payment"}
		{* Change payment method *}
		<li>
			<a href="{url controller='account' action='payment'}">
				{s name="AccountLinkPayment"}{/s}
			</a>
		</li>
	{/block}
	{block name="frontend_account_content_right_support"}
		{* Supportmanagement *}
		{if $sTicketLicensed}
			<li>
				<a href="{url controller='ticket' action='listing'}">
					{s name="sTicketSysSupportManagement"}{/s}
				</a>
			</li>

			<li class="sub"><a href="{url controller='ticket' action='request'}">{s name='TicketLinkSupport'}{/s}</a></li>
		{/if}
	{/block}
	{block name="frontend_account_content_right_notes"}
		{* Leaflet *}
		<li>
			<a href="{url controller='note'}">
				{s name="AccountLinkNotepad"}{/s}
			</a>
		</li>
	{/block}
    {block name="frontend_account_content_right_partner_statistic"}
        {action action="partnerStatisticMenuItem" controller="account"}
    {/block}
	{block name="frontend_account_content_right_logout"}
		{* Logout *}
		<li class="last">
			<a href="{url controller='account' action='logout'}" class="logout">
				{s name="AccountLinkLogout"}{/s}
			</a>
		</li>
	{/block}
	</ul>
	{block name="frontend_account_content_right_end"}
	{/block}
</div>
