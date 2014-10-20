{if $Data}
    <div class="blog--container block-group">
        {foreach $Data.entries as $entry}
			{$image = $entry.media.thumbnails.{$Data.thumbnail_size}}
            <div class="blog--entry blog--entry-{$entry@index} block{if $entry@last} is--last{/if}" style="width:{{"100" / $Data.entries|count}|round:2}%">
               {if $image}
                    <a class="blog--image" href="{url controller=blog action=detail sCategory=$entry.categoryId blogArticle=$entry.id}" style="background-image:url({link file=$image})" title="{$entry.title|escape}">&nbsp;</a>
               {else}
                    <a class="blog--image" href="{url controller=blog action=detail sCategory=$entry.categoryId blogArticle=$entry.id}" title="{$entry.title|escape}">
                        {s name="EmotionBlogPreviewNopic"}Kein Bild vorhanden{/s}
                    </a>
               {/if}

                <a class="blog--title" href="{url controller=blog action=detail sCategory=$entry.categoryId blogArticle=$entry.id}" title="{$entry.title|escape}">{$entry.title|truncate:40}</a>

                {if $entry.shortDescription}
                    <p class="blog--desc">{$entry.shortDescription|truncate:135}</p>
                {else}
                    <p class="blog--desc">{$entry.description|strip_tags|truncate:135}</p>
                {/if}
            </div>
        {/foreach}
    </div>
{/if}
