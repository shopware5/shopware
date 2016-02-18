
{if !$sUserData.additional.user.id && {config name=basketShowCalculation}}
<div id="left" class="grid_4 basket first">
	{include file="frontend/checkout/shipping_costs.tpl"}
</div>
{else}
<div id="left" class="grid_4 first">
	{include file="frontend/checkout/confirm_left.tpl"}
</div>
{/if}
