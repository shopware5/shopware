{* Filter button which will be included in the "listing/listing_actions.tpl" *}
{namespace name="frontend/listing/listing_actions"}

{block name="frontend_listing_actions_filter_button"}

	{if $sPropertiesOptionsOnly|@count or $sSuppliers|@count>1 && $sCategoryContent.parent != 1}

		<div class="action--filter-btn">
			<a href="#" class="filter--trigger btn btn--primary" data-collapse-target=".action--filter-options">
				<i class="icon--compare"></i> {s name='ListingFilterButton'}Filter{/s}
			</a>
		</div>

	{/if}

{/block}
