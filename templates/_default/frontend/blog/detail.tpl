{extends file='frontend/index/index.tpl'}


{block name='frontend_index_header'}
    {include file='frontend/blog/header.tpl'}
{/block}

{* Main content *}
{block name='frontend_index_content'}
<div class="blogbox grid_16 last">

    {* @deprecated no longer in use *}
	{block name="frontend_detail_index_navigation"}{/block}

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
            <span>{$sArticle.displayDate|date_format:"%d.%m.%Y %H:%M"}</span>
		{/block}
		
		{* Category *}
		{block name='frontend_blog_detail_category'}{/block}
		
		{* Comments *}
		{block name='frontend_blog_detail_comments'}
		{if $sArticle.sVoteAverage.averange|round != "0"}
		<span class="last">
			<a href="#commentcontainer" title="{s name="BlogLinkComments"}{/s}">
				{if $sArticle.comments|count}
					{$sArticle.comments|count}
				{else}
					0
				{/if} 
				{se name="BlogInfoComments"}{/se}
			</a>
		</span>
		{/if}
		{/block}
	</p>
	
	{* Description *}
	{block name='frontend_blog_detail_description'}
		<div id="description">
			{$sArticle.description}
		</div>
	{/block}
		
	<div class="grid_6 social last">
		{* Image + Thumbnails *}
		{block name='frontend_blog_detail_images'}
			{include file="frontend/blog/images.tpl"}
		{/block}
		
		<h2 class="headingbox">{s name="BlogHeaderSocialmedia"}{/s}</h2>
		
		<div class="outer">
			{* Bookmarks *}
			{block name='frontend_blog_detail_bookmarks'}
				{include file="frontend/blog/bookmarks.tpl"}
			{/block}
			
			{* Rating*}
			{block name='frontend_blog_detail_rating'}
			<div class="rating">
				<h5 class="bold">
					{se name="BlogHeaderRating"}{/se}:
				</h5>
				<div class="star star{$sArticle.sVoteAverage|round}">{se name="BlogHeaderRating"}{/se}</div>
			</div>
			{/block}
			
			{* Tags *}
            {if $sArticle.tags}
				{block name='frontend_blog_detail_tags'}
				<div class="tags">
					<h5 class="bold">
						{se name="BlogInfoTags"}{/se}:
					</h5>
                    {foreach $sArticle.tags as $tag}
                        <span class="tag">{$tag.name}</span>
                    {/foreach}
					<div class="clear">&nbsp;</div>
				</div>
				{/block}
			{/if}
		</div>
	</div>
	
	<div class="doublespace">&nbsp;</div>
	
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
		
    {* Our Comment *}
    {if $sArticle.attribute.attribute3}
        {block name='frontend_blog_detail_comment'}
        <div id="unser_kommentar">
            <p>
                {se name="BlogInfoComment"}{/se} "{$sArticle.title}"
            </p>
            <blockquote>
                {$sArticle.attribute.attribute3}
            </blockquote>
        </div>
        {/block}
    {/if}
				
		{* Comments *}
		{block name='frontend_blog_detail_comments'}
			{include file="frontend/blog/comments.tpl"}
		{/block}
	</div>
{/block}

{* Empty sidebar right *}
{block name='frontend_index_content_right'}{/block}