{extends file='parent:frontend/listing/index.tpl'}

{* Sidebar right *}
{block name='frontend_index_content_right'}{/block}

{* Tagcloud *}
{block name="frontend_listing_index_tagcloud"}
{if $sCloudShow}
	{action module=widgets controller=listing action=tag_cloud sCategory=$sCategoryContent.id}
{/if}
{/block}

{* Topseller slider *}
{block name="frontend_listing_index_listing" prepend}
    {if !$hasEmotion && !$sSupplierInfo}
        {action module=widgets controller=listing action=top_seller sCategory=$sCategoryContent.id}
    {/if}
{/block}

{block name="frontend_listing_index_listing" append}
{if $sCategoryContent.parent != 1 && $hasEmotion && !$sSupplierInfo}
    <div class="emotion-link">
        <a class="emotion-offers" href="{url controller='cat' sPage=1 sCategory=$sCategoryContent.id}">
            {s name="ListingActionsOffersLink"}Weitere Artikel in dieser Kategorie{/s}
        </a>
    </div>
    <div class="space">&nbsp;</div>
{/if}
{/block}

{* Trusted shops logo *}
{block name='frontend_index_left_trustedshops'}

    {block name="frontend_listing_left_additional_features"}
        {include file="frontend/listing/right.tpl"}
        <div class="clear">&nbsp;</div>
    {/block}

    {if {config name=TSID}}
        {include file='frontend/plugins/trusted_shops/logo.tpl'}
    {/if}
{/block}

{* Hide listing if we're having a emotion here *}
{block name="frontend_listing_list_inline"}
    {if !$hasEmotion}
        {$smarty.block.parent}
    {/if}
{/block}

{* Listing actions top *}
{block name="frontend_listing_top_actions"}
    {if !$hasEmotion}
        {$smarty.block.parent}
    {/if}
{/block}

{* Listing actions bottom *}
{block name="frontend_listing_bottom_paging"}
    {if !$hasEmotion}
        {$smarty.block.parent}
    {/if}
{/block}

{* Category text *}
{block name="frontend_listing_index_text"}
    {if !$hasEmotion && !$sSupplierInfo}
        {$smarty.block.parent}
    {/if}
{/block}
