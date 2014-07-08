
{if $sLiveshoppingData.valid_to_ts}
	{assign var=pseudoprice_num value=$sArticlePseudoprice|replace:'.':''|replace:',':'.'|floatval}
	{assign var=price_num value=$sLiveshoppingData.price|floatval}
	{assign var=procent value=100 - ($price_num * 100 / $pseudoprice_num|number_format:2)}
	{assign var=uniquekey value=$smarty.now|uniqid}
		
	<div class="liveshopping_detail">
	
		{* Needed informations *}
		<input type="hidden" class="valid_to_ts" value="{$sLiveshoppingData.valid_to_ts}" />
		<input type="hidden" class="uniquekey" value="{$uniquekey}" />
		<input type="hidden" class="max_quantity_enable" value="{$sLiveshoppingData.max_quantity_enable}" />
		<input type="hidden" class="max_quantity" value="{$sLiveshoppingData.max_quantity}" />
		<input type="hidden" class="sells" value="{$sLiveshoppingData.sells}" />
		<input type="hidden" class="typeID" value="{$sLiveshoppingData.typeID}" />
		<input type="hidden" class="price" value="{$sLiveshoppingData.price}" />
		<input type="hidden" class="minPrice" value="{$sLiveshoppingData.minPrice}" />
		<input type="hidden" class="ordernumber" value="{$sArticle.ordernumber}" />

		{* Native price *}
			<p class="center">
				Urspr&uuml;nglicher Preis: <strong class="price">{$sArticlePseudoprice|currency}*</strong>
			</p>
			
			{* You save ... *}
			<p class="discount">
				Sie sparen: {$procent|number} %
			</p>
			
			
			{* Liveshopping price display *}
			<div class="live_price_normal">
				<div class="top">
					Angebot endet in:
					<strong class="time_left">
						<span class="live{$uniquekey}{$sLiveshoppingData.ordernumber}_days_doubledigit">00</span>:<span class="live{$uniquekey}{$sLiveshoppingData.ordernumber}_hours_doubledigit">00</span>:<span class="live{$uniquekey}{$sLiveshoppingData.ordernumber}_min_doubledigit">00</span>:<span class="live{$uniquekey}{$sLiveshoppingData.ordernumber}_sec_doubledigit">00</span>
					</strong>
				</div>
				<div class="bottom">
					<p>Aktueller Preis:</p>
					
					<strong class="price">{$sLiveshoppingData.price|currency}*</strong>
				</div>
			</div>
			
			{* Including bar chart *}
			{include file="frontend/detail/liveshopping/ticker/timeline.tpl" sLiveshoppingData=$sLiveshoppingData}
	</div>
{/if}
