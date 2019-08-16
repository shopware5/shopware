{extends file='frontend/index/index.tpl'}

{block name='frontend_index_header'}
    {include file='frontend/content_type/listing_header.tpl'}
{/block}

{block name="frontend_index_body_classes"}{$smarty.block.parent} is--content-type{/block}

{* Main content *}
{block name='frontend_index_content'}
    <h1>{$sType->getName()|escape}</h1>

    {''|snippet:'IndexDescription':$sType->getSnippetNamespaceFrontend()}

    <div class="blog--content block-group">
        {foreach from=$sItems item=sItem}
            {include file="frontend/content_type/listing/item.tpl"}
        {/foreach}

        {if $sTotal > 10}
            {include file="frontend/content_type/pagination.tpl"}
        {/if}
    </div>
{/block}
