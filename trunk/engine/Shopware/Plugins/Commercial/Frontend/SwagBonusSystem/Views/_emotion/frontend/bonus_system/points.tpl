{extends file='parent:frontend/account/index.tpl'}


{* Main content *}
{block name='frontend_index_content'}
	{if $sBonusSystem.settings.bonus_system_active==1}
		{* Orders overview *}
		<div class="grid_16 orders bonus" id="center">
			{if !$bonusOrders}
			{block name="frontend_account_orders_info_empty"}
				<fieldset>
					<div class="notice center bold">
						{s namespace="frontend/bonus_system" name="BonusInfoEmpty"}Sie haben noch keine Punkte gesammelt{/s}
					</div>
				</fieldset>
			{/block}
			{else}
			<h1>{s namespace="frontend/bonus_system" name="MyBonusPointAccount"}Mein Punktekonto{/s}</h1>
			<div class="orderoverview_active">

				<div class="table grid_16">
					{block name="frontend_account_orders_table_head"}
					<div class="table_head">

						<div class="grid_2">
							{s namespace="frontend/bonus_system" name="BonusColumnDate"}Datum{/s}
						</div>

						<div class="grid_3">
							{s namespace="frontend/bonus_system"  name="BonusColumnOrdernumber"}Bestellnummer{/s}
						</div>

						<div class="grid_4">
							{s namespace="frontend/bonus_system"  name="BonusColumnStatus"}Status{/s}
						</div>

						<div class="grid_2">
							{s namespace="frontend/bonus_system" name="BonusColumnAmount"}Betrag{/s}
						</div>

						<div class="grid_2 textright">
							{s namespace="frontend/bonus_system/points" name="Spending"}Ausgegeben{/s}
						</div>

						<div class="grid_2 textright">
							{s namespace="frontend/bonus_system/points" name="Earning"}Gesammelt{/s}
						</div>
					</div>
					{/block}
					{foreach name=bonusitems from=$bonusOrders item=bonusPosition}
						{if $smarty.foreach.bonusitems.last}
							{assign var=lastitem value=1}
						{else}
							{assign var=lastitem value=0}
						{/if}
						{include file="frontend/bonus_system/point_item.tpl" lastitem=$lastitem}
					{/foreach}
				</div>
			</div>
			<div class="space">&nbsp;</div>
			{/if}
		</div>
	{else}
		{block name="frontend_account_orders_info_empty"}
			<fieldset>
				<div class="notice center bold">
					{s namespace="frontend/bonus_system" name="BonusSystemInactive"}Das Bonus System ist auf diesem System nicht aktiv!{/s}
				</div>
			</fieldset>
		{/block}
	{/if}
{/block}
