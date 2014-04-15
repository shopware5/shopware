{* Campaign right top *}
{block name='frontend_listing_right_campaign_top'}
	{include file="frontend/campaign/box.tpl" sCategoryCampaigns=$sCampaigns.rightTop}
{/block}

{* Properties filter *}
{block name='frontend_listing_right_filter_properties'}
	{if $sPropertiesOptionsOnly|@count or $sSuppliers|@count>1 && $sCategoryContent.parent != 1}
		<div class="filter--container">

			{block name='frontend_listing_right_filter_container_inner'}

				<h2 class="filter--headline">{s name='FilterHeadline'}Filtern nach:{/s}</h2>

				{* Properties filter *}
				{if $sPropertiesOptionsOnly|@count}
					{block name="frontend_listing_right_filter_properties_groups"}
						{include file='frontend/listing/filter_properties.tpl'}
					{/block}
				{/if}

				{block name='frontend_listing_right_filter_supplier'}
					{* Supplier filter *}
					{if $sSuppliers|@count>1 && $sCategoryContent.parent != 1}
						{include file='frontend/listing/filter_supplier.tpl'}
					{/if}
				{/block}

			{/block}
		</div>
	{/if}
{/block}

{* Campaign right middle *}
{block name='frontend_listing_right_campaign_middle'}
	{include file="frontend/campaign/box.tpl" sCategoryCampaigns=$sCampaigns.rightMiddle}
{/block}

{* Topseller *}
{block name='frontend_listing_right_topseller'}{/block}

{* Campaign right bottom *}
{block name='frontend_listing_right_campaign_bottom'}
	{include file="frontend/campaign/box.tpl" sCategoryCampaigns=$sCampaigns.rightBottom}
{/block}