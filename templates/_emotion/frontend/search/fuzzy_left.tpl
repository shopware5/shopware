
<div class="grid_4 first" id="left">
	<div class="filter_search">
	{* Headline *}
	<h3 class="heading">{s name="SearchLeftHeadlineCutdown"}Suchergebnis einschr&auml;nken{/s}</h3>
	
	{if $sSearchResults.sPropertyGroups||$sRequests.sFilter.propertygroup}
	
	{* Filter by properties *}
	{block name='frontend_search_filter_properties'}
	<div class="searchbox">
        <style>
            #content #left .filter_search .searchbox li {
               height:100%
            }

        </style>
	 <h3>{se name='SearchLeftHeadlineFilter'}{/se}</h3>
	    
	    {if !$sRequests.sFilter.propertygroup}
	    <ul>
	    {foreach from=$sSearchResults.sPropertyGroups item=sPropertyGroup}
	        <li><a href="{$sLinks.sFilter.propertygroup}&sFilter_propertygroup={$sPropertyGroup.filerID}">{$sPropertyGroup.name} ({$sPropertyGroup.count})</a></li>
	    {/foreach}
	    </ul>
	    {else}
	    	<ul>
	    	{foreach from=$sSearchResults.sPropertyGroups item=sPropertyGroup}
	        	<li class="active">{$sPropertyGroup.name}</li>
	        {/foreach}
	        <li class="showall"><a href="{$sLinks.sFilter.propertygroup}">{se name='SearchLeftLinkAllFilters'}{/se}</a></li>
		    	<li><ul>
		    	{foreach from=$sSearchResults.sPropertyOptions item=sPropertyOption key=optionID}
		    		<li class="head">
		    		<h3>{$sPropertyOption.name}</h3>
		    		<ul>
		    		{if $sPropertyOption.selected}
		    			{foreach from=$sSearchResults.sPropertyValues.$optionID item=sPropertyValue key=valueID}
			        		<li class="active">{$sPropertyValue.name}</li>
		    			{/foreach}
		    			<li><a class="showall" href="{$sLinks.sFilter.propertygroup}&sFilter_propertygroup={$sRequests.sFilter.propertygroup|cat:'_'|replace:"_`$sPropertyOption.selected`_":'_'|trim:'_'|escape:'url'}">{se name='SearchLeftLinkDefault'}{/se}</a></li>	
		    		{else}
			        	{foreach from=$sSearchResults.sPropertyValues.$optionID item=sPropertyValue key=valueID}
			        		<li><a href="{$sLinks.sFilter.propertygroup}&sFilter_propertygroup={$sRequests.sFilter.propertygroup|escape:'url'}_{$valueID}">{$sPropertyValue.name} ({$sPropertyValue.count})</a></li>
			        	{/foreach}
			        	
		        	{/if}
		        	</ul>
		        	</li>
		    	{/foreach}
		    	</ul></li>
	    	</ul>
	    {/if}
	</div>
	{/block}
	{/if}
	
	{if $sSearchResults.sSuppliers}
	
	{* Filter by supplier *}
	{block name='frontend_search_filter_supplier'}
	<div class="searchbox">
	<h3>{se name='SearchLeftHeadlineSupplier'}{/se}</h3>
	{assign var=sSuppliersFirst value=$sSearchResults.sSuppliers|@array_slice:0:10}
	{assign var=sSuppliersRest value=$sSearchResults.sSuppliers|@array_slice:10}
	
	    <ul>
		    {if !$sRequests.sFilter.supplier}
		    {foreach from=$sSuppliersFirst item=supplier}
		        <li><a href="{$sLinks.sFilter.supplier}&sFilter_supplier={$supplier.id}">{$supplier.name}</a></li>
		    {/foreach}
		    
		    {if $sSuppliersRest}
		    <form name="frmsup" method="POST" action="{$sLinks.sFilter.supplier}" id="frmsup">
		    <select name="sFilter_supplier" class="auto_submit">
		        <option value="">{se name='SearchLeftInfoSuppliers'}{/se}</option>
		    {foreach from=$sSuppliersRest item=supplier}
		        <option value="{$supplier.id}">{$supplier.name} ({$supplier.count})</option>
		    {/foreach}
		    </select>
		    </form>
		    {/if}
		    {else}
		        <li class="active">{$sSearchResults.sSuppliers[$sRequests.sFilter.supplier].name}</li>
		        <li class="showall"><a href="{$sLinks.sFilter.supplier}">{se name='SearchLeftLinkAllSuppliers'}{/se}</a></li>
		    {/if}
	    </ul>
	
	</div>
	{/block}
	{/if}
	
	{* Filter by price *}
	{if $sSearchResults.sPrices||$sRequests.sFilter.price}
		{block name='frontend_search_filter_price'}
		<div class="searchbox">
			<h3>{se name='SearchLeftHeadlinePrice'}{/se}</h3>
			<ul>
			    {if !$sRequests.sFilter.price}
			    {foreach from=$sPriceFilter item=sFilterPrice key=sKey}
			        {if $sSearchResults.sPrices.$sKey}
			            <li>
		            		<a href="{$sLinks.sFilter.price}&sFilter_price={$sKey}">
						{$sFilterPrice.start|currency} - {$sFilterPrice.end|currency}
		           				{if $sFilterActive.price}{/if}
		           			</a>
			           	</li>
			        {/if}
			    {/foreach}
			    
			    {else}
			        <li class="active">{$sPriceFilter[$sRequests.sFilter.price].start|currency} - {$sPriceFilter[$sRequests.sFilter.price].end|currency}</li>
			        <li class="showall"><a href="{$sLinks.sFilter.price}">{se name='SearchLeftLinkAllPrices'}{/se}</a></li>
			    {/if}
			</ul>
		</div>
		{/block}
	{/if}
	</div>
</div>
