{block name="frontend_listing_list_inline_ajax"}
    {* Actual listing *}
    {if $showListing}
        {foreach $sArticles as $sArticle}
            {include file="frontend/listing/box_article.tpl" sTemplate=$sTemplate lastitem=$sArticle@last firstitem=$sArticle@first}
        {/foreach}
    {/if}
{/block}