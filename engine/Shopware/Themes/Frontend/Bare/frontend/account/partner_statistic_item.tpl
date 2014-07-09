<div class="panel--tr">

	{block name='frontend_account_partner_statistic_item_overview_row'}
	<div class="panel--td column--date">
        <div class="column--label">
            {s name="PartnerStatisticColumnDate" namespace="frontend/account/partner_statistic"}{/s}:
        </div>

		{$partnerOrder.orderTime|date:datetime}
	</div>
	
	<div class="panel--td column--id is--bold">
        <div class="column--label">
            {s name="PartnerStatisticColumnId" namespace="frontend/account/partner_statistic"}{/s}:
        </div>

		{$partnerOrder.number}
	</div>

    <div class="panel--td column--price">
        <div class="column--label">
            {s name="PartnerStatisticColumnNetAmount" namespace="frontend/account/partner_statistic"}{/s}:
        </div>

		{$partnerOrder.netTurnOver|currency}
	</div>

    <div class="panel--td column--total">
        <div class="column--label">
            {s name="PartnerStatisticColumnProvision" namespace="frontend/account/partner_statistic"}{/s}:
        </div>

        {$partnerOrder.provision|currency}
    </div>

	{/block}
</div>

{if $lastitem}
<div class="panel--tr is--odd is--bold">
    {block name='frontend_account_partner_statistic_item_overview_row'}

        <div class="panel--td column--item-sum">
            {se name="PartnerStatisticItemSum"}{/se}
        </div>

        <div class="panel--td column--price">
            {$sTotalPartnerAmount.netTurnOver|currency}
        </div>

        <div class="panel--td column--total">
            {$sTotalPartnerAmount.provision|currency}
        </div>

    {/block}
</div>
{/if}