{namespace name="frontend/listing/listing"}

{if $sSupplierInfo}
    <div class="hero-unit vendor--info panel has--border">

        {* Vendor headline *}
        {block name="frontend_listing_list_filter_supplier_headline"}
            <h3 class="hero--headline panel--title">{s name='ListingInfoFilterSupplier'}{/s} {$sSupplierInfo.name}</h3>
        {/block}

        {* Vendor content e.g. description and logo *}
        {block name="frontend_listing_list_filter_supplier_content"}
            <div class="hero--content panel--body is--wide">
                {if $sSupplierInfo.description}
                    {if $sSupplierInfo.image}
                        <img class="hero--image" src="{$sSupplierInfo.image}" alt="{$sSupplierInfo.name}">
                    {/if}

                    <div class="hero--text">
						{$sSupplierInfo.description}
					</div>
                {else}
                    {if $sSupplierInfo.image}
                        <img class="hero--image" src="{$sSupplierInfo.image}" alt="{$sSupplierInfo.name}">
                    {/if}
                {/if}

                {* Clear vendor filtering *}
                {block name="frontend_listing_list_filter_supplier_clearing_link"}
                    <a class="hero--link" href="{$sSupplierInfo.link}" title="{s name='ListingLinkAllSuppliers'}{/s}">
                        {s name='ListingLinkAllSuppliers'}{/s}
                    </a>
                {/block}
            </div>
        {/block}
    </div>
{/if}