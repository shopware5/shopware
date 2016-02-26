{namespace name="frontend/listing/box_article"}

<div class="product--actions">

    {* Compare button *}
    {block name='frontend_listing_box_article_actions_compare'}
        {if {config name="compareShow"}}
            <a href="{url controller='compare' action='add_article' articleID=$sArticle.articleID}"
               title="{s name='ListingBoxLinkCompare'}{/s}"
               class="product--action action--compare"
               data-product-compare-add="true"
               rel="nofollow">
                <i class="icon--compare"></i> {s name='ListingBoxLinkCompare'}{/s}
            </a>
        {/if}
    {/block}

    {* Note button *}
    {block name='frontend_listing_box_article_actions_save'}
        <a href="{url controller='note' action='add' ordernumber=$sArticle.ordernumber}"
           title="{"{s name='DetailLinkNotepad' namespace='frontend/detail/actions'}{/s}"|escape}"
           class="product--action action--note"
           data-ajaxUrl="{url controller='note' action='ajaxAdd' ordernumber=$sArticle.ordernumber}"
           data-text="{s name="DetailNotepadMarked"}{/s}"
           rel="nofollow">
            <i class="icon--heart"></i> <span class="action--text">{s name="DetailLinkNotepadShort" namespace="frontend/detail/actions"}{/s}</span>
        </a>
    {/block}

    {* @deprecated: block no longer in use *}
    {block name='frontend_listing_box_article_actions_more'}{/block}

    {* @deprecated: misleading name *}
    {block name="frontend_listing_box_article_actions_inline"}{/block}
</div>