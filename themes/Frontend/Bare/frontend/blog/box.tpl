<div class="blog--box panel has--border is--rounded">
    {block name='frontend_blog_col_blog_entry'}

        {* Blog Header *}
        {block name='frontend_blog-col_box_header'}
            <div class="blog--box-header">

                {* Article name *}
                {block name='frontend_blog_col_article_name'}
                    <h2 class="blog--box-headline panel--title">
                        <a class="blog--box-link" href="{url controller=blog action=detail sCategory=$sArticle.categoryId blogArticle=$sArticle.id}" title="{$sArticle.title|escape}">{$sArticle.title}</a>
                    </h2>
                {/block}

                {* Meta data *}
                {block name='frontend_blog_col_meta_data'}
                    <div class="blog--box-metadata">

                        {* Author *}
                        {block name='frontend_blog_col_meta_data_name'}
                            {if $sArticle.author.name}
                                <span class="blog--metadata-author blog--metadata is--nowrap is--first">{s name="BlogInfoFrom"}{/s} {$sArticle.author.name}</span>
                            {/if}
                        {/block}

                        {* Date *}
                        {block name='frontend_blog_col_meta_data_date'}
                            {if $sArticle.displayDate}
                                <span class="blog--metadata-date blog--metadata is--nowrap{if !$sArticle.author.name} is--first{/if}">{$sArticle.displayDate|date:"DATETIME_SHORT"}</span>
                            {/if}
                        {/block}

                        {* Description *}
                        {block name='frontend_blog_col_meta_data_description'}
                            {if $sArticle.categoryInfo.description}
                                <span class="blog--metadata-description is--nowrap">
                                    {if $sArticle.categoryInfo.linkCategory}
                                        <a href="{$sArticle.categoryInfo.linkCategory}" title="{$sArticle.categoryInfo.description|escape}">{$sArticle.categoryInfo.description}</a>
                                    {else}
                                        {$sArticle.categoryInfo.description}
                                    {/if}
                                </span>
                            {/if}
                        {/block}

                        {* Comments *}
                        {block name='frontend_blog_col_meta_data_comments'}
                            <span class="blog--metadata-comments blog--metadata is--nowrap{if $sArticle.sVoteAverage|round ==0} is--last{/if}">
                                {s name="BlogLinkComments" namespace="frontend/blog/detail" assign="snippetBlogLinkComments"}{/s}
                                <a href="{url controller=blog action=detail sCategory=$sArticle.categoryId blogArticle=$sArticle.id}#blog--comments-start"
                                   title="{$snippetBlogLinkComments|escape}">
                                    {if $sArticle.numberOfComments}{$sArticle.numberOfComments}{else}0{/if} {s name="BlogInfoComments"}{/s}
                                </a>
                            </span>
                        {/block}

                        {* Rating *}
                        {block name='frontend_blog_col_meta_data_rating'}
                            {if $sArticle.sVoteAverage|round !=0}
                                <div class="blog--metadata-rating blog--metadata is--nowrap is--last">
                                    {include file="frontend/_includes/rating.tpl" points=$sArticle.sVoteAverage|round type="aggregated" count=$sArticle.comments|count microData=false}
                                </div>
                            {/if}
                        {/block}
                    </div>
                {/block}

            </div>
        {/block}

        {* Blog Box *}
        {block name='frontend_blog_col_box_content'}
            <div class="blog--box-content panel--body is--wide block">

                {* Article pictures *}
                {block name='frontend_blog_col_article_picture'}
                    {if $sArticle.media}
                        <div class="blog--box-picture">
                            <a href="{url controller=blog action=detail sCategory=$sArticle.categoryId blogArticle=$sArticle.id}"
                               class="blog--picture-main"
                               title="{$sArticle.title|escape}">
                                {if isset($sArticle.media.thumbnails)}
                                    <img srcset="{$sArticle.media.thumbnails[0].sourceSet}"
                                         alt="{$sArticle.title|escape}"
                                         title="{$sArticle.title|escape|truncate:160}" />
                                {else}
                                    <img src="{media path=$sArticle.media[0].media.path}"
                                         alt="{$sArticle.title|escape}"
                                         title="{$sArticle.title|escape|truncate:160}" />
                                {/if}
                            </a>
                        </div>
                    {/if}
                {/block}

                {* Article Description *}
                {block name='frontend_blog_col_description'}
                    <div class="blog--box-description{if !$sArticle.media} is--fluid{/if}">

                        {block name='frontend_blog_col_description_short'}
                            <div class="blog--box-description-short">
                                {if $sArticle.shortDescription}{$sArticle.shortDescription|nl2br}{else}{$sArticle.shortDescription}{/if}
                            </div>
                        {/block}

                        {* Read more button *}
                        {block name='frontend_blog_col_read_more'}
                            <div class="blog--box-readmore">
                                <a href="{url controller=blog action=detail sCategory=$sArticle.categoryId blogArticle=$sArticle.id}" title="{$sArticle.title|escape}" class="btn is--primary is--small">{s name="BlogLinkMore"}{/s}</a>
                            </div>
                        {/block}

                        {* Tags *}
                        {block name='frontend_blog_col_tags'}
                            <div class="blog--box-tags">
                                {if $sArticle.tags|@count > 1}
                                    <strong>{s name="BlogInfoTags"}{/s}</strong>
                                    {foreach $sArticle.tags as $tag}
                                        <a href="{$tag.link}" title="{$tag.name|escape}">{$tag.name}</a>{if !$tag@last}, {/if}
                                    {/foreach}
                                {/if}
                            </div>
                        {/block}

                    </div>
                {/block}

            </div>
        {/block}

    {/block}
</div>