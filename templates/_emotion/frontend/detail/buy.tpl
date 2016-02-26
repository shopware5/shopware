{block name="frontend_detail_buy"}
	<form name="sAddToBasket" method="post" action="{url controller=checkout action=addArticle}" class="basketform">
		{if $sArticle.sConfigurator&&$sArticle.sConfiguratorSettings.type==3}
			{foreach from=$sArticle.sConfigurator item=group}
				<input type="hidden" name="group[{$group.groupID}]" value="{$group.selected_value}"/>
			{/foreach}
		{/if}

		<input type="hidden" name="sActionIdentifier" value="{$sUniqueRand}"/>
		<input type="hidden" name="sAddAccessories" id="sAddAccessories" value=""/>

		{if !$sArticle.sConfigurator && $sArticle.sVariants}
			{block name='frontend_detail_buy_variant'}
				<select name="sAdd" id="sAdd" class="variant">
					<option value="">{s name="DetailBuyValueSelect"}{/s}</option>
					<option value="{$sArticle.ordernumber}">
						{if $sArticle.additionaltext}
							{$sArticle.additionaltext}
						{else}
							{$sArticle.articleName}
						{/if}
					</option>
					{foreach from=$sArticle.sVariants item=variant}
						<option value="{$variant.ordernumber}">
							{if $variant.additionaltext}
								{$variant.additionaltext}
							{else}
								{$variant.ordernumber}
							{/if}
						</option>
					{/foreach}
				</select>
			{/block}
		{else}
			<input type="hidden" name="sAdd" value="{$sArticle.ordernumber}"/>
		{/if}

		{* Article accessories *}
		{if $sArticle.sAccessories}
			{block name='frontend_detail_buy_accessories'}
				<div class="accessory_container">
					{foreach from=$sArticle.sAccessories item=sAccessory}

					{* Group name *}
						<h2 class="headingbox">{$sAccessory.groupname}</h2>
						<div class="accessory_group">


							{* Group description *}
							<p class="groupdescription">
								{$sAccessory.groupdescription}
							</p>

							{foreach from=$sAccessory.childs item=sAccessoryChild}
								<input type="checkbox" class="sValueChanger chkbox" name="sValueChange"
									   id="CHECK{$sAccessoryChild.ordernumber}" value="{$sAccessoryChild.ordernumber}"/>
								<label for="CHECK{$sAccessoryChild.ordernumber}">{$sAccessoryChild.optionname|truncate:35}
									({se name="DetailBuyLabelSurcharge"}{/se}
									: {$sAccessoryChild.price} {$sConfig.sCURRENCYHTML})
								</label>
								<div id="DIV{$sAccessoryChild.ordernumber}" class="accessory_overlay">
									{include file="frontend/detail/accessory.tpl" sArticle=$sAccessoryChild.sArticle}
								</div>
								<div class="clear">&nbsp;</div>
							{/foreach}
						</div>
					{/foreach}
				</div>
				<div class="doublespace">&nbsp;</div>
			{/block}
		{/if}
		{assign var="sCountConfigurator" value=$sArticle.sConfigurator|@count}
		{if (!isset($sArticle.active) || $sArticle.active)}
			{block name='frontend_detail_buy_laststock'}
				{if $sArticle.laststock}
					<div id="detailBuyInfoNotAvailable"{if $sArticle.instock > 0} style="display: none;"{/if}>
						<div class="space">&nbsp;</div>
						<div class="error bold center">
							{s name="DetailBuyInfoNotAvailable"}{/s}
						</div>
					</div>
				{/if}
			{/block}
			{if !$sArticle.laststock || $sArticle.sVariants || $sArticle.instock > 0}
				<div id="detailCartButton"
					 {if $NotifyHideBasket && $sArticle.notification && $sArticle.instock <= 0}style="display: none;"{/if}>
					{block name='frontend_detail_buy_quantity'}
						{if $sArticle.laststock && !$sArticle.sVariants && $sArticle.instock < $sArticle.maxpurchase}
							{assign var=maxQuantity value=$sArticle.instock+1}
						{else}
							{assign var=maxQuantity value=$sArticle.maxpurchase+1}
						{/if}
						<label for="sQuantity">{s name="DetailBuyLabelQuantity"}{/s}:</label>
						<select id="sQuantity" name="sQuantity">
							{section name="i" start=$sArticle.minpurchase loop=$maxQuantity step=$sArticle.purchasesteps}
								<option value="{$smarty.section.i.index}">{$smarty.section.i.index}{if $sArticle.packunit} {$sArticle.packunit}{/if}</option>
							{/section}
						</select>
					{/block}

					<div class="space">&nbsp;</div>

					{* Cart button *}
					{block name='frontend_detail_buy_button'}
						<input type="submit"
							   id="basketButton"{if $sArticle.sConfigurator && !$activeConfiguratorSelection}
							   class="transparent" disabled="disabled"
							   {/if}title="{$sArticle.articleName} {s name="DetailBuyActionAdd"}{/s}"
							   name="{s name="DetailBuyActionAdd"}{/s}" value="{s name="DetailBuyActionAdd"}{/s}"
							   style="{$buy_box_display}"/>
					{/block}

					<div class="space">&nbsp;</div>
				</div>
			{/if}
			<div class="space">&nbsp;</div>
		{/if}
	</form>
{/block}
