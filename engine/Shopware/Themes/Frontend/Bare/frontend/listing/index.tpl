{extends file='frontend/index/index.tpl'}

{block name='frontend_index_header'}
	{include file='frontend/listing/header.tpl'}
{/block}

{* Main content *}
{block name='frontend_index_content'}
<div class="content block listing--content">

	{* Banner *}
	{block name="frontend_listing_index_banner"}
		{include file='frontend/listing/banner.tpl'}
	{/block}
	
	{* Category headline *}
	{block name="frontend_listing_index_text"}
		{if !$hasEmotion && !$sSupplierInfo}
			{include file='frontend/listing/text.tpl'}
		{/if}

		{* Topseller *}
		{if !$hasEmotion && !$sSupplierInfo && {config name=topSellerActive}}
			{action module=widgets controller=listing action=top_seller sCategory=$sCategoryContent.id}
		{/if}
	{/block}
	
	{* Remap the template names to the new syntax *}
	{if $sCategoryContent.template eq "article_listing_1col.tpl"}
		{assign var="sTemplate" value="listing-1col"}
		{assign var="sBoxMode" value="list"}
	{elseif $sCategoryContent.template eq "article_listing_2col.tpl"}
		{assign var="sTemplate" value="listing-2col"}
		{assign var="sBoxMode" value="table"}
	{elseif $sCategoryContent.template eq "article_listing_3col.tpl"}
		{assign var="sTemplate" value="listing-3col"}
		{assign var="sBoxMode" value="table"}
	{elseif $sCategoryContent.template eq "article_listing_4col.tpl"}
		{assign var="sTemplate" value="listing"}
		{assign var="sBoxMode" value="table"}
	{else}
		{assign var="sTemplate" value="listing-3col"}
		{assign var="sBoxMode" value="table"}
	{/if}

	{* Listing *}
	{block name="frontend_listing_index_listing"}
		{include file='frontend/listing/listing.tpl' sTemplate=$sTemplate}

	    {if $sCategoryContent.parent != 1 && ! $showListing && !$sSupplierInfo}

            {* Further products in the category *}
            {block name="frontend_listing_index_listing_further_products"}
                <div class="further-products">
                    <a class="further-products--link" href="{url controller='cat' sPage=1 sCategory=$sCategoryContent.id}">
                        {s name="ListingActionsOffersLink"}Weitere Artikel in dieser Kategorie{/s}
                    </a>
                </div>
            {/block}
	    {/if}
	{/block}
	
	{* Tagcloud *}
	{block name="frontend_listing_index_tagcloud"}
		{if {config name=show namespace=TagCloud }}
		    {action module=widgets controller=listing action=tag_cloud sCategory=$sCategoryContent.id}
		{/if}
	{/block}
</div>
{/block}

{* Trusted shops logo *}
{block name='frontend_index_left_trustedshops'}
    {block name="frontend_listing_left_additional_features"}
        {include file="frontend/listing/sidebar.tpl"}
    {/block}

    {* Trusted shops logo in the sidebar *}
    {block name='frontend_listing_index_sidebar_trusted_shops'}
        {if {config name=TSID}}
            {include file='frontend/plugins/trusted_shops/logo.tpl'}
        {/if}
    {/block}
{/block}

{* Sidebar right *}
{block name='frontend_index_content_right'}{/block}