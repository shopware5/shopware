
{if $sRelatedArticles}
	<div class="relatedbox_container{if $sArticle.sVariant} displaynone{/if}">
		
		{* Needed informations *}
		<div class="bundle_related_articles displaynone">{$sRelatedArticles|json_encode|escape:"html":null:true}</div>
		<div class="bundle_article_details displaynone">{$sArticle|json_encode|escape:"html":null:true}</div>
		
		{* Heading *}
		<h2 class="heading">{s name="BundleHeader"}{/s}:</h2>
			
			{* Related set *}
			<div class="relatedbox">
				
				{* Main image *}
				{if $sArticle.image.src.1}
					<a href="#" title="{$sArticle.articleName}" class="image" style="background-image:url({$sArticle.image.src.1})" id="related_main_image">
						{$sArticle.articleName}
					</a>
				{else}
					<a href="#" title="{$sArticle.articleName}" class="image" style="background-image:url({link file='frontend/_resources/images/no_picture.jpg'})" id="related_main_image">
						{$sArticle.articleName}
					</a>
				{/if}
				
				{* Looping through the articles in the cross selling bundles *}
				{foreach from=$sRelatedArticles item=relatedArticle}
				
					{* Plus symbol *}
					<span id="related_{$relatedArticle.ordernumber}_plus" class="plus">+</span>
					
					{* Article image *}
					<div class="image_box" id="related_{$relatedArticle.ordernumber}_image">
						{if $relatedArticle.image.src[1]}
							<a href="{$relatedArticle.linkDetails}" 
								class="image" 
								title="{$relatedArticle.articleName}" 
								style="background-image: url({$relatedArticle.image.src[1]});">
								{$relatedArticle.articleName}
							</a>
						{else}
							<a href="{$relatedArticle.linkDetails}" 
								class="image" 
								title="{$relatedArticle.articleName}" 
								style="background-image: url({link file='frontend/_resources/images/no_picture.jpg'});">
								{$relatedArticle.articleName}
							</a>
						{/if}
					</div>

				{/foreach}
				
				<div class="clear">&nbsp;</div>
				
				<form method="get" action="{url controller='checkout' action='addArticle'}">
					<div class="price_container">
						<h3 class="heading">{s name="BundleInfoPriceForAll"}{/s}:</h3>
						
						{* Price *}
						<div class="price">
							<span id='price_relatedbundle'></span>
							<span>{config name=CURRENCYHTML}</span>
							{s name="Star" namespace="frontend/listing/box_article"}{/s}
						</div>
						
						{* Basket button *}
						<div class="action">
							<input type='hidden' name='sAddRelatedArticles' value='basket' />
							<input id='related_main_ordernumber' type="hidden" name='sAdd' value='{$sArticle.ordernumber}' />
							<input type='hidden' id='sRelatedOrdernumbers' name='sAddAccessories' value='' />
							
							<input class="button-right small_right" type="submit" 
								title="{$sArticle.articleName} {s name="BundleActionAdd"}{/s}" 
								name="{s name="BundleActionAdd"}{/s}" value="{s name="BundleActionAdd"}{/s}" />	
						</div>
					</div>
				</form>
			</div>
		
		<div class="related_checker">
			{foreach from=$sRelatedArticles item=relatedArticle}
			
				{* Needed informations *}
				<input class="relatedOrdernumber" type="hidden" value="{$relatedArticle.ordernumber}" />
				<input type="hidden" id="{$relatedArticle.ordernumber}_checked" />
				<input type="hidden" id="{$relatedArticle.ordernumber}_price" value="{$relatedArticle.price|replace:',':'.'}"/>
				
				<p>
					{* Checkbox *}
					<input id="{$relatedArticle.ordernumber}_related_checkbox" type="checkbox" class="left" checked="checked" onclick="$.refreshRelatedArticle()" />
					
					{* Article link *}
					<a href="{$relatedArticle.linkDetails}" title="{$relatedArticle.articleName}">
						{$relatedArticle.articleName}
					</a>
					
					{* Description *}
					{if $relatedArticle.description}
						{$relatedArticle.description|truncate:60}
					{else}
						{$relatedArticle.description_long|truncate:60}
					{/if}
				</p>
			{/foreach}
		</div>
		<input type="hidden" id="selected_articel_price" value='{$sArticle.price|replace:',':'.'}' />
		<div class="space">&nbsp;</div>
	</div>
{/if}
