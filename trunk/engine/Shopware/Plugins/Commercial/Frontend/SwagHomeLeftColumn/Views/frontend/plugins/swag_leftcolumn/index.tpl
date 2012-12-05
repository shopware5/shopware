{extends file='parent:frontend/home/index.tpl'}

{block name="frontend_index_header_css_screen" append}
    <style type="text/css">
        #center.home { width: {$middleContainerWidth}px }
    </style>
{/block}

{* Tagcloud *}
{block name="frontend_home_index_tagcloud"}
{if $sCloudShow}
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

{* Re-enable the left sidebar *}
{block name='frontend_index_content_left'}
    {include file='frontend/index/left.tpl'}
{/block}

{* Sidebar right *}
{block name='frontend_index_content_right'}{/block}

{* Category text *}
{block name='frontend_home_index_text'}
    {if !$hasEmotion}
        {$smarty.block.parent}
    {/if}
{/block}