{extends file='frontend/index/index.tpl'}

{block name="frontend_index_content_top"}{/block}

{* Page title *}
{block name='frontend_index_header_title'}{strip}
	{if $sCategoryContent.metaTitle}
		{$sCategoryContent.metaTitle|escapeHtml} | {{config name=sShopname}|escapeHtml}
	{else}
		{$smarty.block.parent}
	{/if}
{/strip}{/block}

{* Breadcrumb *}
{block name='frontend_index_breadcrumb'}
	<div class="clear"></div>
{/block}

{* Hide sidebar left *}
{block name='frontend_index_content_left'}{/block}

{* Main content *}
{block name='frontend_index_content'}
<div id="center" class="grid_13 home">

	{block name='frontend_home_index_banner'}
		{* Banner *}
		{include file='frontend/listing/banner.tpl'}
	{/block}

	{block name='frontend_home_index_liveshopping'}
		{* Liveshopping *}
		{include file='frontend/listing/liveshopping.tpl'}
	{/block}

	{block name='frontend_home_index_text'}
		{* Category headline *}
		{if !$hasEmotion}
			{include file='frontend/listing/text.tpl'}
		{/if}
	{/block}

	{* Promotion *}
	{block name='frontend_home_index_promotions'}
		{action module=widgets controller=emotion action=index categoryId=$sCategoryContent.id controllerName=$Controller}
	{/block}

	{block name='frontend_home_index_blog'}
		{* Blog Articles *}
		{if $sBlog.sArticles|@count}
		<div class="listing_box">
			<h2 class="headingbox_nobg largesize">{se name='WidgetsBlogHeadline'}{/se}:</h2>
			{foreach from=$sBlog.sArticles item=article key=key name="counter"}
				{include file="frontend/blog/box.tpl" sArticle=$article key=$key homepage=true}
			{/foreach}
		</div>
		{/if}
	{/block}


	<div class="doublespace">&nbsp;</div>

	{* Tagcloud *}
	{block name='frontend_home_index_tagcloud'}
		{if {config name=show namespace=TagCloud } && !$isEmotionLandingPage}
			{action module=widgets controller=listing action=tag_cloud sController=index}
		{/if}
	{/block}
</div>
{/block}

{* Hide sidebar right *}
{block name='frontend_index_content_right'}{/block}
