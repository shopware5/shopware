{namespace name="frontend/listing/listing_actions"}

{block name="frontend_listing_filter_facet_boolean"}
	<div class="filter-panel filter--value facet--{$facet->getFacetName()}"
		 data-filter-type="value"
         data-field-name="{$facet->getFieldName()}">

		{block name="frontend_listing_filter_facet_boolean_flyout"}
			<div class="filter-panel--flyout">

				{block name="frontend_listing_filter_facet_boolean_title"}
					<label class="filter-panel--title" for="{$facet->getFieldName()}">
						{$facet->getLabel()}
					</label>
				{/block}

				{block name="frontend_listing_filter_facet_boolean_checkbox"}
					<span class="filter-panel--checkbox">
						<input type="checkbox"
							   id="{$facet->getFieldName()}"
							   name="{$facet->getFieldName()}"
							   value="1"
							   {if $facet->isActive()}checked="checked" {/if}/>

						<span class="checkbox--state">&nbsp;</span>
					</span>
				{/block}
			</div>
		{/block}
	</div>
{/block}