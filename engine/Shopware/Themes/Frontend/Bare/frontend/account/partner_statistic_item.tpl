<div class="panel--tr">

	{block name='frontend_account_partner_statistic_item_overview_row'}
	<div class="panel--td column--date">
        {$partnerOrder.orderTime|date:datetime}
    </div>
    <div class="panel--td column--id is--bold">
        {$partnerOrder.number}
    </div>
    <div class="panel--td column--price">
        {$partnerOrder.netTurnOver|currency}
	</div>
    <div class="panel--td column--total">
        {$partnerOrder.provision|currency}
    </div>

	{/block}
</div>

{if $lastitem}
<div class="panel--tr is--odd is--bold">
    {block name='frontend_account_partner_statistic_item_overview_row'}

        <div class="panel--td column--item-sum column--price">
            <div class="column--label">
                {se name="PartnerStatisticItemSum"}{/se}
            </div>
            <div class="column--value">
                {$sTotalPartnerAmount.netTurnOver|currency}
            </div>
        </div>

        <div class="panel--td column--total">
            <div class="column--label">
                {se name="PartnerStatisticColumnProvision" namespace="frontend/account/partner_statistic"}{/se}
            </div>
            <div class="column--value">
                {$sTotalPartnerAmount.provision|currency}
            </div>
        </div>
    {/block}
</div>
{/if}