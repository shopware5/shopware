{block name="widget_emotion_component_html_panel"}
    <div class="emotion--html panel has--border">

        {block name="widget_emotion_component_html_title"}
            {if $Data.cms_title}
                <div class="panel--title is--underline html--title">
                    {$Data.cms_title}
                </div>
            {/if}
        {/block}

        {block name="widget_emotion_component_html_content"}
            <div class="html--content{if $Data.cms_title} panel--body is--wide{/if}">
                {$Data.text}
            </div>
        {/block}
    </div>
{/block}