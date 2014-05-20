<div class="account--menu">
	{block name="frontend_account_menu"}

		{block name="frontend_account_menu_title"}
			<h2 class="navigation--headline">
				{s name="AccountHeaderNavigation"}{/s}
			</h2>
		{/block}

		<div class="account--menu-container">
			<ul class="sidebar--navigation navigation--list is--level0">

				{block name="frontend_account_menu_link_overview"}
					<li class="navigation--entry">
						<a href="{url controller='account'}" class="navigation--link">
							{s name="AccountLinkOverview"}{/s}
						</a>
					</li>
				{/block}

				{block name="frontend_account_menu_link_orders"}
					<li class="navigation--entry">
						<a href="{url controller='account' action='orders'}" class="navigation--link">
							{s name="AccountLinkPreviousOrders"}{/s}
						</a>
					</li>
				{/block}

				{block name="frontend_account_menu_link_downloads"}
					<li class="navigation--entry">
						<a href="{url controller='account' action='downloads'}" class="navigation--link">
							{s name="AccountLinkDownloads"}{/s}
						</a>
					</li>
				{/block}

				{block name="frontend_account_menu_link_billing"}
					<li class="navigation--entry">
						<a href="{url controller='account' action='billing'}" class="navigation--link">
							{s name="AccountLinkBillingAddress"}{/s}
						</a>
					</li>
				{/block}

				{block name="frontend_account_menu_link_shipping"}
					<li class="navigation--entry">
						<a href="{url controller='account' action='shipping'}" class="navigation--link">
							{s name="AccountLinkShippingAddress"}{/s}
						</a>
					</li>
				{/block}

				{block name="frontend_account_menu_link_payment"}
					<li class="navigation--entry">
						<a href="{url controller='account' action='payment'}" class="navigation--link">
							{s name="AccountLinkPayment"}{/s}
						</a>
					</li>
				{/block}

				{block name="frontend_account_menu_link_support"}
					{if $sTicketLicensed}
						<li class="navigation--entry">
							<a href="{url controller='ticket' action='listing'}" class="navigation--link">
								{s name="sTicketSysSupportManagement"}{/s}
							</a>
						</li>
					{/if}
				{/block}

				{block name="frontend_account_menu_link_notes"}
					<li class="navigation--entry">
						<a href="{url controller='note'}" class="navigation--link">
							{s name="AccountLinkNotepad"}{/s}
						</a>
					</li>
				{/block}

				{block name="frontend_account_menu_link_partner_statistics"}
					{action name="partnerStatisticMenuItem" controller="account"}
				{/block}

				{block name="frontend_account_menu_link_logout"}
					<li class="navigation--entry">
						<a href="{url controller='account' action='logout'}" class="navigation--link logout">
							<i class="icon--compare"></i> {s name="AccountLinkLogout"}{/s}
						</a>
					</li>
				{/block}

			</ul>
		</div>
	{/block}
</div>