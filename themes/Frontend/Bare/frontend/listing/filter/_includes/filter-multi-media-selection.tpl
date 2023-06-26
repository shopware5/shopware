{extends file="parent:frontend/listing/filter/_includes/filter-multi-selection.tpl"}

{block name="frontend_listing_filter_facet_multi_selection_input"}
    {$name = "__{$facet->getFieldName()|escapeHtmlAttr}__{$option->getId()|escapeHtmlAttr}"}
    {if $singleSelection}
        {$name = {$facet->getFieldName()|escapeHtmlAttr} }
    {/if}

    <input type="{$inputType}"
       id="__{$facet->getFieldName()|escapeHtmlAttr}__{$option->getId()|escapeHtmlAttr}"
       name="{$name}"
       title="{$option->getLabel()|escapeHtmlAttr}"
       value="{$option->getId()|escapeHtmlAttr}"
       {if $option->isActive()}checked="checked" {/if}/>
{/block}
