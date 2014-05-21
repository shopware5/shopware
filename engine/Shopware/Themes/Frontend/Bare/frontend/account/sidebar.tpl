<div class="account--menu">
	{block name="frontend_account_menu"}

		{* Sidebar navigation headline *}
		{block name="frontend_account_menu_title"}
			<h2 class="navigation--headline">
				{s name="AccountHeaderNavigation"}{/s}
			</h2>
		{/block}

		{* Sidebar menu container *}
		<div class="account--menu-container">

			{* Sidebar navigation *}
			<ul class="sidebar--navigation navigation--list is--level0">

				{* Link to the account overview page *}
				{block name="frontend_account_menu_link_overview"}
					<li class="navigation--entry">
						<a href="{url controller='account'}" class="navigation--link">
							{s name="AccountLinkOverview"}{/s}
						</a>
					</li>
				{/block}

				{* Link to the user orders *}
				{block name="frontend_account_menu_link_orders"}
					<li class="navigation--entry">
						<a href="{url controller='account' action='orders'}" class="navigation--link">
							{s name="AccountLinkPreviousOrders"}{/s}
						</a>
					</li>
				{/block}

				{* Link to the user downloads *}
				{block name="frontend_account_menu_link_downloads"}
					<li class="navigation--entry">
						<a href="{url controller='account' action='downloads'}" class="navigation--link">
							{s name="AccountLinkDownloads"}{/s}
						</a>
					</li>
				{/block}

				{* Link to the user billing address settings *}
				{block name="frontend_account_menu_link_billing"}
					<li class="navigation--entry">
						<a href="{url controller='account' action='billing'}" class="navigation--link">
							{s name="AccountLinkBillingAddress"}{/s}
						</a>
					</li>
				{/block}

				{* Linkt to the user shipping address settings *}
				{block name="frontend_account_menu_link_shipping"}
					<li class="navigation--entry">
						<a href="{url controller='account' action='shipping'}" class="navigation--link">
							{s name="AccountLinkShippingAddress"}{/s}
						</a>
					</li>
				{/block}

				{* Link to the user payment method settings *}
				{block name="frontend_account_menu_link_payment"}
					<li class="navigation--entry">
						<a href="{url controller='account' action='payment'}" class="navigation--link">
							{s name="AccountLinkPayment"}{/s}
						</a>
					</li>
				{/block}

				{* Link to the ticket system *}
				{block name="frontend_account_menu_link_support"}
					{if $sTicketLicensed}
						<li class="navigation--entry">
							<a href="{url controller='ticket' action='listing'}" class="navigation--link">
								{s name="sTicketSysSupportManagement"}{/s}
							</a>
						</li>
					{/if}
				{/block}

				{* Link to the user product notes *}
				{block name="frontend_account_menu_link_notes"}
					<li class="navigation--entry">
						<a href="{url controller='note'}" class="navigation--link">
							{s name="AccountLinkNotepad"}{/s}
						</a>
					</li>
				{/block}

				{* Link to the partner statistics *}
				{block name="frontend_account_menu_link_partner_statistics"}
					{action name="partnerStatisticMenuItem" controller="account"}
				{/block}

				{* Logout action *}
				{block name="frontend_account_menu_link_logout"}
					<li class="navigation--entry">
						<a href="{url controller='account' action='logout'}" class="navigation--link link--logout">
							<i class="icon--compare"></i> {s name="AccountLinkLogout"}{/s}
						</a>
					</li>
				{/block}

			</ul>
		</div>
	{/block}
</div>