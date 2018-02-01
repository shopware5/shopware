{extends file='frontend/index/index.tpl'}

{block name='frontend_index_header'}
    {include file='frontend/listing/header.tpl'}
{/block}

{block name='frontend_index_content_left'}

    {block name='frontend_index_controller_url'}
        {* Controller url for the found products counter *}
        {$countCtrlUrl = "{url module="widgets" controller="listing" action="listingCount" params=$ajaxCountUrlParams fullPath}"}
    {/block}

    {include file='frontend/listing/sidebar.tpl'}
{/block}

{block name="frontend_index_content_main_classes"}
    {strip}{$smarty.block.parent} is--small{/strip}
{/block}

{* Main content *}
{block name='frontend_index_content_wrapper'}
    <div class="content--wrapper">
        <div class="content listing--content">
            {action module=frontend controller=listing action=layout params=$params}
        </div>
    </div>
{/block}

{* Sidebar right *}
{block name='frontend_index_content_right'}{/block}
