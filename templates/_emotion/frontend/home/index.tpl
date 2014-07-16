{extends file='parent:frontend/home/index.tpl'}

{* Tagcloud *}
{block name="frontend_home_index_tagcloud"}
{if {config name=show namespace=TagCloud } && !$isEmotionLandingPage}
	{action module=widgets controller=listing action=tag_cloud}
{/if}
{/block}

{* Topseller *}
{block name='frontend_home_right_topseller'}{/block}

{* Breadcrumb *}
{block name='frontend_index_breadcrumb'}
	<div class="clear"></div>
{/block}

{* Promotion *}
{block name='frontend_home_index_promotions'}
    {action module=widgets controller=emotion action=index categoryId=$sCategoryContent.id controllerName=$Controller}
{/block}

{* Sidebar left *}
{block name='frontend_index_content_left'}{/block}

{* Sidebar right *}
{block name='frontend_index_content_right'}{/block}

{* Category text *}
{block name='frontend_home_index_text'}
    {if !$hasEmotion}
        {$smarty.block.parent}
    {/if}
{/block}