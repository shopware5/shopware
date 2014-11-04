<aside class="sidebar-main off-canvas">

	{* Campaign left top *}
	{block name='frontend_index_left_campaigns_top'}
		{include file="frontend/campaign/box.tpl" campaignsData=$campaigns.leftTop}
	{/block}

	{* Mobile specific menu actions *}
	{block name="frontend_index_left_navigation_smartphone"}
		<div class="navigation--smartphone">
			<ul class="navigation--list ">

				{* Trigger to close the off canvas menu *}
				{block name="frontend_index_left_categories_close_menu"}
					<li class="navigation--entry entry--close-off-canvas">
						<a href="#close-categories-menu" class="navigation--link">
							{s namespace='frontend/index/menu_left' name="IndexActionCloseMenu"}Menü schließen{/s} <i class="icon--arrow-right"></i>
						</a>
					</li>
				{/block}

				{* My account link in the sidebar *}
				{block name="frontend_index_left_categories_my_account"}
					<li class="navigation--entry entry--my-account">
						<a class="navigation--link" href="{url controller='account'}" title="{"{s namespace='frontend/index/checkout_actions' name='IndexLinkAccount'}{/s}"|escape}">
							<i class="icon--account"></i> {s namespace='frontend/index/checkout_actions' name='IndexLinkAccount'}{/s}
						</a>
					</li>
				{/block}

				{* Switches for currency and language on mobile devices *}
				{block name="frontend_index_left_switches"}
					<div class="mobile--switches">
						{action module=widgets controller=index action=shopMenu}
					</div>
				{/block}
			</ul>
		</div>
	{/block}

    <div class="sidebar--categories-wrapper" data-categories-slider="true" data-mainCategoryId="{$Shop->get('parentID')}" data-categoryId="{$sCategoryContent.id}" data-fetchUrl="{url module=widgets controller=listing action=getCategory categoryId={$sCategoryContent.id}}">

        {* Sidebar category tree *}
        {block name='frontend_index_left_categories'}

            {* Categories headline *}
            {block name="frontend_index_left_categories_headline"}
                <h2 class="categories--headline navigation--headline">{s name="IndexSidebarCategoryHeadline"}Kategorien{/s}</h2>
            {/block}

            {* Actual include of the categories *}
            {block name='frontend_index_left_categories_inner'}
                <div data-categories-dropdown="true">
                    {include file='frontend/index/sidebar-categories.tpl'}
                </div>
            {/block}
        {/block}

        {* Campaign left middle *}
        {block name='frontend_index_left_campaigns_middle'}
            {include file="frontend/campaign/box.tpl" campaignsData=$campaigns.leftMiddle}
        {/block}

        {* Static sites *}
        {block name='frontend_index_left_menu'}
            {include file='frontend/index/sites-navigation.tpl'}
        {/block}

        {* Campaign left bottom *}
        {block name='frontend_index_left_campaigns_bottom'}
            {include file="frontend/campaign/box.tpl" campaignsData=$campaigns.leftBottom}
        {/block}

    </div>
</aside>