{namespace name="frontend/listing/listing"}

{* Categorie headline *}
{block name="frontend_listing_text"}
    {if $sCategoryContent.cmsheadline || $sCategoryContent.cmstext}
        <div class="hero-unit category--teaser panel has--border is--rounded">

            {* Headline *}
            {block name="frontend_listing_text_headline"}
                {if $sCategoryContent.cmsheadline}
                    <h1 class="hero--headline panel--title">{$sCategoryContent.cmsheadline}</h1>
                {/if}
            {/block}

            {* Category text *}
            {block name="frontend_listing_text_content"}
                <div class="hero--text panel--body is--wide">
                    {if $sCategoryContent.cmstext}

                        {* Long description *}
                        {block name="frontend_listing_text_content_long"}
                            <div class="teaser--text-long">
                                {$sCategoryContent.cmstext}
                            </div>
                        {/block}

                        {* Short description *}
                        {block name="frontend_listing_text_content_short"}
                            <div class="teaser--text-short is--hidden">
                                {$sCategoryContent.cmstext|strip_tags|truncate:200}
                                {s namespace="frontend/listing/listing" name="ListingActionsOpenOffCanvas" assign="snippetListingActionsOpenOffCanvas"}{/s}
                                <a href="#" title="{$snippetListingActionsOpenOffCanvas|escape}" class="text--offcanvas-link">
                                    {s namespace="frontend/listing/listing" name="ListingActionsOpenOffCanvas"}{/s} &raquo;
                                </a>
                            </div>
                        {/block}

                        {* Off Canvas Container *}
                        {block name="frontend_listing_text_content_offcanvas"}
                            <div class="teaser--text-offcanvas is--hidden">

                                {* Close Button *}
                                {block name="frontend_listing_text_content_offcanvas_close"}
                                    {s namespace="frontend/listing/listing" name="ListingActionsCloseOffCanvas" assign="snippetListingActionsCloseOffCanvas"}{/s}
                                    <a href="#" title="{$snippetListingActionsCloseOffCanvas|escape}" class="close--off-canvas">
                                        <i class="icon--arrow-left"></i> {s namespace="frontend/listing/listing" name="ListingActionsCloseOffCanvas"}{/s}
                                    </a>
                                {/block}
                                {* Off Canvas Content *}
                                {block name="frontend_listing_text_content_offcanvas_content"}
                                    <div class="offcanvas--content">
                                        <div class="content--title">{$sCategoryContent.cmsheadline}</div>
                                        {$sCategoryContent.cmstext}
                                    </div>
                                {/block}
                            </div>
                        {/block}
                    {/if}
                </div>
            {/block}
        </div>
    {/if}
{/block}
