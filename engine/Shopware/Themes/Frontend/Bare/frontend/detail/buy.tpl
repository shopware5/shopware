{block name="frontend_detail_buy"}
	<form name="sAddToBasket" method="post" action="{url controller=checkout action=addArticle}" class="buybox--form">
		{if $sArticle.sConfigurator&&$sArticle.sConfiguratorSettings.type==3}
			{foreach $sArticle.sConfigurator as $group}
				<input type="hidden" name="group[{$group.groupID}]" value="{$group.selected_value}"/>
			{/foreach}
		{/if}

		{* Configurator Table *}
		{if $sArticle.sConfigurator && $sArticle.sConfiguratorSettings.type==2}
			{block name='frontend_detail_buy_config_table'}
				{include file="frontend/detail/config_table.tpl"}
			{/block}
		{/if}

		<input type="hidden" name="sActionIdentifier" value="{$sUniqueRand}"/>
		<input type="hidden" name="sAddAccessories" id="sAddAccessories" value=""/>

		{* @deprecated - Product variants block *}
		{block name='frontend_detail_buy_variant'}{/block}

		{if !$sArticle.sConfigurator || $sArticle.sConfiguratorSettings.type!=2}
			<input type="hidden" name="sAdd" value="{$sArticle.ordernumber}"/>
		{/if}

		{* Article accessories *}
		{if $sArticle.sAccessories}
			{block name='frontend_detail_buy_accessories'}
				<div class="buybox--accessory">
					{foreach $sArticle.sAccessories as $sAccessory}

						{* Group name *}
						<h2 class="accessory--title">{$sAccessory.groupname}</h2>
						<div class="accessory--group">

							{* Group description *}
							<p class="group--description">
								{$sAccessory.groupdescription}
							</p>

							{foreach $sAccessory.childs as $sAccessoryChild}
								<input type="checkbox" class="sValueChanger chkbox" name="sValueChange"
									   id="CHECK{$sAccessoryChild.ordernumber}" value="{$sAccessoryChild.ordernumber}"/>
								<label for="CHECK{$sAccessoryChild.ordernumber}">{$sAccessoryChild.optionname|truncate:35}
									({se name="DetailBuyLabelSurcharge"}{/se}
									: {$sAccessoryChild.price} {$sConfig.sCURRENCYHTML})
								</label>
								<div id="DIV{$sAccessoryChild.ordernumber}" class="accessory--overlay">
									{include file="frontend/detail/accessory.tpl" sArticle=$sAccessoryChild.sArticle}
								</div>
							{/foreach}
						</div>
					{/foreach}
				</div>
			{/block}
		{/if}

		{$sCountConfigurator=$sArticle.sConfigurator|@count}

		{if (!isset($sArticle.active) || $sArticle.active)}
			{block name='frontend_detail_buy_laststock'}
				{if $sArticle.laststock}
					<div id="detailBuyInfoNotAvailable"{if $sArticle.instock > 0} style="display: none;"{/if}>
						<div class="error bold center">
							{s name="DetailBuyInfoNotAvailable"}{/s}
						</div>
					</div>
				{/if}
			{/block}

			{if !$sArticle.laststock || $sArticle.instock>0}

				{block name="frontend_detail_buy_button_container"}
				<div class="buybox--button-container block-group{if $NotifyHideBasket && $sArticle.notification && $sArticle.instock <= 0} is--hidden{/if}">

					{* Quantity selection *}
					{block name='frontend_detail_buy_quantity'}
						<div class="buybox--quantity block">
							{$maxQuantity=$sArticle.maxpurchase+1}
							{if $sArticle.laststock && $sArticle.instock < $sArticle.maxpurchase}
								{$maxQuantity=$sArticle.instock+1}
							{/if}

							<input type="number" name="sQuantity" min="{$sArticle.minpurchase}" max="{$maxQuantity}" data-step="{$sArticle.purchasesteps}" value="{$sArticle.minpurchase}" required="required" aria-required="true">
						</div>
					{/block}

					{* "Buy now" button *}
					{block name="frontend_detail_buy"}
						{if $sArticle.sConfiguratorSettings.type == 1 && !$sArticle.sConfigurator[$sCountConfigurator-1].user_selected}
							<button class="buybox--button block is--transparent" disabled="disabled" aria-disabled="true" name="{s name="DetailBuyActionAdd"}{/s}"{if $buy_box_display} style="{$buy_box_display}"{/if}>
								{s name="DetailBuyActionAdd"}{/s}
							</button>
						{else}
							<button class="buybox--button block" name="{s name="DetailBuyActionAdd"}{/s}"{if $buy_box_display} style="{$buy_box_display}"{/if}>
								{s name="DetailBuyActionAdd"}{/s}
							</button>
						{/if}
					{/block}
				</div>
				{/block}
			{/if}
		{/if}
	</form>
{/block}