{namespace name=backend/search/index}

{block name="backend/search/index/result_total"}
    <div class="row articles">

        {block name="backend/search/index/result_header"}
            <div class="header">
                <div class="inner">
                    {s name="title/articles"}Article{/s}:
                </div>
            </div>
        {/block}

        {block name="backend/search/index/result_content"}
            <div class="result-container">
                {foreach $searchResult.articles as $item}
                    <a onclick="openSearchResult('articles', {$item.articleId});return false;" href="#"{if $item@iteration is odd by 2} class="odd"{/if}>
                        <span class="name" style="display:inline-block;width: 155px">{$item.name|truncate:60}</span>

                        {if $item.description}
                            <span class="desc">{$item.description|truncate:120}</span>
                        {/if}
                        <span class="right">{$item.number}</span>
                    </a>
                {/foreach}
            </div>
        {/block}
    </div>
{/block}
