{extends file="parent:frontend/listing/index.tpl"}

{namespace name="frontend/listing/listing"}

{block name="frontend_listing_listing_content"}
	<div class="listing"
		 data-ajax-wishlist="true"
		 data-compare-ajax="true"
			{if $theme.infiniteScrolling}
			data-infinite-scrolling="true"
			data-ajaxUrl="{url module="widgets" controller="Listing" action="ajaxListing" sSupplier=$manufacturer->getId()}"
			data-loadPreviousSnippet="{s name="ListingActionsLoadPrevious"}{/s}"
			data-loadMoreSnippet="{s name="ListingActionsLoadMore"}{/s}"
			data-categoryId="{$Shop->getCategory()->getId()}"
			data-pages="{$pages}"
			data-threshold="{$theme.infiniteThreshold}"{/if}>

		{* Actual listing *}
		{block name="frontend_listing_list_inline"}
			{foreach $sArticles as $sArticle}
				{include file="frontend/listing/box_article.tpl"}
			{/foreach}
		{/block}
	</div>
{/block}

{block name="frontend_listing_text"}
    <div class="vendor--info panel has--border">

        {* Vendor headline *}
        {block name="frontend_listing_list_filter_supplier_headline"}
            <h1 class="panel--title is--underline">
                {s name='ListingInfoFilterSupplier'}{/s} {$manufacturer->getName()|escapeHtml}
            </h1>
        {/block}

        {* Vendor content e.g. description and logo *}
        {block name="frontend_listing_list_filter_supplier_content"}
            <div class="panel--body is--wide">

                {if $manufacturer->getCoverFile()}
                    <div class="vendor--image-wrapper">
						<img class="vendor--image" src="{$manufacturer->getCoverFile()}" alt="{$manufacturer->getName()|escape}">
					</div>
                {/if}

                {if $manufacturer->getDescription()}
                    <div class="vendor--text">
                        {$manufacturer->getDescription()}
                    </div>
                {/if}
            </div>
        {/block}
    </div>
{/block}

{block name="frontend_listing_index_topseller"}
{/block}
