{* Properties filter *}
{block name='frontend_listing_right_filter_properties'}
	{if $sPropertiesOptionsOnly|@count or $sSuppliers|@count>1 && $sCategoryContent.parent != 1}
		<div class="filter_properties">
			<h2 class="headingbox_nobg filter_properties">{s name='FilterHeadline'}Filtern nach:{/s}</h2>
			<div class="supplier_filter">
				{* Properties filter *}
				{if $sPropertiesOptionsOnly|@count}
					{include file='frontend/listing/filter_properties.tpl'}
				{/if}
				{block name='frontend_listing_right_filter_supplier'}
				{* Supplier filter *}
				{if $sSuppliers|@count>1 && $sCategoryContent.parent != 1}
					{include file='frontend/listing/filter_supplier.tpl'}
				{/if}
				{/block}
			</div>
		</div>
	{/if}
	
{/block}

{* Topseller *}
{block name='frontend_listing_right_topseller'}{/block}
