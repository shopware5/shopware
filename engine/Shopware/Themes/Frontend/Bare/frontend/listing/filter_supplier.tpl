{* Filter supplier *}
{block name="frontend_listing_filter_supplier"}
	{if $sSuppliers|@count>1 && $sCategoryContent.parent != 1}
		<span class="collapse--header filter--indicator" data-collapse-panel="true">
			{s name='FilterSupplierHeadline'}{/s} <span class="filter--expand-collapse collapse--toggler"></span>
		</span>

		{foreach from=$sSuppliers key=supKey item=supplier name=supplier}{/foreach}
		{block name="frontend_listing_filter_supplier_each"}
			<div class="filter--content collapse--content">
				<ul class="filter--list{if $sSuppliers|@count > 5} content--scrollable{/if}">
					{foreach from=$sSuppliers key=supKey item=supplier name=supplier}
						{if $supplier.image}
							<li id="n{$supKey+1}" class="filter--entry entry--image{if $sSupplierInfo.name eq $supplier.name} is--active{/if}">
								{if $sSupplierInfo.name eq $supplier.name}
									<img class="filter--image" src="{link file=$supplier.image}" alt="{$supplier.name}">
								{else}
									<a class="filter--link" href="{$supplier.link}" title="{$supplier.name}">
										<img class="filter--image" src="{link file=$supplier.image}" alt="{$supplier.name}">
									</a>
								{/if}
							</li>
						{else}
							<li class="filter--entry{if $sSupplierInfo.name eq $supplier.name} is--active{/if}{if $smarty.foreach.supplier.last} is--last{/if}" id="n{$supKey+1}">
								{if $sSupplierInfo.name eq $supplier.name}
									{$supplier.name} ({$supplier.countSuppliers})
								{else}
									<a class="filter--link" href="{$supplier.link}" title="{$supplier.name}">
                                        {$supplier.name}
										({$supplier.countSuppliers})
                                    </a>
								{/if}
							</li>
						{/if}
					{/foreach}

				</ul>
				{if $sSupplierInfo.name}
					<ul class="filter--list">
						<li class="filter--entry">
							<a class="filter--link link--close" href="{$sSupplierInfo.link}" title="{s name='FilterLinkDefault'}Alle Anzeigen{/s}">
								{se name='FilterLinkDefault'}Alle Anzeigen{/se}
							</a>
						</li>
					</ul>
				{/if}
			</div>
		{/block}
	{/if}
{/block}