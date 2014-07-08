<table class="grid_11 first table-configurator" cellpadding="0" cellspacing="0">
	<thead>
		<tr>
			<th>&nbsp;</th>
			{foreach from=$sArticle.sConfigurator.1.values item=option key=pos}
				<th>{$option.optionname}</th>
			{/foreach}
		</tr>
	</thead>
	<tbody>

	{foreach from=$sArticle.sConfiguratorValues item=values key=value1}
		<tr>
			<th>{$sArticle.sConfigurator.0.values[$value1].optionname}</th>
			{foreach from=$values item=value key=value2}
				<td>
					{if $value.active}
						<input type="radio" value="{$value.ordernumber}" name="sAdd"{if $value.sBlockPrices} class="block-prices"{/if}{if $sArticle.sConfigurator.0.values[$value1].selected&&$sArticle.sConfigurator.1.values[$value2].selected} checked="checked"{/if}/>
						{if $value.prices && $value.prices.0.to > 0}
                            {se namespace="frontend/detail/data" name="DetailDataInfoFrom"}{/se} {$value.prices.0.price|currency}
                        {else}
                            {$value.price|currency}
                        {/if}
					{else}
						&nbsp;
					{/if}
				</td>
			{/foreach}
		</tr>
	{/foreach}
	</tbody>
</table>

{* Article price *}
{block name='frontend_detail_data_price_info'}
    <div class="clear"></div>
    <p class="modal_open">
        {s namespace="frontend/detail/data" name="DetailDataPriceInfo"}{/s}
    </p>
{/block}