<div class="blogbox">
	{block name='frontend_blog_col_blog_entry'}
	
	{* Article name *}
	{block name='frontend_blog_col_article_name'}
	<h2>
		<a href="{url controller=blog action=detail sCategory=$sArticle.categoryId blogArticle=$sArticle.id}" title="{$sArticle.title}">{$sArticle.title}</a>
	</h2>
	{/block}
	
	{* Meta data *}
	{block name='frontend_blog_col_meta_data'}
        <p class="post_metadata">
            {if $sArticle.author.name}
                <span class="first">
                        {s name="BlogInfoFrom"}{/s} {$sArticle.author.name}
                    </span>
            {/if}

            {if $sArticle.displayDate}
                <span {if !$sArticle.author.name} class="first"{/if}>
                    {$sArticle.displayDate|date_format:"%d.%m.%Y %H:%M"}
                </span>
            {/if}

            {if $sArticle.categoryInfo.description}<span>{if $sArticle.categoryInfo.linkCategory}<a href="{$sArticle.categoryInfo.linkCategory}" title="{$sArticle.categoryInfo.description}">{$sArticle.categoryInfo.description}</a>{else}{$sArticle.categoryInfo.description}{/if}</span>{/if}

            <span {if $sArticle.sVoteAverage|round ==0}class="last"{/if}><a href="{url controller=blog action=detail sCategory=$sArticle.categoryId blogArticle=$sArticle.id}#commentcontainer" title="{$sArticle.title}">{if $sArticle.numberOfComments}{$sArticle.numberOfComments}{else}0{/if} {s name="BlogInfoComments"}{/s}</a></span>
            {if $sArticle.sVoteAverage|round !=0}
                <span class="last star star{$sArticle.sVoteAverage|round}">{se name="BlogInfoRating"}{/se}</span>
            {/if}
        </p>
	{/block}
	
	{* Article picture *}
    {foreach $sArticle.media as $media}
        {if $media.preview}
            <div class="blog_picture">
                {block name='frontend_blog_col_article_picture'}
                    {if !$homepage}
                    <a href="{url controller=blog action=detail sCategory=$sArticle.categoryId blogArticle=$sArticle.id}"title="{$sArticle.title}">
                    {else}
                    <a href="{url controller=blog action=detail sCategory=$sArticle.categoryId blogArticle=$sArticle.id}" title="{$sArticle.title}" class="main_image">
                    {/if}
                       <img src="{link file=$sArticle.preview.thumbNails.1}" alt="{$sArticle.title}" border="0" title="{$sArticle.title}" />
                    </a>
                {/block}
            </div>
        {/if}
    {/foreach}


    {* Article Description *}
	<div>
		{block name='frontend_blog_col_description'}
			{if $sArticle.shortDescription}{$sArticle.shortDescription|nl2br}{else}{$sArticle.description}{/if}
		{/block}
	</div>
	
	<div class="clear">&nbsp;</div>
	
	{* Read more button *}
	{block name='frontend_blog_col_read_more'}	
	<p>
		<a href="{url controller=blog action=detail sCategory=$sArticle.categoryId blogArticle=$sArticle.id}" title="{$sArticle.title}" class="more_info">{se name="BlogLinkMore"}{/se}</a>
	</p>
	{/block}
	{/block}
</div>