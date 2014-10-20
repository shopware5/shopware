{block name='frontend_detail_data_block_prices_start'}
	<div class="block-prices--container{if $hidden && !$sArticle.selected} is--hidden{/if} block-price--{$sArticle.ordernumber}">

		{* @deprecated *}
		{block name='frontend_detail_data_block_prices_headline'}{/block}

		{block name="frontend_detail_data_block_prices_table"}
			<table class="block-prices--table">
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
					{foreach $sArticle.sBlockPrices as $blockPrice}
						{block name='frontend_detail_data_block_prices'}
							<tr class="{cycle values="is--primary,is--secondary"}">
								<td>
									{if $blockPrice.from == 1}
										{s namespace="frontend/detail/data" name="DetailDataInfoUntil"}{/s} {$blockPrice.to}
									{else}
										{s namespace="frontend/detail/data" name="DetailDataInfoFrom"}{/s} {$blockPrice.from}
									{/if}
								</td>
								<td>
									{$blockPrice.price|currency} {s name="Star" namespace="frontend/listing/box_article"}{/s}
								</td>
							</tr>
						{/block}
					{/foreach}
				</tbody>
			</table>
		{/block}
	</div>
{/block}