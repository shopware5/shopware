{* Categorie headline *}
{block name="frontend_listing_text"}
	<div class="hero-unit category--teaser">

        {* Headline *}
        {block name="frontend_listing_text_headline"}
            {if $sCategoryContent.cmsheadline}
                <h1 class="hero--headline">{$sCategoryContent.cmsheadline}</h1>
            {/if}
        {/block}

        {* Category text *}
        {block name="frontend_listing_text_content"}
            {if $sCategoryContent.cmstext}
                {$sCategoryContent.cmstext}
            {/if}
        {/block}
	</div>
{/block}