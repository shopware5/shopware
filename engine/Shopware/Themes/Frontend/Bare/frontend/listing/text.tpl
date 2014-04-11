{* Categorie headline *}
{block name="frontend_listing_text"}
	<div class="hero-unit category--teaser panel has--border">

        {* Headline *}
        {block name="frontend_listing_text_headline"}
            {if $sCategoryContent.cmsheadline}
                <h1 class="hero--headline panel--title">{$sCategoryContent.cmsheadline}</h1>
            {/if}
        {/block}

        {* Category text *}
        {block name="frontend_listing_text_content"}
            {if $sCategoryContent.cmstext}
                <div class="hero--text panel--body wide"
					 data-collapse-show-more="{s name='ListingCategoryTeaserShowMore'}{/s}"
					 data-collapse-show-less="{s name='ListingCategoryTeaserShowLess'}{/s}">
                    {$sCategoryContent.cmstext}
                </div>
            {/if}
        {/block}
	</div>
{/block}