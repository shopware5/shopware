
{if $sLiveshoppingData.valid_to_ts}
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
		<div class="top">
			Angebot endet in:
			
			{* Time left *}
			<strong class="time_left">
				<span class="live{$uniquekey}{$sLiveshoppingData.ordernumber}_days_doubledigit">00</span>:<span class="live{$uniquekey}{$sLiveshoppingData.ordernumber}_hours_doubledigit">00</span>:<span class="live{$uniquekey}{$sLiveshoppingData.ordernumber}_min_doubledigit">00</span>:<span class="live{$uniquekey}{$sLiveshoppingData.ordernumber}_sec_doubledigit">00</span>
			</strong>
		</div>
		
		{* Wrapper *}
		<div class={if $sLiveshoppingData.max_quantity_enable}"quantity" style="top:{$instockTopValue}px;"{else}"outer"{/if}>
			<div class="{if $sLiveshoppingData.typeID == 2}liveprice_container_down{elseif $sLiveshoppingData.typeID == 3}liveprice_container_up{/if}">
		
				{* Start price *}
				<div class="top">
					<div class="price_start">{s name='LiveCountdownStartPrice'}{/s}:&nbsp;{$sLiveshoppingData.startprice|currency}*</div>
				</div>
				
				{* Actual price *}
				<div class="middle">
					<p>{s name='LiveCountdownCurrentPrice'}{/s}:</p>
					<strong class="live{$uniquekey}{$sLiveshoppingData.ordernumber}_display_price">
						{$sLiveshoppingData.price|number_format:2:',': '.'} 
					</strong>
					<strong>{config name=sCURRENCYHTML}*</strong>
					
					<div class="bar_time">
						<div class="live{$uniquekey}{$sLiveshoppingData.ordernumber}_secbar_process">&nbsp;</div>
					</div>
				</div>
				
				{* You save... *}
				<div class="bottom">
					{if $sLiveshoppingData.typeID == 2}
						{s name='LiveCountdownPriceFails'}{/s} {$sLiveshoppingData.minPrice|currency}* / {s name='LiveCountdownMinutes'}{/s}
					{elseif $sLiveshoppingData.typeID == 3}
						{s name='LiveCountdownPriceRising'}{/s} {$sLiveshoppingData.minPrice|currency}* / {s name='LiveCountdownMinutes'}{/s}
					{/if}
				</div>
				
				{* Quantity display *}
				{if $sLiveshoppingData.max_quantity_enable}
				
					<div class="right">
						{s name='LiveCountdownRemaining'}{/s}
					 	<strong class="quantity">{$sLiveshoppingData.max_quantity}</strong>
					 	{s name='LiveCountdownRemainingPieces'}{/s}
					</div>
				{/if}
			</div>
		</div>
		
		{* Quantity slider *}
		{if $sLiveshoppingData.max_quantity_enable}
			{assign var="slider0" value=0}
			{assign var="slider100" value=120}
			{assign var="sliderDiff" value=$slider100-$slider0}
			{assign var="instockTotal" value=$sLiveshoppingData.max_quantity+$sLiveshoppingData.sells}
			{assign var="sliderOnePro" value=$sliderDiff/100}
			{if $instockTotal}
				{assign var="instockPro" value=$sLiveshoppingData.max_quantity * 100 / $instockTotal}
			{else}
				{$instockPro=0}
			{/if}
			{assign var="instockTopValue" value=$sliderOnePro*$instockPro}
			{assign var="instockTopValue" value=$slider100-$instockTopValue}
			<div class="liveshopping_slider" style="top:-{$instockTopValue}px">
				&nbsp;
			</div>
		{/if}
	</div>
{/if}
