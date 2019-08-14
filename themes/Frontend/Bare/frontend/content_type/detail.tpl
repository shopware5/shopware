{extends file='frontend/index/index.tpl'}

{block name='frontend_index_header'}
    {include file='frontend/content_type/detail_header.tpl'}
{/block}

{block name="frontend_index_body_classes"}{$smarty.block.parent} is--content-type{/block}

{* Main content *}
{block name='frontend_index_content'}

    {$titleFieldName = $sType->getViewTitleFieldName()}
    {$descriptionFieldName = $sType->getViewDescriptionFieldName()}
    {$imageFieldName = $sType->getViewImageFieldName()}
    {$previewImage = null}

    {* Pick the first image as a preview, if a media-grid is used *}
    {if is_array($sItem[$imageFieldName][0])}
        {$previewImage = $sItem[$imageFieldName][0]}
    {else}
        {$previewImage = $sItem[$imageFieldName]}
    {/if}

    {block name='frontend_content_type_detail_wrapper'}
        <div class="content-type {$sType->getInternalName()}">
            {block name='frontend_content_type_detail_wrapper_inner'}

                {include file='frontend/content_type/detail_head.tpl' title=$sItem[$titleFieldName] description=$sItem[$descriptionFieldName] image=$previewImage}

                {block name='frontend_content_type_detail_body'}
                    <div class="content-type--body panel--table has--border is--rounded">

                        {foreach $sFields as $field}
                            {if $field.name === $titleFieldName || $field.name === $descriptionFieldName || $field.name === $imageFieldName || $field.name === $sMetaTitleKey || $field.name === $sMetaDescriptionKey}
                                {continue}
                            {/if}

                            {block name='frontend_content_type_detail_body_field_wrapper'}
                                <div class="content-type--field content-type--field-{$field.type} {$sType->getInternalName()}-{$field.name} panel--tr">
                                    {block name='frontend_content_type_detail_body_field_include'}

                                        {include file=$field.template content=$sItem[$field.name] detail=$field}

                                    {/block}
                                </div>
                            {/block}
                        {/foreach}

                    </div>
                {/block}

            {/block}
        </div>
    {/block}

{/block}
