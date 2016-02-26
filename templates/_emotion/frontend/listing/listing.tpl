{* Promotion *}
{include file='frontend/listing/promotions.tpl' sTemplate=$sTemplate}

{* Sorting and changing layout *}
{block name="frontend_listing_top_actions"}
	{if $showListing && !$sOffers}
		{include file='frontend/listing/listing_actions.tpl' sTemplate=$sTemplate}
	{/if}
{/block}

{* Supplier filter *}
{block name="frontend_listing_list_filter_supplier"}
	{if $sSupplierInfo}
		<div id="supplierfilter_top" {if $sSupplierInfo.image}class="supplierfilter_image"{/if}>
			<div class="cat_text">
				<div class="inner_container">
					<h3>{se name='ListingInfoFilterSupplier'}{/se} {$sSupplierInfo.name}</h3>
			        <div class="inner-supplier">
			            {if $sSupplierInfo.description}
			                <div class="supplier-desc">
			                    {if $sSupplierInfo.image}
			                        <img src="{$sSupplierInfo.image}" alt="{$sSupplierInfo.name}" name="{$sSupplierInfo.name}" class="right" border="0" title="{$sSupplierInfo.name}" />
			                    {/if}
			                    {$sSupplierInfo.description}
	                            <div class="clear"></div>
			                </div>
			            {else}
			                {if $sSupplierInfo.image}
			                    <img src="{$sSupplierInfo.image}" alt="{$sSupplierInfo.name}" name="{$sSupplierInfo.name}" class="right" border="0" title="{$sSupplierInfo.name}" />
			                {/if}
	                        <div class="clear"></div>
			            {/if}
			        </div>
				</div>
			</div>
			<div class="clear">&nbsp;</div>
			{if $sSupplierInfo.link}
				<div class="{if !$sSupplierInfo.description}no-desc{else}right{/if}">
					<a href="{$sSupplierInfo.link}" title="{s name='ListingLinkAllSuppliers'}{/s}" class="close">
						{se name='ListingLinkAllSuppliers'}{/se}
					</a>
				</div>
			{/if}
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
            {if $showListing}
                {include file="frontend/listing/listing_articles.tpl"}
            {/if}
        {/block}
    </div>
{/if}

{* Paging *}
{block name="frontend_listing_bottom_paging"}
	{if $showListing}
		{if !$sOffers}
		    {include file='frontend/listing/listing_actions.tpl' sTemplate=$sTemplate}
		{else}
			{if $sCategoryContent.parent != 1}
			<div class="actions_offer">
				{include file='frontend/listing/listing_actions.tpl' sTemplate=$sTemplate}
			</div>
			{/if}
		{/if}
	{/if}
{/block}