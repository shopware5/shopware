{* Promotion *}
{include file='frontend/listing/promotions.tpl' sTemplate=$sTemplate}

{* Sorting and changing layout *}
{block name="frontend_listing_top_actions"}
{if !$sOffers}
	{include file='frontend/listing/listing_actions.tpl' sTemplate=$sTemplate}
{/if}
{/block}

{* Supplier filter *}
{block name="frontend_listing_list_filter_supplier"}
{if $sSupplierInfo} 
	<div id="supplierfilter" {if $sSupplierInfo.image}class="supplierfilter_image"{/if}>
		{if $sSupplierInfo.image}
			<img src="{$sSupplierInfo.image}" alt="{$sSupplierInfo.name}" name="{$sSupplierInfo.name}" border="0" title="{$sSupplierInfo.name}" />
		{else}
			<div class="text">
				{se name='ListingInfoFilterSupplier'}{/se} <strong>{$sSupplierInfo.name}</strong>
			</div>
		{/if}
		<div class="right">
			<a href="{$sSupplierInfo.link}" title="{s name='ListingLinkAllSuppliers'}{/s}" class="bt_allsupplier">
				{se name='ListingLinkAllSuppliers'}{/se}
			</a>
		</div>
		<div class="clear">&nbsp;</div>
	</div>
	<div class="space">&nbsp;</div>
{/if}
{/block}

{* Hide actual listing if a promotion is active *}
{if !$sOffers} 
<div class="listing" id="{$sTemplate}">
{block name="frontend_listing_list_inline"}
		{* Actual listing *}
		{foreach $sArticles as $sArticle}
			{include file="frontend/listing/box_article.tpl" sTemplate=$sTemplate lastitem=$sArticle@last firstitem=$sArticle@first}
		{/foreach}
{/block}
</div>
{/if}

{* Paging *}
{block name="frontend_listing_bottom_paging"}
	{include file='frontend/listing/listing_actions.tpl' sTemplate=$sTemplate}
{/block}