{extends file='frontend/index/index.tpl'}

{block name='frontend_index_content_left'}{/block}

{block name='frontend_index_header_meta_description'}{$sItem[$sDescriptionKey]|strip_tags|truncate:$SeoDescriptionMaxLength:'…'}{/block}
{block name='frontend_index_header_meta_description_og'}{$sItem[$sDescriptionKey]|strip_tags|truncate:$SeoDescriptionMaxLength:'…'}{/block}
{block name='frontend_index_header_meta_description_twitter'}{$sItem[$sDescriptionKey]|strip_tags|truncate:$SeoDescriptionMaxLength:'…'}{/block}

{* Main content *}
{block name='frontend_index_content'}
    {foreach $sFields as $field}
        <div class="block">
            {include file=$field.template content=$sItem[$field.name]}
        </div>
    {/foreach}
{/block}
