{namespace name="frontend/account/sidebar"}

<h2 class="headingbox largesize">{se name="AccountHeaderNavigation"}{/se}</h2>
<div class="adminbox">
	{block name="frontend_account_content_right_start"}
	{/block}
	<ul>
	{block name="frontend_account_content_right_overview"}
		{* Overview *}
		<li>
			<a href="{url controller='account'}">
				{se name="AccountLinkOverview"}{/se}
			</a>
		</li>
	{/block}
	{block name="frontend_account_content_right_orders"}
		{* My orders *}
		<li>
			<a href="{url controller='account' action='orders'}">
				{se name="AccountLinkPreviousOrders"}{/se}
			</a>
		</li>
	{/block}
	{block name="frontend_account_content_right_downloads"}
		{* My esd articles *}
		{if {config name=showEsd}}
			<li>
				<a href="{url controller='account' action='downloads'}">
					{se name="AccountLinkDownloads"}{/se}
				</a>
			</li>
		{/if}
	{/block}
	{block name="frontend_account_content_right_billing"}
		{* Change billing address *}
		<li>
			<a href="{url controller='account' action='billing'}">
				{se name="AccountLinkBillingAddress"}{/se}
			</a>
		</li>
	{/block}
	{block name="frontend_account_content_right_shipping"}
		{* Change shipping address *}
		<li>
			<a href="{url controller='account' action='shipping'}">
				{se name="AccountLinkShippingAddress"}{/se}
			</a>
		</li>
	{/block}
	{block name="frontend_account_content_right_payment"}
		{* Change payment method *}
		<li>
			<a href="{url controller='account' action='payment'}">
				{se name="AccountLinkPayment"}{/se}
			</a>
		</li>		
	{/block}
	{block name="frontend_account_content_right_support"}
		{* Supportmanagement *}
		{if $sTicketLicensed}
			<li>
				<a href="{url controller='ticket' action='listing'}">
					{se name="sTicketSysSupportManagement"}{/se}
				</a>
			</li>
			
			<li class="sub"><a href="{url controller='ticket' action='request'}">{s name='TicketLinkSupport'}{/s}</a></li>		
		{/if}
	{/block}
	{block name="frontend_account_content_right_notes"}
		{* Leaflet *}
		<li>
			<a href="{url controller='note'}">
				{se name="AccountLinkNotepad"}{/se}
			</a>
		</li>
	{/block}
    {block name="frontend_account_content_right_partner_statistic"}
        {action name="partnerStatisticMenuItem" controller="account"}
    {/block}
	{block name="frontend_account_content_right_logout"}
		{* Logout *}
		<li class="last">
			<a href="{url controller='account' action='logout'}" class="logout">
				{se name="AccountLinkLogout"}{/se}
			</a>
		</li>
	{/block}
	</ul>	
	{block name="frontend_account_content_right_end"}
	{/block}
</div>
