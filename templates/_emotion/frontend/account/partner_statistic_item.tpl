
<div class="table_row{if $lastitem} lastrow{/if}">

	{block name='frontend_account_partner_statistic_item_overview_row'}
	<div class="grid_3">
		{$partnerOrder.orderTime|date:datetime}
	</div>
	
	<div class="grid_2 prefix_1 bold">
		{$partnerOrder.number}
	</div>

    <div class="grid_2 prefix_2">
		{$partnerOrder.netTurnOver|currency}
	</div>

    <div class="grid_2 prefix_2">
        {$partnerOrder.provision|currency}
    </div>

	{/block}
</div>

{if $lastitem}
<div class="table_foot">
    {block name='frontend_account_partner_statistic_item_overview_row'}

        <div class="grid_2 bold textright">
            <div class="textright">
                <strong>
                    {se name="PartnerStatisticItemSum"}{/se}
                </strong>
            </div>
        </div>

        <div class="grid_4 prefix_6">
            <strong>
            {$sTotalPartnerAmount.netTurnOver|currency}
            </strong>
        </div>

        <div class="grid_1">
            <strong>
            {$sTotalPartnerAmount.provision|currency}
            </strong>
        </div>

    {/block}
</div>
{/if}
