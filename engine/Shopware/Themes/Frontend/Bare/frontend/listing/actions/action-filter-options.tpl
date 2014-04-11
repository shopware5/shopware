{* Filter options which will be included in the "listing/listing_actions.tpl" *}
{namespace name="frontend/listing/listing_actions"}

{block name='frontend_listing_actions_filter_properties'}

	<div class="action--filter-options">

	{if $sPropertiesOptionsOnly|@count or $sSuppliers|@count>1 && $sCategoryContent.parent != 1}
		<div class="filter--container">

			{* Properties filter *}
			{if $sPropertiesOptionsOnly|@count}
				{include file='frontend/listing/filter_properties.tpl'}
			{/if}

			{block name='frontend_listing_actions_filter_supplier'}
				{* Supplier filter *}
				{if $sSuppliers|@count>1 && $sCategoryContent.parent != 1}
					{include file='frontend/listing/filter_supplier.tpl'}
				{/if}
			{/block}
		</div>
	{/if}

	</div>

{/block}