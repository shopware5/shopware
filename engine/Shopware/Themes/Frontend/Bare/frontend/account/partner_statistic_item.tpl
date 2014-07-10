<div class="panel--tr">

	{block name='frontend_account_partner_statistic_item_overview_row'}
	<div class="panel--td column--date">
        <div class="column--label">
            {s name="PartnerStatisticColumnDate" namespace="frontend/account/partner_statistic"}{/s}:
        </div>
        <div class="column--value">
            {$partnerOrder.orderTime|date:datetime}
        </div>
    </div>
    <div class="panel--td column--id is--bold">
        <div class="column--label">
            {s name="PartnerStatisticColumnId" namespace="frontend/account/partner_statistic"}{/s}:
        </div>
        <div class="column--value">
            {$partnerOrder.number}
        </div>
    </div>
    <div class="panel--td column--price">
        <div class="column--label">
            {s name="PartnerStatisticColumnNetAmount" namespace="frontend/account/partner_statistic"}{/s}:
        </div>
        <div class="column--value">
            {$partnerOrder.netTurnOver|currency}
        </div>
	</div>

    <div class="panel--td column--total">
        <div class="column--label">
            {s name="PartnerStatisticColumnProvision" namespace="frontend/account/partner_statistic"}{/s}:
        </div>
        <div class="column--value">
            {$partnerOrder.provision|currency}
        </div>
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