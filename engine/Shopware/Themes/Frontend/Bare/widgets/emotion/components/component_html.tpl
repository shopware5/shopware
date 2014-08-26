{block name="widget_emotion_component_html_panel"}
    <div class="panel has--border">
        {block name="widget_emotion_component_html_title"}
            {if $Data.cms_title}
                <h1 class="panel--title">{$Data.cms_title}</h1>
            {/if}
        {/block}

        {block name="widget_emotion_component_html_content"}
            <div class="panel--body is--wide" data-lines="{{$sColHeight * 1.5}|round:0}" data-collapse-text="true"
                 data-readMoreText="{s name='ListingCategoryTeaserShowMore' namespace='frontend/listing/listing'}{/s}"
                 data-readLessText="{s name='ListingCategoryTeaserShowLess' namespace='frontend/listing/listing'}{/s}">
                {$Data.text}
            </div>
        {/block}
    </div>
{/block}