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

{* Main content *}
{block name='frontend_index_content'}
    <div class="content listing--content">
        {action controller=listing action=layout params=$params}
    </div>
{/block}

{* Sidebar right *}
{block name='frontend_index_content_right'}{/block}
