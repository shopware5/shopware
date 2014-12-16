{* Delivery informations *}
{block name='frontend_widgets_delivery_infos'}
	<div class="delivery_container">
	{if $sArticle.shippingfree}
	    <p class="shipping_free">
	    	<strong>{se name="DetailDataInfoShippingfree"}{/se}</strong>
	    </p>
	{/if}
	{if isset($sArticle.active)&&!$sArticle.active}
		<div class="status4">&nbsp;</div>
		<p class="deliverable2">
			{se name="DetailDataInfoNotAvailable"}{/se}
		</p>
    {elseif $sArticle.sReleaseDate && $sArticle.sReleaseDate|date_format:"%Y%m%d" > $smarty.now|date_format:"%Y%m%d"}
		<div class="status0">&nbsp;</div>
	    <p class="deliverable2">
	    	{se name="DetailDataInfoShipping"}{/se} {$sArticle.sReleaseDate|date:'date_long'}
	    </p>
	{elseif $sArticle.esd}
		<div class="status2">&nbsp;</div>
	    <p class="deliverable1">
	    	{se name="DetailDataInfoInstantDownload"}{/se}
	    </p>
	{elseif {config name="instockinfo"} && $sArticle.modus == 0 && $sArticle.instock > 0 && $sArticle.quantity > $sArticle.instock}
		<div class="status3">&nbsp;</div>
		<p class="deliverable2">
			{s name="DetailDataInfoPartialStock"}{/s}
		</p>
	{elseif $sArticle.instock > 0}
		<div class="status2">&nbsp;</div>
	    <p class="deliverable1">
	    	{s name="DetailDataInfoInstock"}{/s}
	    </p>
	{elseif $sArticle.shippingtime}
		<div class="status0">&nbsp;</div>
	    <p class="deliverable2">
	    	{se name="DetailDataShippingtime"}{/se} {$sArticle.shippingtime} {se name="DetailDataShippingDays"}{/se}
	    </p>
	{else}
		<div class="status4">&nbsp;</div>
	    <p class="deliverable3">
            {se name="DetailDataNotAvailable"}{config name=notavailable}{/se}
	    </p>
	{/if}
	</div>
{/block}
