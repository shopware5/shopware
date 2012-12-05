{block name='frontend_index_header_javascript_inline' prepend}
	jQuery(document).ready(function($) {
		{if $new_active}
			$('.slider').ajaxSlider('ajax', {
			'url': '{url controller=recommendation action=new category=$sCategoryContent.id}',
			'title': '{s name="IndexNewArticlesSlider"}Neu im Sortiment:{/s}',
			'headline': true,
			'navigation': false,
			'scrollSpeed': 800,
			'rotate': false,
			'width':628,
			'containerCSS': { 'marginTop': '12px', 'marginBottom': '15px' }
			});
		{/if}
		
		{if $bought_active}
			$('.slider2').ajaxSlider('ajax', {
				'url': '{url controller=recommendation action=similaryViewed category=$sCategoryContent.id}',
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
				'scrollSpeed': 600
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
{/block}


{block name="frontend_listing_index_tagcloud" prepend}
{if $bought_active}
	<div class="slider2"></div>
{/if}
{/block} 


{block name="frontend_listing_index_tagcloud" prepend}
{if $new_active}
	<div class="slider"></div>
{/if}
{/block} 

{block name="frontend_listing_index_banner"}
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
			<div class="slide">
				{include file="frontend/listing/box_liveshopping.tpl" liveArt=$banner.liveshoppingData}
			</div>
		{/if}
		{/foreach}
	</div>
	{else}
		{$smarty.block.parent}
	{/if}
{/block}

{block name="frontend_listing_index_listing" append}
	{if $supplier_active}
		<!-- Supplier slider -->
		<div class="supplier_slider">
		{foreach from=$suppliers item=slide}
			<!-- Slide container -->
			<div class="slide">
				{foreach from=$slide item=supplier}
				<!-- Hersteller Logo -->
				<div {if $supplier.image}class="logo"{else}class="text"{/if}>
					<a href="{$supplier.link}" title="{$supplier.name}" {if $supplier.image}style="background-image:url({$supplier.image});"{/if}>
						{if $supplier.image}
							<img src="{$supplier.image}" alt="{$supplier.name}" />
						{else}
							{$supplier.name}
						{/if}
						
					</a>
				</div>	
				{/foreach}
			</div> <!-- //Slide container -->
		{/foreach}
		</div> <!-- //Supplier slider -->
	{/if}
{/block}