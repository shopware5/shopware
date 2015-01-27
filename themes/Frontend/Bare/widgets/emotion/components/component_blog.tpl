{block name="widget_emotion_component_blog"}
    <div class="emotion--blog">
        {if $Data}
            {block name="widget_emotion_component_blog_container"}
                <div class="blog--container block-group">
                    {foreach $Data.entries as $entry}
                        {block name="widget_emotion_component_blog_entry"}
                            {$image = $entry.media.thumbnails.{$Data.thumbnail_size}}

                            <div class="blog--entry blog--entry-{$entry@index} block"
                                 style="width:{{"100" / $Data.entries|count}|round:2}%">

                                {block name="widget_emotion_component_blog_entry_image"}
                                    {if $image}
                                        <a class="blog--image" href="{url controller=blog action=detail sCategory=$entry.categoryId blogArticle=$entry.id}" style="background-image:url({link file=$image})" title="{$entry.title|escape}">&nbsp;</a>
                                    {else}
                                        <a class="blog--image" href="{url controller=blog action=detail sCategory=$entry.categoryId blogArticle=$entry.id}" title="{$entry.title|escape}">
                                            {s name="EmotionBlogPreviewNopic"}Kein Bild vorhanden{/s}
                                        </a>
                                    {/if}
                                {/block}

                                {block name="widget_emotion_component_blog_entry_title"}
                                    <a class="blog--title"
                                       href="{url controller=blog action=detail sCategory=$entry.categoryId blogArticle=$entry.id}"
                                       title="{$entry.title|escape}">
                                       {$entry.title|truncate:40}
                                    </a>
                                {/block}

                                {block name="widget_emotion_component_blog_entry_description"}
                                    <div class="blog--description">
                                        {if $entry.shortDescription}
                                            {$entry.shortDescription|truncate:135}
                                        {else}
                                            {$entry.description|strip_tags|truncate:135}
                                        {/if}
                                    </div>
                                {/block}
                            </div>
                        {/block}
                    {/foreach}
                </div>
            {/block}
        {/if}
    </div>
{/block}