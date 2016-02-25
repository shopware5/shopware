{extends file='frontend/index/index.tpl'}

{block name='frontend_index_header'}
	{include file='frontend/listing/header.tpl'}
{/block}

{* Main content *}
{block name='frontend_index_content'}
<div id="center" class="grid_13">
	{* Banner *}
	
	{block name="frontend_listing_index_banner"}
		{if !$sLiveShopping}
			{include file='frontend/listing/banner.tpl' sLiveShopping=$sLiveShopping}
		{/if}
	{/block}
	
	{* Liveshopping *}
	{block name="frontend_listing_index_liveshopping"}
		{include file='frontend/listing/liveshopping.tpl'}
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
	
	{* Change / Logic move to controller *}
	
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
	        <div class="emotion-link">
	            <a class="emotion-offers" href="{url controller='cat' sPage=1 sCategory=$sCategoryContent.id}" rel="nofollow">
	                {s name="ListingActionsOffersLink"}Weitere Artikel in dieser Kategorie{/s}
	            </a>
	        </div>
	        <div class="space">&nbsp;</div>
	    {/if}
	{/block}

	
	{* Tagcloud *}
	{block name="frontend_listing_index_tagcloud"}
		{if {config name=show namespace=TagCloud }}
		    {action module=widgets controller=listing action=tag_cloud sController=listing sCategory=$sCategoryContent.id}
		{/if}
	{/block}
</div>
{/block}

{block name="frontend_index_left_categories" append}
	{include file="frontend/listing/right.tpl"}
	<div class="clear">&nbsp;</div>
{/block}

{* Sidebar right *}
{block name='frontend_index_content_right'}{/block}