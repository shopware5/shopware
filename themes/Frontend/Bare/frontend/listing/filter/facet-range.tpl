{namespace name="frontend/listing/listing_actions"}

{block name="frontend_listing_filter_facet_range"}
	<div class="filter-panel filter--range facet--{$facet->getFacetName()}"
		 data-filter-type="range"
		 data-field-name="{$facet->getFacetName()}">

		{block name="frontend_listing_filter_facet_range_flyout"}
			<div class="filter-panel--flyout">

				{block name="frontend_listing_filter_facet_range_title"}
					<label class="filter-panel--title">
						{$facet->getLabel()}
					</label>
				{/block}

				{block name="frontend_listing_filter_facet_range_icon"}
					<span class="filter-panel--icon"></span>
				{/block}

				{block name="frontend_listing_filter_facet_range_content"}
					<div class="filter-panel--content">

						{$startMin = $facet->getActiveMin()}
						{$startMax = $facet->getActiveMax()}

						{block name="frontend_listing_filter_facet_range_slider"}
							<div class="range-slider"
								 data-range-slider="true"
								 data-startMin="{$startMin}"
								 data-startMax="{$startMax}"
								 data-rangeMin="{$facet->getMin()}"
								 data-rangeMax="{$facet->getMax()}">

								{block name="frontend_listing_filter_facet_range_input_min"}
									<input type="hidden"
										   id="{$facet->getMinFieldName()}"
										   name="{$facet->getMinFieldName()}"
										   data-range-input="min"
										   value="{$startMin}" {if !$facet->isActive() || $startMin == 0}disabled="disabled" {/if}/>
								{/block}

								{block name="frontend_listing_filter_facet_range_input_max"}
									<input type="hidden"
										   id="{$facet->getMaxFieldName()}"
										   name="{$facet->getMaxFieldName()}"
										   data-range-input="max"
										   value="{$startMax}" {if !$facet->isActive() || $startMax == 0}disabled="disabled" {/if}/>
								{/block}

								{block name="frontend_listing_filter_facet_range_format_helper"}
									<div class="range-slider--currency" data-range-currency=""></div>
								{/block}

								{block name="frontend_listing_filter_facet_range_info"}
									<div class="filter-panel--range-info">

										{block name="frontend_listing_filter_facet_range_info_min"}
											<span class="range-info--min">
												{s name="ListingFilterRangeFrom"}von{/s}
											</span>
										{/block}

										{block name="frontend_listing_filter_facet_range_label_min"}
											<label class="range-info--label"
												   for="{$facet->getMinFieldName()}"
												   data-range-label="min">
												{$startMin}
											</label>
										{/block}

										{block name="frontend_listing_filter_facet_range_info_max"}
											<span class="range-info--max">
												{s name="ListingFilterRangeTo"}bis{/s}
											</span>
										{/block}

										{block name="frontend_listing_filter_facet_range_label_max"}
											<label class="range-info--label"
												   for="{$facet->getMaxFieldName()}"
												   data-range-label="max">
												{$startMax}
											</label>
										{/block}
									</div>
								{/block}
							</div>
						{/block}
					</div>
				{/block}
			</div>
		{/block}
	</div>
{/block}