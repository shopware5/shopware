{block name='frontend_index_header_javascript' append}
	<script type="text/javascript">
	//<![CDATA[
	jQuery(document).ready(function($) {
		{if $new_active}
			$('.new-in-store-slider').ajaxSlider('ajax', {
				'url': unescape('{"{url controller=sliders action=new category=$sCategoryContent.id}"|escape:url}'),
				'title': '{s name="IndexNewArticlesSlider"}Neu im Sortiment:{/s}',
				'headline': true,
				'navigation': false,
				'scrollSpeed': 800,
				'rotate': false,
				'width':628,
				'containerCSS': { 'marginTop': '0px', 'marginBottom': '15px' }
			});
		{/if}
		{if $bought_active}
			$('.similar-article-slider').ajaxSlider('ajax', {
				'url': unescape('{"{url controller=sliders action=similaryViewed category=$sCategoryContent.id}"|escape:url}'),
				'title': '{s name="IndexSimilaryArticlesSlider"}Ähnliche Artikel wie die, die Sie sich angesehen haben:{/s}',
				'headline': true,
				'navigation': false,
				'scrollSpeed': 800,
				'rotate': false,
				'width':628,
				'containerCSS': { 'marginTop': '12px', 'marginBottom': '15px' }
			});
		{/if}
		{if $banner_active}
			$('.slider_banner').ajaxSlider('locale', {
				'width':630,
				'height':386,
				'scrollWidth': 630,
				'containerClass': 'bannerSlider',
				'outer': false,
				'headline': false,
				'rotate': true,
				'scrollSpeed': 600,
				'containerCSS': { 'marginBottom': '15px'}
			});
		{/if}
		{if $supplier_active}
			$('.supplier_slider').ajaxSlider('locale', {
				'height': 130,
				'width': 628,
				'scrollWidth': 576,
				'title': '{s name="IndexSupplierSlider"}Unsere Top Marken{/s}',
				'titleClass': 'headingbox_nobg',
				'headline': true,
				'navigation': false,
				'showNumbers': false,
				'containerCSS': {
					'marginTop': '20px',
					'marginBottom': '20px'
				}
			});	
		{/if}
	});
	//]]>
	</script>
{/block}


{block name="frontend_home_index_liveshopping" append}{/block}


{block name="frontend_home_index_promotions" append}
{if $new_active}
	<div class="slider similar-article-slider"></div>
{/if}

{if $bought_active}
    <div class="slider new-in-store-slider"></div>
{/if}
{/block} 

{block name="frontend_listing_banner"}
	{if $banners && !$sLiveShopping && $banner_active}
	<div class="slider_banner">
		{foreach from=$banners item=banner}
			{if !$banner.liveshoppingID}
				<div class="slide">
					<a href="{$banner.link}" class="banner" {if $banner.link_target}target="{$banner.link_target}"{/if} title="{$banner.description}">
				    	<img src="{$banner.img}" alt="{$banner.description}" title="{$banner.description}" />
			    	</a>
				</div>
			{else}
				{if $banner.liveshoppingData}
					<div class="slide">
						{include file="frontend/listing/box_liveshopping.tpl" liveArt=$banner.liveshoppingData}
					</div>
				{/if}
			{/if}
		{/foreach}
	</div>
	{else}
		{$smarty.block.parent}
	{/if}
{/block}

{block name="frontend_home_index_blog" prepend}
	{if $supplier_active}
		<div class="supplier_slider">
		{foreach from=$suppliers item=slide}
			<div class="slide">
				{foreach from=$slide item=supplier}
				<div {if $supplier.image}class="logo"{else}class="text"{/if}>
					<a href="{$supplier.link}" title="{$supplier.name}"{if $supplier.image} style="background-image:url({$supplier.image});"{/if}>
						{if $supplier.image}
							<img src="{$supplier.image}" alt="{$supplier.name}" />
						{else}
							{$supplier.name}
						{/if}
					</a>
				</div>	
				{/foreach}
			</div>
		{/foreach}
		</div>
	{/if}
{/block}