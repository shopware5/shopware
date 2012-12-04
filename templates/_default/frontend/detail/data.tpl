{block name="frontend_detail_data"}

	{* Caching instock status *}
	{if !$sView}
		<input id='instock_{$sArticle.ordernumber}'type='hidden' value='{$sArticle.instock}' /> 
	{/if}

	{if $sArticle.sBlockPrices && (!$sArticle.sConfigurator || $sArticle.pricegroupActive) && $sArticle.sConfiguratorSettings.type!=2}
		{foreach from=$sArticle.sBlockPrices item=row key=key} 
			{if $row.from=="1"} 
				<input id='price_{$sArticle.ordernumber}'type='hidden' value='{$row.price|replace:",":"."}' /> 
			{/if} 
		{/foreach} 
	{else}
		{if !$sView}
			<input id='price_{$sArticle.ordernumber}' type='hidden' value='{$sArticle.price|replace:".":""|replace:",":"."}' />
		{/if}
	{/if} 
	
	{* Order number *}
	{if $sArticle.ordernumber} 
		{block name='frontend_detail_data_ordernumber'}
			<p>{se name="DetailDataId"}{/se} {$sArticle.ordernumber}</p>
		{/block}
	{/if}
	
	{* Attributes fields *}
	{block name='frontend_detail_data_attributes'}
		{if $sArticle.attr1} 
			<p>{$sArticle.attr1}</p>
		{/if}
		{if $sArticle.attr2} 
			<p>{$sArticle.attr2}</p>
		{/if}
	{/block}
		   
	{block name="frontend_detail_data_delivery"}
		{* Delivery informations *}
		{include file="frontend/plugins/index/delivery_informations.tpl" sArticle=$sArticle}
	{/block}

	{if !$sArticle.liveshoppingData.valid_to_ts}
		{* Graduated prices *}
        {if $sArticle.sBlockPrices && ($sArticle.sConfiguratorSettings.type!=2 || $sArticle.pricegroupActive) && !$sArticle.liveshoppingData.valid_to_ts}

            {* Include block prices *}
            {block name="frontend_detail_data_block_price_include"}
                {include file="frontend/detail/block_price.tpl" sArticle=$sArticle}
            {/block}

            {* Article price *}
            {block name='frontend_detail_data_price_info'}
                <p class="modal_open">
                    {s namespace="frontend/detail/data" name="DetailDataPriceInfo"}{/s}
                </p>
            {/block}
		{else}
            {if $sArticle.sConfiguratorSettings.type!=2}
			{* Pseudo price *}
			<div class='article_details_bottom'>
				<div {if $sArticle.pseudoprice} class='article_details_price2'>{else} class='article_details_price'>{/if}
					{block name='frontend_detail_data_pseudo_price'}
					{if $sArticle.pseudoprice}
					{* if $sArticle.sVariants || $sArticle.priceStartingFrom*}
					<div class="PseudoPrice{if $sArticle.sVariants} displaynone{/if}">
		            	<em>{s name="reducedPrice" namespace="frontend/listing/box_article"}{/s} {$sArticle.pseudoprice|currency} {s name="Star" namespace="frontend/listing/box_article"}{/s}</em>
		            	{if $sArticle.pseudopricePercent.float}
		            		<span>
		            			({$sArticle.pseudopricePercent.float} % {se name="DetailDataInfoSavePercent"}{/se})
		            		</span>
		            	{/if}
		            </div>
		          	{*/if*}
		            {/if}
		            {/block}
		            
		          	{* Article price configurator *}
		            {block name='frontend_detail_data_price_configurator'}
					<strong {if $sArticle.priceStartingFrom && $sView} class="starting_price"{/if}>
						{if $sArticle.priceStartingFrom && !$sArticle.sConfigurator && $sView}
							<span id="DetailDataInfoFrom">{se name="DetailDataInfoFrom"}{/se}</span>
							{$sArticle.priceStartingFrom|currency} {s name="Star" namespace="frontend/listing/box_article"}{/s}
						{else}
							{$sArticle.price|currency} {s name="Star" namespace="frontend/listing/box_article"}{/s}
						{/if}
					</strong>
					{/block}
				</div>
				
				{* Article price *}
				{block name='frontend_detail_data_price_info'}
				<p class="tax_attention modal_open">
					{s name="DetailDataPriceInfo"}{/s}
				</p>
				{/block}
			</div>
		    {/if}
		{/if}
		{if $sArticle.purchaseunit}
				{* Article price *}
				{block name='frontend_detail_data_price'}
					<hr class="space" />
					<div class='article_details_price_unit'>
					<strong>
						<span>
							{se name="DetailDataInfoContent"}{/se} {$sArticle.purchaseunit} {$sArticle.sUnit.description}
						</span>
						
						<br />
						{if $sArticle.purchaseunit != $sArticle.referenceunit}
							<span class="smallsize">
			 				{if $sArticle.referenceunit}
			 					{se name="DetailDataInfoBaseprice"}{/se} {$sArticle.referenceunit} {$sArticle.sUnit.description} = {$sArticle.referenceprice|currency} {s name="Star" namespace="frontend/listing/box_article"}{/s}
			 				{/if}
			 				</span>
						{/if}
					</strong>
					</div>
				{/block}
		{/if}
	{/if}
	 
	{block name="frontend_detail_data_liveshopping"}
		{* Liveshopping *}
		{if $sArticle.liveshoppingData.valid_to_ts}
			{if $sArticle.liveshoppingData.typeID == 2 || $sArticle.liveshoppingData.typeID == 3}
				{include file="frontend/detail/liveshopping/detail_countdown.tpl" sLiveshoppingData=$sArticle.liveshoppingData}
			{else}
				{include file="frontend/detail/liveshopping/detail.tpl" sLiveshoppingData=$sArticle.liveshoppingData sArticlePseudoprice=$sArticle.pseudoprice}
			{/if}
		{/if}
	{/block}
{/block}