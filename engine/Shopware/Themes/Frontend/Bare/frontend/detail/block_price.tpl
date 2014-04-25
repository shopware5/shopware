{block name='frontend_detail_data_block_prices_start'}
	<div class="block-prices--table {$sArticle.ordernumber}{if $hidden && !$sArticle.selected} is--hidden{/if}">

		{* @deprecated *}
		{block name='frontend_detail_data_block_prices_headline'}{/block}

		{block name="frontend_detail_data_block_prices_table"}
			<table class="panel--table">
				{block name="frontend_detail_data_block_prices_table_head"}
					<thead>
						<tr>
							<th>
								{s namespace="frontend/detail/data" name="DetailDataColumnQuantity"}{/s}
							</th>
							<th>
								{s namespace="frontend/detail/data" name="DetailDataColumnPrice"}{/s}
							</th>
						</tr>
					</thead>
				{/block}

				<tbody>
				{foreach $sArticle.sBlockPrices as $row}
					{block name='frontend_detail_data_block_prices'}
						<tr class="{cycle values="is--primary,is--secondary"}">
							<td>
								{if $row.from == 1}
									{s namespace="frontend/detail/data" name="DetailDataInfoUntil"}{/s} {$row.to}
								{else}
									{s namespace="frontend/detail/data" name="DetailDataInfoFrom"}{/s} {$row.from}
								{/if}
							</td>
							<td>
								{$row.price|currency} {s name="Star" namespace="frontend/listing/box_article"}{/s}
							</td>
						</tr>
					{/block}
				{/foreach}
				</tbody>
			</table>
		{/block}
	</div>
{/block}