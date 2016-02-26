
{if $sArticle.sRelatedArticles && !$sArticle.crossbundlelook}
    <div id="related">
        <h2>{se name='DetailRelatedHeader'}{/se}</h2>
        <div class="container">
        	<div class="listing" id="listing">
	        {foreach from=$sArticle.sRelatedArticles item=sArticleSub key=key name="counter"}
	        	
	        	{include file="frontend/listing/box_article.tpl" sArticle=$sArticleSub sTemplate='listing'}
	        	
	        {/foreach}
	        </div>
        </div>
        <div class="clear">&nbsp;</div>
    </div>
{/if}
