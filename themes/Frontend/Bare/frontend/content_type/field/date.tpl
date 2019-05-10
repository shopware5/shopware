{extends file="frontend/content_type/field/base.tpl"}

{block name='frontend_content_type_field_base_label'}
    <span title="{$detail->getLabel()}">{$detail->getLabel()}</span>
{/block}

{block name='frontend_content_type_field_base_content'}
    <span>{$content|date:"DATE_LONG"}</span>
{/block}