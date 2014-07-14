
{if $sLiveshoppingData.valid_to_ts}
	{assign var=uniquekey value=$smarty.now|uniqid}
	
	
	<div class="liveshopping_container">
	
		{* Needed informations *}
		<input type="hidden" class="valid_to_ts" value="{$sLiveshoppingData.valid_to_ts}" />
		<input type="hidden" class="uniquekey" value="{$uniquekey}" />
		<input type="hidden" class="max_quantity_enable" value="{$sLiveshoppingData.max_quantity_enable}" />
		<input type="hidden" class="max_quantity" value="{$sLiveshoppingData.max_quantity}" />
		<input type="hidden" class="sells" value="{$sLiveshoppingData.sells}" />
		<input type="hidden" class="typeID" value="{$sLiveshoppingData.typeID}" />
		<input type="hidden" class="price" value="{$sLiveshoppingData.price}" />
		<input type="hidden" class="minPrice" value="{$sLiveshoppingData.minPrice}" />
		
		{if $sLiveshoppingData.sLiveStints}
			<input type="hidden" value="{foreach from=$sLiveshoppingData.sLiveStints item=stints name=livestints}{$stints}{if !$smarty.foreach.livestints.last};{/if}{/foreach}" name="stints" class="stints" />
		{/if}
		
		{* Left - Image *}
		<div class="liveshopping_left">
		
			{* Image with link *}
			<div class="grid_6">
				<a href="{$sLiveshoppingData.sDetails.linkDetails}" title="{$sLiveshoppingData.sDetails.articleName}" style="{if $sLiveshoppingData.sDetails.image.src.4 != ''}background:url({$sLiveshoppingData.sDetails.image.src.4}) no-repeat center center;{/if}" class="image">
					{$sLiveshoppingData.sDetails.articleName}
				</a>
			</div>
		</div>	
		
		<div class="liveshopping_middle">
		
			{* Native price *}
			<div class="top">
				{s name='LiveCategoryOffersEnds'}Angebot endet in:{/s}
				
				{* Time left *}
				<strong class="time_left">
					<span class="live{$uniquekey}{$sLiveshoppingData.ordernumber}_days_doubledigit">00</span>:<span class="live{$uniquekey}{$sLiveshoppingData.ordernumber}_hours_doubledigit">00</span>:<span class="live{$uniquekey}{$sLiveshoppingData.ordernumber}_min_doubledigit">00</span>:<span class="live{$uniquekey}{$sLiveshoppingData.ordernumber}_sec_doubledigit">00</span>
				</strong>
			</div>
			
			{* Wrapper *}
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
			{/if}
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
		</div>
		
		{* Quantity slider *}
		{if $sLiveshoppingData.max_quantity_enable}
			<div class="liveshopping_slider">
				&nbsp;
			</div>
		{/if}
		
		<hr class="clear space" />
		
		{* Bottom *}
		<div class="liveshopping_bottom">
			
			{* Articlename *}
			<h3 class="headline">
				<a href="{$sLiveshoppingData.sDetails.linkDetails}" title="{$sLiveshoppingData.sDetails.articleName}">
					{$sLiveshoppingData.sDetails.articleName}
				</a>
			</h3>
			
			{* Description *}
			<p class="description">
				{$sLiveshoppingData.sDetails.description_long|strip_tags|truncate:200}
			</p>
			
			{* Basket buttons *}
			<div class="actions">
				<form method="get" action="{url controller='checkout' action='addArticle'}">
					{if !$sLiveshoppingData.sDetails.sConfigurator && !$sLiveshoppingData.sDetails.sVariantArticle}
					<input type="submit" class="button-right small" value="In den Warenkorb" name="In den Warenkorb" title="{$sLiveshoppingData.sDetails.articleName} in den Warenkorb legen" />
					{/if}
					<input type="hidden" name="sAdd" class="ordernumber" value="{$sLiveshoppingData.ordernumber}" />
				</form>
			</div>
		</div>
		
	</div>
{/if}
