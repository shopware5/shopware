{extends file='frontend/index/index.tpl'}

{block name="frontend_index_content_top"}{/block}

{* Page title *}
{block name='frontend_index_header_title'}{strip}
    {if $sCategoryContent.metaTitle}
        {$sCategoryContent.metaTitle|escapeHtml} | {{config name=sShopname}|escapeHtml}
    {else}
        {$smarty.block.parent}
    {/if}
{/strip}{/block}

{* Canonical URL *}
{block name='frontend_index_header_canonical'}
    <link rel="canonical" href="{url controller='index'}" />
{/block}

{* Main content *}
{block name='frontend_index_content'}
    <div class="content content--home">

        {block name='frontend_home_index_banner'}
            {* Banner *}
            {include file='frontend/listing/banner.tpl'}
        {/block}

        {block name='frontend_home_index_text'}
            {* Category headline *}
            {if !$hasEmotion}
                {include file='frontend/listing/text.tpl'}
            {/if}
        {/block}

        {* Promotion *}
        {block name='frontend_home_index_promotions'}
            {if $hasCustomerStreamEmotion}
                {action module=frontend controller=listing action=layout sCategory=$sCategoryContent.id}
            {elseif $hasEmotion}
                <div class="content--emotions">
                    {foreach $emotions as $emotion}
                        {block name='frontend_home_index_emotion_wrapper'}
                            {include file="frontend/_includes/emotion.tpl"}
                        {/block}
                    {/foreach}
                </div>
            {/if}
        {/block}

        {block name='frontend_home_index_blog'}
            {* Blog Articles *}
            {if $sBlog.sArticles|@count}
                <div class="listing_box">
                    <h2 class="headingbox_nobg largesize">{s name='WidgetsBlogHeadline'}{/s}:</h2>
                    {foreach from=$sBlog.sArticles item=article key=key name="counter"}
                        {include file="frontend/blog/box.tpl" sArticle=$article key=$key homepage=true}
                    {/foreach}
                </div>
            {/if}
        {/block}

        {* Tagcloud *}
        {block name='frontend_home_index_tagcloud'}
            {if {config name=show namespace=TagCloud } && !$isEmotionLandingPage}
                {action module=widgets controller=listing action=tag_cloud sController=index}
            {/if}
        {/block}
    </div>
{/block}

{block name='frontend_index_left_last_articles'}{/block}
