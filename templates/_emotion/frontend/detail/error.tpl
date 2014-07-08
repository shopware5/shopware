
{extends file='frontend/detail/index.tpl'}
{block name='frontend_index_content'}

{if $sRelatedArticles}
	
   
	<div id="related">
        <h2>{se name='DetailRelatedHeader'}{/se}</h2>
        <h2>{se name='DetailRelatedHeaderSimilarArticles'}{/se}</h2>
        
        	<div class="listing" id="listing">
	        {foreach from=$sRelatedArticles item=sArticleSub key=key name="counter"}
	        	
	        	{include file="frontend/listing/box_article.tpl" sArticle=$sArticleSub}
	        	
	        {/foreach}
	        </div>
        
        <hr class="clear" />
    </div>
   
    
{/if}
{/block}
