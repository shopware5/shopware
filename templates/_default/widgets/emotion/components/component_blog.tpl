{if $Data}
    <div class="blog-outer-container">
        {foreach $Data.entries as $entry}
            <div class="blog-entry" style="width:{"100" / $Data.entries|count}%">
            	<div class="blog-entry-inner{if $entry@last} last{/if}">
	                <div class="blog_img">
		                <a href="{url controller=blog action=detail sCategory=$entry.categoryId blogArticle=$entry.id}" title="{$entry.title}">
			                {if $entry.media.thumbnails.3}
				                 <img src="{$entry.media.thumbnails.3}" />
				            {else}
				            	{se name="EmotionBlogPreviewNopic"}Kein Bild vorhanden{/se}
				            {/if}
		                </a>
	                </div>
	                
	                <h2>
	                	<a href="{url controller=blog action=detail sCategory=$entry.categoryId blogArticle=$entry.id}" title="{$entry.title}">{$entry.title|truncate:40}</a>
	                </h2>
	                {if $entry.shortDescription}
	                    <p>{$entry.shortDescription|truncate:105}</p>
	                {else}
	                    <p>{$entry.description|strip_tags|truncate:105}</p>
	                {/if}
            	</div>
            </div>
        {/foreach}
    </div>
{/if}
