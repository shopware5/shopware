{* Article picture *}
{if $sArticle.preview.thumbNails.2}
	{block name='frontend_blog_images_main_image'}{/block}
    <div id="imgTarget">
        <a href="{$sArticle.preview.source}"
           rel="lightbox[photos]"
           title="{if $sArticle.preview.media.description}{$sArticle.preview.media.description}{else}{$sArticle.title}{/if}"
           class="main_image">

            <img src="{$sArticle.preview.thumbNails.2}"
                 alt="{$sArticle.title}"
                 border="0"
                 title="{if $sArticle.preview.media.description}{$sArticle.preview.media.description}{else}{$sArticle.title}{/if}"/>
        </a>
    </div>
    
	{* Thumbnails *}
	{if $sArticle.media}
		{block name='frontend_blog_images_thumbnails'}
		<div class="thumbnail_box">
			{foreach from=$sArticle.media item=sArticleMedia}
				{if !$sArticleMedia.preview}
                    <a href="{link file=$sArticleMedia.media.path}"
                       rel="lightbox"
                       title="{if $sArticleMedia.description}{$sArticleMedia.description}{else}{$sArticle.title}{/if}">
                        <img src="{link file=$sArticleMedia.thumbNails.0}">
                    </a>
			    {/if}
			{/foreach}
	    	<div class="space">&nbsp;</div>
	    </div>
	    {/block}
	{/if}
{/if}