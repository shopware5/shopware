{* Properties filter *}
{block name='frontend_listing_right_filter_properties'}
	{if $sProperties|@count > 0 or $sSuppliers|@count > 1 && $sCategoryContent.parent != 1}
		<div class="filter_properties">
			<h2 class="headingbox_nobg filter_properties">{s name='FilterHeadline'}Filtern nach:{/s}</h2>

			<div class="supplier_filter">

                {include file='frontend/listing/filter_properties.tpl'}

                {block name='frontend_listing_right_filter_supplier'}
                    {include file='frontend/listing/filter_supplier.tpl'}
				{/block}
			</div>
		</div>
	{/if}
	
{/block}

{* Topseller *}
{block name='frontend_listing_right_topseller'}{/block}
