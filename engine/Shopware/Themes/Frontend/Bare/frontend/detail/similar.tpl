<div class="similar">
	<div class="content">
		{if $sArticle.sSimilarArticles}    
	        <h3>{s name='DetailSimilarHeader'}{/s}</h3>

			<ul class="listing">
				{foreach $sArticle.sSimilarArticles as $sSimilarArticle}
					{include file="frontend/listing/box_similar.tpl" sArticle=$sSimilarArticle}
				{/foreach}
			</ul>
	    {/if}
    </div>
</div>