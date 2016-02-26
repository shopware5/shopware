{extends file='frontend/index/index.tpl'}


{block name='frontend_index_header'}
    {include file='frontend/blog/header.tpl'}
{/block}

{* Main content *}
{block name='frontend_index_content'}
    <div class="blogbox grid_16 last">

        {* @deprecated no longer in use *}
	    {block name="frontend_detail_index_navigation"}{/block}

	    <div class="blogdetail">
	        <div class="blogdetail_header">
	            {* Article name *}
	            {block name='frontend_blog_detail_title'}
	                <h1>{$sArticle.title}</h1>
	            {/block}
	            <p class="post_metadata">

	                {* Author *}
	                {block name='frontend_blog_detail_author'}
	                {if $sArticle.author.name}
	                <span class="first">
	                    {se name="BlogInfoFrom"}{/se}: {$sArticle.author.name}
	                </span>
	                {/if}
	                {/block}

	                {* Date *}
	                {block name='frontend_blog_detail_date'}
	                <span>{$sArticle.displayDate|date:"DATETIME_SHORT"}</span>
	                {/block}

	                {* Category *}
	                {block name='frontend_blog_detail_category'}{/block}

	                {* Comments *}
	                {block name='frontend_blog_detail_comments'}
	                {if $sArticle.sVoteAverage.averange|round != "0"}
                    <span class="last">
	                    <a href="#commentcontainer" title="{s name="BlogLinkComments"}{/s}">
	                        {if $sArticle.comments|count}{$sArticle.comments|count}{else}0 {/if} {se name="BlogInfoComments"}{/se}
	                    </a>
	                </span>
	                {/if}
	                {/block}

	                {* Rating*}
	                {block name='frontend_blog_detail_rating'}
	                <span class="rating last">{se name="BlogHeaderRating"}{/se}:
	                    <span class="star star{$sArticle.sVoteAverage|round}">{se name="BlogHeaderRating"}{/se}</span>
	                </span>
	                {/block}
	            </p>
	        </div>

	        <div class="blogdetail_content">

            {* Description *}
            {block name='frontend_blog_detail_description'}
                <div class="description">
                    {$sArticle.description}
                </div>
            {/block}

	            {* Image + Thumbnails *}
	            {block name='frontend_blog_detail_images'}
	                {include file="frontend/blog/images.tpl"}
	            {/block}
	            <div class="clear">&nbsp;</div>

	            {* Tags *}
	            <div class="blog_tags">
	                {if $sArticle.tags}
	                    <strong>{se name="BlogInfoTags"}{/se}:</strong>
	                    {foreach $sArticle.tags as $tag}
	                    <a href="{url controller=blog sCategory=$sArticle.categoryId sFilterTags=$tag.name}" title="{$tag.name}">{$tag.name}</a>{if !$tag@last}, {/if}
	                    {/foreach}
	                {/if}

	                <div class="right">
	                {* Bookmarks *}
	                {block name='frontend_blog_detail_bookmarks'}
	                    {include file="frontend/blog/bookmarks.tpl"}
	                {/block}
	                </div>
	            </div>

                {* @deprecated no longer in use *}
                {block name='frontend_blog_detail_links'}{/block}

                {* @deprecated no longer in use go over the mediaselection in the tiny mce to present downloads *}
                {block name='frontend_blog_detail_downloads'}{/block}

                {* Cross selling *}
	            {if $sArticle.sRelatedArticles}
	                <h2 class="headingbox">{s name="BlogHeaderCrossSelling"}{/s}</h2>
	                <div class="bloglisting" id="listing-blog">
	                    {foreach from=$sArticle.sRelatedArticles item=related name=relatedarticle}
	                        {if $smarty.foreach.relatedarticle.last}
	                            {assign var=lastitem value=1}
	                        {else}
	                            {assign var=lastitem value=0}
	                        {/if}
	                        {include file="frontend/listing/box_blog.tpl" sArticle=$related lastitem=$lastitem}
	                    {/foreach}
	                </div>
	            {/if}

	            {* Comments *}
	            {block name='frontend_blog_detail_comments'}
	                {include file="frontend/blog/comments.tpl"}
	            {/block}
	        </div>
	    </div>
	</div>
{/block}

{* Empty sidebar right *}
{block name='frontend_index_content_right'}{/block}

{* Empty sidebar left *}
{block name='frontend_index_content_left'}{/block}
