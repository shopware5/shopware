{block name='frontend_bonus_system_point'}
<div class="table_row{if $lastitem} lastrow{/if}">
	{block name='frontend_bonus_system_point_row'}
	<div class="grid_2">
		{$bonusPosition.ordertime|date_format:"%d.%m.%Y"}
	</div>

	<div class="grid_3 bold">
		{$bonusPosition.ordernumber}
	</div>

	<div class="grid_4">
        {if $bonusPosition.approval}
            {s namespace="frontend/bonus_system" name="StatusApproval"}<span style="color:green;">Freigegeben</span>{/s}
        {else}
            {s namespace="frontend/bonus_system" name="StatusWaitForApproval"}<span style="color:red;">Warte auf Freigabe</span>{/s}
        {/if}
	</div>

	<div class="grid_2">
		{$bonusPosition.amount|number_format:2:',':'.'}
	</div>

	<div class="grid_2 textright">
		<span style="color:red;">-{$bonusPosition.spending}</span>
	</div>
	<div class="grid_2 textright">
		<span style="color:green;">+{$bonusPosition.earning}</span>
	</div>
	{/block}
</div>
{if $lastitem}
    {block name='frontend_bonus_system_point_sumrow'}
    <div class="table_row lastrow" style="min-height:20px;">
        <div class="grid_9">&nbsp;</div>
        <div class="grid_4 textright"><b>{s namespace="frontend/bonus_system" name="BonusPointSum"}Summe:{/s}</b></div>
		<div class="grid_2 textright"><b>{if $sBonusSystem.points.user}{$sBonusSystem.points.user}{else}0{/if}</b></var></div>
    </div>
    {/block}
{/if}
{/block}