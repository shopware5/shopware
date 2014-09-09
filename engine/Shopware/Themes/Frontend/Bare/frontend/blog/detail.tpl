{extends file='frontend/index/index.tpl'}

{block name='frontend_index_header'}
    {include file='frontend/blog/header.tpl'}
{/block}

{* Main content *}
{block name='frontend_index_content'}
    <div class="blog--detail panel has--border is--rounded block-group listing listing--listing-1col">

        {* Content *}
        {block name='frontend_blog_detail_content'}
            <div class="blog--detail-content blog--box block" itemscope itemtype="https://schema.org/BlogPosting">

                {* Detail Box Header *}
                {block name='frontend_blog_detail_box_header'}
                    <div class="blog--detail-header">

                        {* Article name *}
                        {block name='frontend_blog_detail_title'}
                            <h1 class="blog--detail-headline panel--title" itemprop="name">{$sArticle.title}</h1>
                        {/block}

                        {* Metadata *}
                        {block name='frontend_blog_detail_metadata'}
                            <div class="blog--box-metadata">

                                {* Author *}
                                {block name='frontend_blog_detail_author'}
                                    {if $sArticle.author.name}
                                        <span class="blog--metadata-author blog--metadata is--first" itemprop="author">{s name="BlogInfoFrom"}{/s}: {$sArticle.author.name}</span>
                                    {/if}
                                {/block}

                                {* Date *}
                                {block name='frontend_blog_detail_date'}
                                    <span class="blog--metadata-date blog--metadata{if !$sArticle.author.name} is--first{/if}" itemprop="dateCreated">{$sArticle.displayDate|date:"DATETIME_SHORT"}</span>
                                {/block}

                                {* Category *}
                                {block name='frontend_blog_detail_category'}{/block}

                                {* Comments *}
                                {block name='frontend_blog_detail_comments'}
                                    {if $sArticle.sVoteAverage|round != "0"}
                                        <span class="blog--metadata-comments blog--metadata">
                                            <a href="#commentcontainer" title="{"{s name="BlogLinkComments"}{/s}"|escape}">{if $sArticle.comments|count}{$sArticle.comments|count}{else}0 {/if} {s name="BlogInfoComments"}{/s}</a>
                                        </span>
                                    {/if}
                                {/block}

                                {* Rating *}
                                {block name='frontend_blog_detail_rating'}
                                    <span class="blog--metadata-rating blog--metadata is--last">
                                        {if $sArticle.sVoteAverage|round != "0"}
                                            <a href="#commentcontainer" class="blog--rating-link" rel="nofollow" title="{"{s name='BlogHeaderRating'}{/s}"|escape}">
                                                {include file="frontend/_includes/rating.tpl" points=$sArticle.sVoteAverage|round type="aggregated" count=$sArticle.comments|count}
                                            </a>
                                        {else}
                                            {include file="frontend/_includes/rating.tpl" points=$sArticle.sVoteAverage|round type="aggregated" count=$sArticle.comments|count}
                                        {/if}
                                    </span>
                                {/block}

                            </div>
                        {/block}
                    </div>
                {/block}

                {* Detail Box Content *}
                {block name='frontend_blog_detail_box_content'}
                    <div class="blog--detail-box-content panel--body is--wide block">

                        {* Description *}
                        {block name='frontend_blog_detail_description'}
                            <div class="blog--detail-description block" itemprop="articleBody">

                                {* Image + Thumbnails *}
                                {block name='frontend_blog_detail_images'}
                                    {include file="frontend/blog/images.tpl"}
                                {/block}

                                {$sArticle.description}
                            </div>
                        {/block}

                        {* Tags *}
                        {block name='frontend_blog_detail_tags'}
                            <div class="blog--detail-tags block">
                                {if $sArticle.tags}
                                    <span class="is--bold">{s name="BlogInfoTags"}{/s}:</span>
                                    {foreach $sArticle.tags as $tag}
                                        <a href="{url controller=blog sCategory=$sArticle.categoryId sFilterTags=$tag.name}" title="{$tag.name|escape}">{$tag.name}</a>{if !$tag@last}, {/if}
                                    {/foreach}
                                {/if}
                            </div>

                            {* Bookmarks *}
                            {block name='frontend_blog_detail_bookmarks'}
                                {include file="frontend/blog/bookmarks.tpl"}
                            {/block}

                        {/block}
                    </div>
                {/block}
            </div>
        {/block}

        {* Cross selling *}
        {block name='frontend_blog_detail_crossselling'}
            {if $sArticle.sRelatedArticles}
                <div class="blog--crossselling panel--body is--wide block">

                    {* Headline *}
                    {block name='frontend_blog_detail_crossselling_headline'}
                        <h2 class="blog--crossselling-headline">{s name="BlogHeaderCrossSelling"}{/s}</h2>
                    {/block}

                    {* Listing *}
                    {block name='frontend_blog_detail_crossselling_listing'}
                        <div class="blog--crossseling-listing">
                            {foreach $sArticle.sRelatedArticles as $related}
                                {if $relatedarticle@last}
                                    {$lastitem=true}
                                {else}
                                    {$lastitem=false}
                                {/if}
                                {include file="frontend/listing/box_blog.tpl" sArticle=$related sTemplate='listing-1col' lastitem=$lastitem}
                            {/foreach}
                        </div>
                    {/block}
                </div>
            {/if}
        {/block}

        {* Comments *}
        {block name='frontend_blog_detail_comments'}
            {include file="frontend/blog/comments.tpl"}
        {/block}
    </div>
{/block}