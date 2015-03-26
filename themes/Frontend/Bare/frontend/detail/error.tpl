{extends file='frontend/index/index.tpl'}

{block name='frontend_index_content'}
    {if $sRelatedArticles}
        <div id="related">
            <h2>{s name='DetailRelatedHeader'}{/s}</h2>
            <h2>{s name='DetailRelatedHeaderSimilarArticles'}{/s}</h2>

            <div class="listing" id="listing">
                {foreach from=$sRelatedArticles item=sArticleSub key=key name="counter"}
                    {include file="frontend/listing/box_article.tpl" sArticle=$sArticleSub}
                {/foreach}
            </div>
        </div>
    {/if}
{/block}