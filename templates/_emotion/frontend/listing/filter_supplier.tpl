{* Filter supplier *}
{block name="frontend_listing_filter_supplier"}
	{if $sSuppliers|@count>1 && $sCategoryContent.parent != 1}
		<div class="options">
			{s name='FilterSupplierHeadline'}{/s} <span class="expandcollapse">+</span>
		</div>
		{foreach from=$sSuppliers key=supKey item=supplier name=supplier}{/foreach}
		{block name="frontend_listing_filter_supplier_each"}
			<div class="slideContainer">
				<ul{if $sSuppliers|@count > 5} class="overflow"{/if}>
					{foreach from=$sSuppliers key=supKey item=supplier name=supplier}
						{if $supplier.image}
							<li id="n{$supKey+1}" class="image{if $sSupplierInfo.name eq $supplier.name} active{/if}">
								{if $sSupplierInfo.name eq $supplier.name}
									<img src="{link file=$supplier.image}" alt="{$supplier.name}" border="0"
										 title="{$supplier.name}"/>
								{else}
									<a href="{$supplier.link}" title="{$supplier.name}">
										<img src="{link file=$supplier.image}" alt="{$supplier.name}" border="0"
											 title="{$supplier.name}"/>
									</a>
								{/if}
							</li>
						{else}
							<li class="{if $sSupplierInfo.name eq $supplier.name}active{/if} {if $smarty.foreach.supplier.last}last{/if}"
								id="n{$supKey+1}">
								{if $sSupplierInfo.name eq $supplier.name}
									{$supplier.name} ({$supplier.countSuppliers})
								{else}
									<a href="{$supplier.link}" title="{$supplier.name}">{$supplier.name}
										({$supplier.countSuppliers})</a>
								{/if}
							</li>
						{/if}
					{/foreach}

				</ul>
				{if $sSupplierInfo.name}
					<ul>
						<li class="close">
							<a href="{$sSupplierInfo.link}" title="{s name='FilterLinkDefault'}Alle Anzeigen{/s}">
								{se name='FilterLinkDefault'}Alle Anzeigen{/se}
							</a>
						</li>
					</ul>
				{/if}
			</div>
		{/block}
	{/if}
{/block}