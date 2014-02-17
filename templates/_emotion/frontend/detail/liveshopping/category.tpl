
{if $sLiveshoppingData.valid_to_ts}

	<!-- <pre>{$sLiveshoppingData|print_r}</pre> -->
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
				<a href="{$sLiveshoppingData.sDetails.linkDetails}" title="{$sLiveshoppingData.sDetails.articleName}" style="{if $sLiveshoppingData.sDetails.image.src.4}background:url({$sLiveshoppingData.sDetails.image.src.4}) no-repeat center center;{/if}" class="image">
					{$sLiveshoppingData.sDetails.articleName}
				</a>
			</div>
		</div>
		
		{* Right *}
		<div class="liveshopping_right">
		
			{* Native price *}
			<p class="center">
				{s name='LiveCategoryPreviousPrice'}Ursprünglicher Preis:{/s} <strong class="price">{$sLiveshoppingData.sDetails.pseudoprice|currency}*</strong>
			</p>
			
			{* You save ... *}
			<p class="discount">
				{s name='LiveCategorySavingPercent'}Sie sparen:{/s} {$sLiveshoppingData.sDetails.pseudopricePercent.float|number} %
			</p>
			
			
			{* Liveshopping price display *}
			<div class="live_price_normal">
				<div class="top">
					{s name='LiveCategoryOffersEnds'}Angebot endet in:{/s}
					<strong class="time_left">
						<span class="live{$uniquekey}{$sLiveshoppingData.ordernumber}_days_doubledigit">00</span>:<span class="live{$uniquekey}{$sLiveshoppingData.ordernumber}_hours_doubledigit">00</span>:<span class="live{$uniquekey}{$sLiveshoppingData.ordernumber}_min_doubledigit">00</span>:<span class="live{$uniquekey}{$sLiveshoppingData.ordernumber}_sec_doubledigit">00</span>
					</strong>
				</div>
				<div class="bottom">
					<p>{s name='LiveCategoryCurrentPrice'}Aktueller Preis:{/s}</p>
					
					<strong class="price">{$sLiveshoppingData.price|currency}*</strong>
				</div>
			</div>
			
			{* Including bar chart *}
			{include file="frontend/detail/liveshopping/ticker/timeline.tpl" sLiveshoppingData=$sLiveshoppingData}
		</div>
		
		<div class="clear space">&nbsp;</div>
		
		
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
