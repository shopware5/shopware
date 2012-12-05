{* Filter supplier *}
{block name="frontend_listing_filter_supplier"}
{if $sSuppliers|@count>1 && $sCategoryContent.parent != 1}
<h2 class="headingbox">{s name='FilterSupplierHeadline'}{/s}</h2>
<div class="supplier">
	{foreach from=$sSuppliers key=supKey item=supplier name=supplier}{/foreach}
		{block name="frontend_listing_filter_supplier_each"}
		<ul>
	        {foreach from=$sSuppliers key=supKey item=supplier name=supplier}
	        {if $supplier.image} 
	            <li id="n{$supKey+1}" class="image"><a href="{$supplier.link}" title="{$supplier.name}"><img src="{link file=$supplier.image}" alt="{$supplier.name}" border="0" title="{$supplier.name}" /></a></li>
	        {else}
	            <li {if $smarty.foreach.supplier.last}class="last"{/if} id="n{$supKey+1}" {if $sSupplierInfo.name eq $supplier.name}class="active"{/if}><a href="{$supplier.link}" title="{$supplier.name}">{$supplier.name} ({$supplier.countSuppliers})</a></li>
	        {/if}
			{/foreach}		 
    	</ul>
    	{/block}
</div>
{/if}
{/block}