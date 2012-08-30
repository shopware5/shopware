{extends file='parent:frontend/index/index.tpl'}

{* Shop header *}
{block name='frontend_index_navigation'}{/block}

{block name="frontend_index_no_script_message"}
	<div id="header">
		<div class="inner">


			{* Trusted Shops *}
			{if {config name=TSID}}
			<div class="trusted_shops_top">
				<a href="https://www.trustedshops.com/shop/certificate.php?shop_id={config name=TSID}" title="{s name='WidgetsTrustedLogo' namespace='frontend/plugins/trusted_shops/logo'}{/s}" target="_blank">
				    <img src="{link file='frontend/_resources/images/logo_trusted_shop_top.png'}" alt="{s name='WidgetsTrustedLogo' namespace='frontend/plugins/trusted_shops/logo'}{/s}" />
				    <p>{s name='WidgetsTrustedLogoText2'}<span><strong>Sicher</strong> einkaufen</span><br/>Trusted Shops zertifiziert{/s}</p>	
				</a>
			</div>
			{/if}
			
			{* Search *}
			{include file="frontend/index/search.tpl"}
		
			{* Language and Currency bar *}
			{block name='frontend_index_actions'}{/block}
			
			{* Shop logo *}
			{block name='frontend_index_logo'}
			<div id="logo" class="grid_5">
				<a href="{url controller='index'}" title="{$sShopname} - {s name='IndexLinkDefault'}{/s}">{$sShopname}</a>
			</div>
			{/block}
		
			{* Shop navigation *}
			{block name='frontend_index_checkout_actions'}
				{action module=widgets controller=checkout action=info}
			{/block}
			
			{block name='frontend_index_navigation_inline'}{/block}
			
		</div>
		
		
	</div>
	<div id="wrapper">
	<div class="wrap_top"></div>
		<div class="wrap_inner">
		
		{* Maincategories navigation top *}
		{block name='frontend_index_navigation_categories_top_neu'}
			{include file='frontend/index/categories_top.tpl'}
		{/block}
{/block}

{block name="frontend_index_shopware_footer"}
		</div>
	<div class="wrap_cap"></div>
	</div>

	{* FOOTER *}
	<div id="footer_wrapper">
		<div class="footer_inner">
			<div class="clear"></div>
			{include file='frontend/index/footer.tpl'}
		</div>
		
		<div class="shopware_footer">
			{s name="IndexRealizedWith"}Realisiert mit{/s} <a href="http://www.shopware.de" target="_blank" title="{s name='IndexRealizedShopsystem'}Shopware{/s}">{s name="IndexRealizedShopsystem"}Shopware{/s}</a>
			<div class="clear"></div>
		</div>
		
		<div class="clear"></div>

	</div>

{/block}


{* Maincategories navigation top *}
{block name='frontend_index_navigation_categories_top'}{/block}
	
{* Compare container *}
{block name='frontend_index_navigation_inline'}
{if $sCompareShow}
<div id="compareContainerAjax">
    {action module=widgets controller=compare}
</div>
{/if}
{/block}

{* Search *}
{block name='frontend_index_search'}{/block}

{* Footer *}
{block name="frontend_index_footer"}
    {if $sLastArticlesShow}
        {action module=widgets controller=lastArticles}
    {/if}
{/block}