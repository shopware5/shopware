<aside class="sidebar-main off-canvas block">
	{* Campaign left top *}
	{block name='frontend_index_left_campaigns_top'}
		{include file="frontend/campaign/box.tpl" campaignsData=$campaigns.leftTop}
	{/block}
	
	{* Sidebar category tree *}
	{block name='frontend_index_left_categories'}
        <div class="navigation--smartphone">
            <ul class="navigation--list ">

				{* Trigger to close the off canvas menu *}
				{block name="frontend_index_left_categories_close_menu"}
					<li class="navigation--entry entry--close-off-canvas">
						<a href="#close-categories-menu" class="navigation--link">
							{s name="IndexActionCloseMenu"}Menü schließen{/s} <i class="icon--arrow-right"></i>
						</a>
					</li>
				{/block}

				{* My account link in the sidebar *}
				{block name="frontend_index_left_categories_my_account"}
					<li class="navigation--entry entry--my-account">
						<a class="navigation--link" href="{url controller='account'}" title="{s namespace='frontend/index/checkout_actions' name='IndexLinkAccount'}{/s}">
							<i class="icon--account"></i> {s namespace='frontend/index/checkout_actions' name='IndexLinkAccount'}{/s}
						</a>
					</li>
				{/block}
            </ul>

			{* Headline which should only be visible on mobile *}
			{block name="frontend_index_left_categories_headline"}
				<h2 class="navigation--headline">{s name="IndexSidebarCategoryHeadline"}Kategorien{/s}</h2>
			{/block}
        </div>

		{* Actual include of the categories *}
		{block name='frontend_index_left_categories_inner'}
			{include file='frontend/index/sidebar-categories.tpl'}
		{/block}
	{/block}			
	
	{* Campaign left middle *}
	{block name='frontend_index_left_campaigns_middle'}
		{include file="frontend/campaign/box.tpl" campaignsData=$campaigns.leftMiddle}
	{/block}

	{* Trusted shops logo *}
	{block name='frontend_index_left_trustedshops'}
		{if {config name=TSID}}
			{include file='frontend/plugins/trusted_shops/logo.tpl'}
		{/if}
	{/block}

	{* Static sites *}
	{block name='frontend_index_left_menu'}
		{include file='frontend/index/sites-navigation.tpl'}
	{/block}

	{* Campaign left bottom *}
	{block name='frontend_index_left_campaigns_bottom'}
		{include file="frontend/campaign/box.tpl" campaignsData=$campaigns.leftBottom}
	{/block}
</aside>