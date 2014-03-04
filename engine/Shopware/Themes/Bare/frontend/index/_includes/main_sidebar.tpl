<aside class="sidebar-main block">
	{* Campaign left top *}
	{block name='frontend_index_left_campaigns_top'}
		{include file="frontend/campaign/box.tpl" campaignsData=$campaigns.leftTop}
	{/block}
	
	{* Maincategories left *}
	{block name='frontend_index_left_categories'}
        <div class="navigation--smartphone">
            <ul class="navigation--list ">
                <li class="navigation--entry entry--close-off-canvas">
                    <a href="#close-categories-menu" class="navigation--link">
                        Menü schließen <i class="icon--arrow-right"></i>
                    </a>
                </li>

                <li class="navigation--entry entry--my-account">
                    <a class="navigation--link" href="{url controller='account'}" title="{s namespace='frontend/index/checkout_actions' name='IndexLinkAccount'}{/s}">
                        {s namespace='frontend/index/checkout_actions' name='IndexLinkAccount'}{/s}
                    </a>
                </li>
            </ul>

            <h2 class="headline">Kategorien</h2>
        </div>

		{include file='frontend/index/_includes/categories_left.tpl'}
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
		{include file='frontend/index/_includes/menu_left.tpl'}
	{/block}

	{* Campaign left bottom *}
	{block name='frontend_index_left_campaigns_bottom'}
		{if $campaigns.leftBottom}
			<div class="space"></div>
		{/if}
		{include file="frontend/campaign/box.tpl" campaignsData=$campaigns.leftBottom}
	{/block}
	
	{* Last articles *}
	{block name='frontend_index_left_last_articles'}{/block}
	
	<div class="clear">&nbsp;</div>
</aside>