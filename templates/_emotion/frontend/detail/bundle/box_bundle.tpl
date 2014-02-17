
{if $sBundles}
	<div class="bundle_container{if $sArtile.sVariant} displaynone{/if}">
		
		{* Needed informations *}
		<div class="bundle_bundles_details displaynone">{$sBundles|json_encode|escape:"html":null:true}</div>
		<div class="bundle_article_details displaynone">{$sArticle|json_encode|escape:"html":null:true}</div>
				
		{* Headline *}
		<h2 class="heading">{s name="BundleHeader"}{/s}:</h2>
		
		{foreach from=$sBundles item=bundle}
			{if $bundle.sBundleArticles}
				
				{* Start form *}
				<form method="get" action="{url controller='checkout' action='addBundle'}">
				
					<div id='bundleset_{$bundle.id}' class="bundleset">
					
						{* Main article *}
						{if {config name=ShowBundleMainArticle}}
						
							{* Setting image *}
							{if $sArticle.image.src.1}
								<a href="#" title="{$sArticle.articleName}" class="image" style="background-image:url({$sArticle.image.src.1})">
									{$sArticle.articleName}
								</a>
							{else}
								<a href="#" title="{$sArticle.articleName}" class="image" style="background-image:url({link file='frontend/_resources/images/no_picture.jpg'})">
									{$sArticle.articleName}
								</a>
							{/if}
							
							{* Plus symbol *}
							<span class="plus">+</span>
							
							{assign var="firstPlus" value=0}
						{/if}
						
						{* Looping through the articles in the current bundle *}
						{foreach from=$bundle.sBundleArticles item=bundleArticle}
							
							{* Plus symbol *}
							{if $firstPlus != 0}
								<span class="plus">+</span>
							{/if}
							
							{* Setting image *}
							{if $bundleArticle.sDetails.image.src[1]}
								<a href="{$bundleArticle.sDetails.linkDetails}" 
								   title="{$bundleArticle.sDetails.articleName}" 
								   class="image" style="background-image:url({$bundleArticle.sDetails.image.src[1]})">
									{$bundleArticle.sDetails.articleName}
								</a>
							{else}
								<a href='{$bundleArticle.sDetails.linkDetails}' 
								   title="{$bundleArticle.sDetails.articleName}" 
								   class="bundleImg" 
								   style="background-image: url({link file='frontend/_resources/images/no_picture.jpg'});">
								</a>
							{/if}
							
							{assign var="firstPlus" value=1}
							
						{/foreach}
						
						<div class="clear">&nbsp;</div>
					
						<div class="price_container">
						
							{* Bundle price *}
							<div class="price">
								<h3 class="heading">{s name="BundleInfoPriceForAll"}{/s}:</h3>
								<span id='price_bundle_{$bundle.id}'></span> {config name=CURRENCYHTML} {s name="Star" namespace="frontend/listing/box_article"}{/s}
							</div>
							
							{* Basket button *}
							<div class="action">
								<input type='hidden' name='sAddBundle' value='' />
								<input type='hidden' name='sBID' value='{$bundle.id}' />
								
								<input class="button-right small_right" type="submit" 
									title="{$sArticle.articleName} {s name='BundleActionAdd'}{/s}" 
									name="{s name='BundleActionAdd'}{/s}" value="{s name='BundleActionAdd'}{/s}" />
							</div>
							
							{* Discount price *}
							<div class="discount">
								({s name="BundleInfoPriceInstead"}{/s}
								<span id='price_rabAbs_{$bundle.id}'></span> {config name=CURRENCYHTML} {s name="Star" namespace="frontend/listing/box_article"}{/s} - <span id='price_rabPro_{$bundle.id}'></span>
								{s name="BundleInfoPercent"}{/s})
							</div>
						</div>
						<hr class="clear" />
					</div>
					
					{* Included articles *}
					<div class="names">
						<h4>{$sArticle.articleName}</h4>
						
						<ul>
							{foreach from=$bundle.sBundleArticles item=bundleArticle}
							<li>
								+ <a href="{$bundleArticle.sDetails.linkDetails}" title="{$bundleArticle.sDetails.articleName}">
									{$bundleArticle.sDetails.articleName}
								</a>
							</li>
							{/foreach}
						</ul>
					</div>
				</form>
			{/if}
		{/foreach}
		<div class="space">&nbsp;</div>
	</div>
{/if}
