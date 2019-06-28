{extends file='parent:frontend/home/index.tpl'}

{block name='frontend_index_header_canonical'}
    <link rel="canonical" href="{url controller=campaign emotionId=$landingPage.id}" />
{/block}

{block name='frontend_index_header_title'}{strip}
    {if $seo_title}
        {$seo_title|escapeHtml} | {{config name=sShopname}|escapeHtml}
    {else}
        {$smarty.block.parent}
    {/if}
{/strip}{/block}

{* In case this campaign is rendered as a pageNotFoundError, we make sure the sidebar menu is hidden by setting the campaign body class *}
{block name="frontend_index_body_classes"}{$smarty.block.parent}{if {controllerName|lower} =='error'} is--ctl-campaign{/if}{/block}

{* Keywords *}
{block name="frontend_index_header_meta_keywords"}{if $seo_keywords}{$seo_keywords|escapeHtml}{/if}{/block}

{* Description *}
{block name="frontend_index_header_meta_description"}{if $seo_description}{$seo_description|escapeHtml}{/if}{/block}

{* Promotion *}
{block name='frontend_home_index_promotions'}
    {foreach $landingPage.emotions as $emotion}

        <div class="content--emotions">
            {block name='frontend_home_index_promotions_emotion'}
                {include file="frontend/_includes/emotion.tpl"}
            {/block}
        </div>
    {/foreach}
{/block}
