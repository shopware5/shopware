{* Delivery informations *}
{block name='frontend_widgets_delivery_infos'}
	<div class="product--delivery">
		{if $sArticle.shippingfree}
			<p class="delivery--shipping-free">
				<strong>{s name="DetailDataInfoShippingfree"}{/s}</strong>
			</p>
		{/if}
		{if isset($sArticle.active )&& !$sArticle.active}
            <link itemprop="availability" href="http://schema.org/LimitedAvailability" />
			<div class="delivery--status-icon delivery--status-not-available">&nbsp;</div>
			<p class="delivery--text  delivery--text-not-available">
				{s name="DetailDataInfoNotAvailable"}{/s}
			</p>
		{elseif $sArticle.sReleaseDate && $sArticle.sReleaseDate|date_format:"%Y%m%d" > $smarty.now|date_format:"%Y%m%d"}
            <link itemprop="availability" href="http://schema.org/PreOrder" />
			<div class="delivery--status-icon delivery--more-is-coming">&nbsp;</div>
			<p class="delivery--text delivery--more-is-coming">
				{s name="DetailDataInfoShipping"}{/s} {$sArticle.sReleaseDate|date:'date_long'}
			</p>
		{elseif $sArticle.esd}
            <link itemprop="availability" href="http://schema.org/InStock" />
			<div class="delivery--status-icon delivery--status-available">&nbsp;</div>
			<p class="delivery--text delivery--text-available">
				{s name="DetailDataInfoInstantDownload"}{/s}
			</p>
		{elseif $sArticle.instock > 0}
            <link itemprop="availability" href="http://schema.org/InStock" />
			<div class="delivery--status-icon delivery--status-available">&nbsp;</div>
			<p class="delivery--text delivery--text-available">
				{s name="DetailDataInfoInstock"}{/s}
			</p>
		{elseif $sArticle.shippingtime}
            <link itemprop="availability" href="http://schema.org/LimitedAvailability" />
			<div class="delivery--status-icon delivery--status-more-is-coming">&nbsp;</div>
			<p class="delivery--text delivery--text-more-is-coming">
				{s name="DetailDataShippingtime"}{/s} {$sArticle.shippingtime} {s name="DetailDataShippingDays"}{/s}
			</p>
		{else}
            <link itemprop="availability" href="http://schema.org/LimitedAvailability" />
			<div class="delivery--status-icon delivery--status-available">&nbsp;</div>
			<p class="delivery--text delivery--text-not-available">
				{s name="DetailDataNotAvailable"}{config name=notavailable}{/s}
			</p>
		{/if}
	</div>
{/block}
