{extends file='frontend/index/index.tpl'}

{block name='frontend_index_header'}
    {include file='frontend/blog/header.tpl'}
{/block}

{* Main content *}
{block name='frontend_index_content'}
    <div class="blog--detail panel block-group">

        {* Content *}
        {block name='frontend_blog_detail_content'}
            <div class="blog--detail-content blog--box block" itemscope itemtype="https://schema.org/BlogPosting">

                {* Rich snippets *}
                {block name='frontend_blog_detail_rich_snippets'}
                    <meta itemprop="image" content="{if $sArticle.preview.thumbnails[1].source}{$sArticle.preview.thumbnails[1].source}{else}{link file=$theme.desktopLogo fullPath}{/if}">
                    <meta itemprop="dateModified" content="{$sArticle.displayDate->format(DateTime::ATOM)|escapeHtml}">
                    <meta itemprop="description" content="{$sArticle.shortDescription}">
                    <meta itemprop="mainEntityOfPage" content="{url controller=blog action=detail sCategory=$sArticle.categoryId blogArticle=$sArticle.id}">

                    <div itemprop="publisher" itemscope itemtype="http://schema.org/Organization">
                        <meta itemprop="name" content="{{config name=sShopname}|escapeHtml}">
                        <div itemprop="logo" itemscope itemtype="http://schema.org/ImageObject">
                            <meta itemprop="url" content="{link file=$theme.desktopLogo fullPath}">
                        </div>
                    </div>
                    <meta itemprop="wordCount" content="{$sArticle.description|strip_tags|count_words}">
                {/block}

                {* Detail Box Header *}
                {block name='frontend_blog_detail_box_header'}
                    <div class="blog--detail-header">

                        {* Article name *}
                        {block name='frontend_blog_detail_title'}
                            <h1 class="blog--detail-headline" itemprop="name">{$sArticle.title}</h1>
                            <meta itemprop="headline" content="{$sArticle.title}">
                        {/block}

                        {* Metadata *}
                        {block name='frontend_blog_detail_metadata'}
                            <div class="blog--box-metadata">

                                {* Author *}
                                {block name='frontend_blog_detail_author'}
                                    {if $sArticle.author.name}
                                        <span class="blog--metadata-author blog--metadata is--first" itemprop="author" itemscope itemtype="https://schema.org/Person">{s name="BlogInfoFrom"}{/s}: <span itemprop="name">{$sArticle.author.name}</span></span>
                                    {/if}
                                {/block}

                                {* Date *}
                                {block name='frontend_blog_detail_date'}
                                    <span class="blog--metadata-date blog--metadata{if !$sArticle.author.name} is--first{/if}" itemprop="datePublished" content="{$sArticle.displayDate->format(DateTime::ATOM)|escapeHtml}">{$sArticle.displayDate|date:"DATETIME_SHORT"}</span>
                                {/block}

                                {* Category *}
                                {block name='frontend_blog_detail_category'}{/block}

                                {* Comments *}
                                {block name='frontend_blog_detail_comments_count'}
                                    <span class="blog--metadata-comments blog--metadata">
                                        {s name="BlogLinkComments" assign="snippetBlogLinkComments"}{/s}
                                        <a data-scroll="true" data-scrollTarget="#blog--comments-start" href="#blog--comments-start" title="{$snippetBlogLinkComments|escape}">{$sArticle.comments|count|default:0} {s name="BlogInfoComments"}{/s}</a>
                                    </span>
                                {/block}

                                {* Rating *}
                                {block name='frontend_blog_detail_rating'}
                                    {if $sArticle.sVoteAverage|round}
                                        <span class="blog--metadata-rating blog--metadata">
                                            {s name="BlogHeaderRating" assign="snippetBlogHeaderRating"}{/s}
                                            <a data-scroll="true" data-scrollTarget="#blog--comments-start" href="#blog--comments-start" class="blog--rating-link" rel="nofollow" title="{$snippetBlogHeaderRating|escape}">
                                                {include file="frontend/_includes/rating.tpl" points=$sArticle.sVoteAverage|round type="aggregated" count=$sArticle.comments|count}
                                            </a>
                                        </span>
                                    {/if}
                                {/block}

                            </div>
                        {/block}
                    </div>
                {/block}

                {* Detail Box Content *}
                {block name='frontend_blog_detail_box_content'}
                    <div class="blog--detail-box-content block">

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

                                    {$tags=''}
                                    {foreach $sArticle.tags as $tag}
                                        {$tags="{$tags}{$tag.name}{if !$tag@last},{/if}"}
                                    {/foreach}
                                    <meta itemprop="keywords" content="{$tags}">

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

        {* Comments *}
        {block name='frontend_blog_detail_comments_list'}
            {include file="frontend/blog/comments.tpl"}
        {/block}

        {* Cross selling *}
        {block name='frontend_blog_detail_crossselling'}
            {if $sArticle.sRelatedArticles}
                <div class="blog--crossselling block">

                    {* Headline *}
                    {block name='frontend_blog_detail_crossselling_headline'}
                        <div class="blog--crossselling-headline panel--title is--underline">{s name="BlogHeaderCrossSelling"}{/s}</div>
                    {/block}

                    {* Listing *}
                    {block name='frontend_blog_detail_crossselling_listing'}
                        <div class="blog--crossselling panel--body is--wide block">
                            {include file="frontend/_includes/product_slider.tpl" articles=$sArticle.sRelatedArticles productSliderCls="crossselling--content panel--body is--rounded"}
                        </div>
                    {/block}
                </div>
            {/if}
        {/block}
    </div>
{/block}
