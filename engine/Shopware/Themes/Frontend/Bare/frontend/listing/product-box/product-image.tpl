{* Product image - uses the picturefill polyfill for the HTML5 "picture" element *}
<a href="{$sArticle.linkDetails|rewrite:$sArticle.articleName}" title="{$sArticle.articleName|escape:'html'}" class="product--image">
	<span data-picture data-alt="{$sArticle.articleName|escape:'html'}" class="image--element">
		<span class="image--media" data-src="{if isset($sArticle.image.src)}{$sArticle.image.src.4}{else}{link file='frontend/_public/src/img/no-picture.jpg'}{/if}"></span>
		<span class="image--media" data-src="{if isset($sArticle.image.src)}{$sArticle.image.src.4}{else}{link file='frontend/_public/src/img/no-picture.jpg'}{/if}" data-media="(min-width: 48em)"></span>
		<span class="image--media" data-src="{if isset($sArticle.image.src)}{$sArticle.image.src.4}{else}{link file='frontend/_public/src/img/no-picture.jpg'}{/if}" data-media="(min-width: 78.75em)"></span>

		<noscript>
			<img src="{if isset($sArticle.image.src)}{$sArticle.image.src.4}{else}{link file='frontend/_public/src/img/no-picture.jpg'}{/if}" alt="{$sArticle.articleName|escape:'html'}">
		</noscript>
	</span>
</a>