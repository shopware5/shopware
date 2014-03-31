{* Delivery informations *}
{block name='frontend_widgets_delivery_infos'}
	<div class="product--delivery">
		{if $sArticle.shippingfree}
			<p class="delivery--shipping-free">
				<strong>{s name="DetailDataInfoShippingfree"}{/s}</strong>
			</p>
		{/if}
		{if isset($sArticle.active)&&!$sArticle.active}
			<div class="status4">&nbsp;</div>
			<p class="deliverable2">
				{s name="DetailDataInfoNotAvailable"}{/s}
			</p>
		{elseif $sArticle.sReleaseDate && $sArticle.sReleaseDate|date_format:"%Y%m%d" > $smarty.now|date_format:"%Y%m%d"}
			<div class="status0">&nbsp;</div>
			<p class="deliverable2">
				{s name="DetailDataInfoShipping"}{/s} {$sArticle.sReleaseDate|date:'date_long'}
			</p>
		{elseif $sArticle.esd}
			<div class="status2">&nbsp;</div>
			<p class="deliverable1">
				{s name="DetailDataInfoInstantDownload"}{/s}
			</p>
		{elseif $sArticle.instock > 0}
			<div class="status2">&nbsp;</div>
			<p class="deliverable1">
				{s name="DetailDataInfoInstock"}{/s}
			</p>
		{elseif $sArticle.shippingtime}
			<div class="status0">&nbsp;</div>
			<p class="deliverable2">
				{s name="DetailDataShippingtime"}{/s} {$sArticle.shippingtime} {s name="DetailDataShippingDays"}{/s}
			</p>
		{else}
			<div class="status4">&nbsp;</div>
			<p class="deliverable3">
				{s name="DetailDataNotAvailable"}{config name=notavailable}{/s}
			</p>
		{/if}
	</div>
{/block}
