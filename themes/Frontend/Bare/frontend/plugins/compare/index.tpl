{* Compare container *}
{block name='frontend_index_navigation_inline'}
    {$smarty.block.parent}
    {include file='frontend/compare/index.tpl'}
{/block}

{* Compare result *}
{block name='frontend_index_body_inline'}
    {$smarty.block.parent}
    <div id="compare_bigbox"></div>
{/block}


{* Compare button *}
{block name='frontend_listing_box_article_actions_buy_now'}
    {s name="ListingBoxLinkCompare" assign="snippetListingBoxLinkCompare"}{/s}
    <a href="{url controller='compare' action='add_article' articleID=$sArticle.articleID}"
       rel="nofollow"
       title="{$snippetListingBoxLinkCompare|escape}"
       class="product--action action--compare btn is--secondary is--icon-right">
        {s name='ListingBoxLinkCompare'}{/s}
        <i class="icon--arrow-right"></i>
    </a>
    {$smarty.block.parent}
{/block}

{* Compare button 2 *}
{block name='frontend_detail_actions_notepad'}
    {s name="DetailActionLinkCompare" assign="snippetDetailActionLinkCompare"}{/s}
    <a href="{url controller='compare' action='add_article' articleID=$sArticle.articleID}" rel="nofollow" title="{$snippetDetailActionLinkCompare|escape}" class="action--link action--compare">
        <i class="icon--compare"></i> {s name="DetailActionLinkCompare"}{/s}
    </a>
    {$smarty.block.parent}
{/block}

{* Compare button note *}
{block name='frontend_note_item_actions_compare'}
    {s name="ListingBoxLinkCompare" assign="snippetListingBoxLinkCompare"}{/s}
    <a href="{url controller='compare' action='add_article' articleID=$sBasketItem.articleID}" class="product--action action--compare btn is--secondary" title="{$snippetListingBoxLinkCompare|escape}" rel="nofollow">
        {s name='ListingBoxLinkCompare'}{/s}
    </a>
{/block}
