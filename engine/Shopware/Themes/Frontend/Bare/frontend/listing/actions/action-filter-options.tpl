{* Filter options which will be included in the "listing/listing_actions.tpl" *}
{namespace name="frontend/listing/listing_actions"}

{block name='frontend_listing_actions_filter'}

	<div class="action--filter-options off-canvas">

		{block name='frontend_listing_actions_filter_container'}

			<a href="#" class="filter--close-btn">
				{s name="ListingActionsCloseFilter"}Filter schließen{/s} <i class="icon--arrow-right"></i>
			</a>

			{if $sPropertiesOptionsOnly|@count or $sSuppliers|@count>1 && $sCategoryContent.parent != 1}
				<div class="filter--container">

					{block name="frontend_listing_actions_filter_container_inner"}

						<h2 class="filter--headline">{s name='FilterHeadline'}Filtern nach:{/s}</h2>

						{* Properties filter *}
						{if $sPropertiesOptionsOnly|@count}
							{block name="frontend_listing_actions_filter_properties"}
								{include file='frontend/listing/filter_properties.tpl'}
							{/block}
						{/if}

						{block name='frontend_listing_actions_filter_supplier'}
							{* Supplier filter *}
							{if $sSuppliers|@count>1 && $sCategoryContent.parent != 1}
								{include file='frontend/listing/filter_supplier.tpl'}
							{/if}
						{/block}

					{/block}

				</div>
			{/if}

		{/block}

	</div>

{/block}