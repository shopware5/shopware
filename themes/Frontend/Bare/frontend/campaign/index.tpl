{extends file='parent:frontend/home/index.tpl'}

{block name='frontend_index_header_canonical'}
    <link rel="canonical" href="{url controller=campaign emotionId=$landingPage.id}" />
{/block}

{* Google optimized crawling *}
{block name='frontend_index_header_meta_tags' append}
    {if !$hasEscapedFragment}
        <meta name="fragment" content="!">
    {/if}
{/block}

{* Keywords *}
{block name="frontend_index_header_meta_keywords"}{if $seo_keywords}{$seo_keywords}{/if}{/block}

{* Description *}
{block name="frontend_index_header_meta_description"}{if $seo_description}{$seo_description}{/if}{/block}

{* Promotion *}
{block name='frontend_home_index_promotions'}
    {foreach $landingPage.emotions as $emotion}

        <div class="content--emotions">
            {if $hasEscapedFragment}
                {if 0|in_array:$emotion.devicesArray}
                    <div class="emotion--fragment">
                        {action module=widgets controller=campaign action=index emotionId=$emotion.id controllerName=$Controller}
                    </div>
                {/if}
            {else}
                <div class="emotion--wrapper"
                     data-controllerUrl="{url module=widgets controller=emotion action=index emotionId=$emotion.id controllerName=$Controller}"
                     data-availableDevices="{$emotion.devices}"
                     data-showListing="false">
                </div>
            {/if}
        </div>
    {/foreach}
{/block}