{block name="widget_emotion_component_category_teaser_panel"}
    {if $Data}
        <div class="emotion--category-teaser">

            {* Category teaser image *}
            {block name="widget_emotion_component_category_teaser_image"}
                {if $Data.image_type === 'selected_image'}
                    {$image = $Data.image}
                {elseif isset($Data.images)}
                    {$image = $Data.images.5}
                {else}
                    {$image = "{link file='frontend/_public/src/img/no-picture.jpg'}"}
                {/if}
            {/block}

            {* Category teaser lnk *}
            {block name="widget_emotion_component_category_teaser_link"}

                {if $Data.blog_category}
                    {$url = "{url controller=blog action=index sCategory=$Data.category_selection}"}
                {else}
                    {$url = "{url controller=cat action=index sCategory=$Data.category_selection}"}
                {/if}

                <a href="{$url}"
                   title="{$Data.categoryName|strip_tags|escape}"
                   class="category-teaser--link"
                   style="background-image: url('{link file=$image}')">

                    {* Category teaser title *}
                    {block name="widget_emotion_component_category_teaser_title"}
                        <span class="category-teaser--title">
                            {$Data.categoryName}
                        </span>
                    {/block}
                </a>
            {/block}


        </div>
    {/if}
{/block}