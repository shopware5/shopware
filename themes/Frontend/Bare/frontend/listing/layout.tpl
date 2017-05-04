{* Banner *}
{block name="frontend_listing_index_banner"}
    {if !$hasEmotion && !$isHomePage}
        {include file='frontend/listing/banner.tpl'}
    {/if}
{/block}

{* Category headline *}
{block name="frontend_listing_index_text"}
    {if !$hasEmotion && !$isHomePage}
        {include file='frontend/listing/text.tpl'}
    {/if}
{/block}

{* Topseller *}
{block name="frontend_listing_index_topseller"}
    {if !$hasEmotion && {config name=topSellerActive}  && !$isHomePage}
        {action module=widgets controller=listing action=top_seller sCategory=$sCategoryContent.id}
    {/if}
{/block}

{* Listing *}
{block name="frontend_listing_index_listing"}
    {include file='frontend/listing/listing_wrapper.tpl'}
{/block}

{* Tagcloud *}
{block name="frontend_listing_index_tagcloud"}
    {if {config name=show namespace=TagCloud } && !$isHomePage}
        {action module=widgets controller=listing action=tag_cloud sController=listing sCategory=$sCategoryContent.id}
    {/if}
{/block}