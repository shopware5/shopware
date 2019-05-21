{extends file="frontend/content_type/field/base.tpl"}

{block name='frontend_content_type_field_base_label'}
    <span title="{$detail.label}">{$detail.label}</span>
{/block}

{block name='frontend_content_type_field_base_content'}
    {if $content > 0}
        <i class="icon--check"></i>
    {else}
        <i class="icon--cross"></i>
    {/if}
{/block}
