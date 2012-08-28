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
		{include file='frontend/listing/text.tpl'}
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
	{/block}

	
	{* Tagcloud *}
	{block name="frontend_listing_index_tagcloud"}
		{include file='frontend/plugins/index/tagcloud.tpl'}
	{/block}
</div>
{/block}

{* Sidebar right *}
{block name='frontend_index_content_right'}
	{include file='frontend/listing/right.tpl'}
{/block}