{* Filter options which will be included in the "listing/listing_actions.tpl" *}
{namespace name="frontend/listing/listing_actions"}

{block name='frontend_listing_actions_filter'}

	{if $facets|count > 0}
		<div class="action--filter-options off-canvas">

			{block name='frontend_listing_actions_filter_close_button'}
				<a href="#" class="filter--close-btn">
					{s name="ListingActionsCloseFilter"}{/s} <i class="icon--arrow-right"></i>
				</a>
			{/block}

			{block name='frontend_listing_actions_filter_container'}
				<div class="filter--container">

					{block name="frontend_listing_actions_filter_active_filters"}
						<div class="filter--active-container"
							 data-reset-label="{s name='ListingFilterResetAll'}{/s}">
						</div>
					{/block}

					{block name='frontend_listing_actions_filter_form'}
						<form id="filter"
							  method="get"
							  data-filter-form="true"
							  data-is-filtered="{$criteria->getCustomerConditions()|count}"
							  data-load-facets="{config name=generatePartialFacets}"
							  data-count-ctrl="{$countCtrlUrl}">

							{block name="frontend_listing_actions_filter_submit_button"}
								<div class="filter--actions">
									<button type="submit"
									        class="btn is--primary filter--btn-apply is--large is--icon-right"
									        disabled="disabled">
										<span class="filter--count"></span>
										{s name="ListingFilterApplyButton"}{/s}
										<i class="icon--cycle"></i>
									</button>
								</div>
							{/block}

							{block name="frontend_listing_actions_filter_form_page"}
								<input type="hidden" name="{$shortParameters['sPage']}" value="1" />
							{/block}

							{block name="frontend_listing_actions_filter_form_search"}
								{if $term}
									<input type="hidden" name="{$shortParameters['sSearch']}" value="{$term|escape}" />
								{/if}
							{/block}

							{block name="frontend_listing_actions_filter_form_sort"}
								{if $sSort}
									<input type="hidden" name="{$shortParameters['sSort']}" value="{$sSort|escape}" />
								{/if}
							{/block}

							{block name="frontend_listing_actions_filter_form_perpage"}
								{if $criteria && $criteria->getLimit()}
									<input type="hidden" name="{$shortParameters['sPerPage']}" value="{$criteria->getLimit()|escape}" />
								{/if}
							{/block}

							{block name="frontend_listing_actions_filter_form_category"}
								{if !$sCategoryContent && $sCategoryCurrent != $sCategoryStart}
									<input type="hidden" name="{$shortParameters['sCategory']}" value="{$sCategoryCurrent|escape}" />
								{/if}
							{/block}

                            {block name="frontend_listing_actions_filter_form_facets"}
                                {include file="frontend/listing/actions/action-filter-facets.tpl" facets=$facets}
                            {/block}
						</form>
					{/block}
				</div>
			{/block}
		</div>
	{/if}
{/block}
