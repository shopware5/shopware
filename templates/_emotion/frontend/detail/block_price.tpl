{block name='frontend_detail_data_block_prices_start'}
	<div class="block-prices {$sArticle.ordernumber}{if $hidden && !$sArticle.selected} hidden{/if}">
		{block name='frontend_detail_data_block_prices_headline'}
			<div class="space">&nbsp;</div>
			<h5 class="bold">{se namespace="frontend/detail/data" name="DetailDataHeaderBlockprices"}{/se}</h5>
		{/block}

		{block name="frontend_detail_data_block_prices_table"}
			<table width="220" border="0" cellspacing="0" cellpadding="0" class="text">
				{block name="frontend_detail_data_block_prices_table_head"}
					<thead>
					<tr>
						<td width="160">
							<strong>{se namespace="frontend/detail/data" name="DetailDataColumnQuantity"}{/se}</strong>
						</td>
						<td width='140'>
							<strong>{se namespace="frontend/detail/data" name="DetailDataColumnPrice"}{/se}</strong>
						</td>
					</tr>
					</thead>
				{/block}

				<tbody>
				{foreach from=$sArticle.sBlockPrices item=row key=key}
					{block name='frontend_detail_data_block_prices'}
						<tr valign="top">
							<td>
								{if $row.from=="1"}
									{se namespace="frontend/detail/data" name="DetailDataInfoUntil"}{/se} {$row.to}
								{else}
									{se namespace="frontend/detail/data" name="DetailDataInfoFrom"}{/se} {$row.from}
								{/if}
							</td>
							<td>
								<strong>
									{$row.price|currency}*
								</strong>
							</td>
						</tr>
					{/block}
				{/foreach}
				</tbody>
			</table>
		{/block}
	</div>
{/block}