{extends file="frontend/listing/filter/facet-date.tpl"}

{namespace name="frontend/listing/listing_actions"}

{block name="frontend_listing_filter_facet_date_content"}
    <div class="filter-panel--content input-type--date">

        {$value = ''}

        {foreach $facet->getValues() as $option}
            {if $option->isActive()}
                {$value = "{if $value !== ''}{$value}|{/if}{$option->getId()}"}
            {/if}

            {$enabledDates = "{if $enabledDates}{$enabledDates}, {/if}{$option->getId()}"}
        {/foreach}

        {block name="frontend_listing_filter_facet_date_multi_input"}
            <input type="text"
                   class="filter-panel--input"
                   name="{$facet->getFieldName()|escape:'htmlall'}"
                   id="{$facet->getFieldName()|escape:'htmlall'}"
                   placeholder="Select a date"
                   data-datepicker="true"
                   data-mode="multiple"
                   data-enableTime="{$enableTime}"
                   data-multiDateSeparator="|"
                   data-enabledDates="{$enabledDates}"
                   readonly="readonly"
                   value="{$value}" />
        {/block}
    </div>
{/block}