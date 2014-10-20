{block name="widget_emotion_component_video_container"}
    <div class="emotion--element-video-inner">
        {block name="widget_emotion_component_video_element"}
            {strip}
            <video class="emotion--element-video-element"
                   data-video-resize="true"
                    {if $Data.fallback_picture} poster="{$Data.fallback_picture}"{/if}
                    {if $Data.autobuffer} preload{/if}
                    {if $Data.autoplay} autoplay{/if}
                    {if $Data.loop} loop{/if}
                    {if $Data.controls} controls{/if}
                    {if $Data.muted} muted{/if}
                    {if $Data.originLeft} data-origin-y="{$Data.originLeft}"{/if}
                    {if $Data.originTop} data-origin-x="{$Data.originTop}"{/if}
                    {if $Data.scale} data-scale="{$Data.scale}"{/if}>
                <source src="{$Data.webm_video}" type="video/webm">
                <source src="{$Data.h264_video}" type="video/mp4">
                <source src="{$Data.ogg_video}" type="video/ogg" />
            </video>
            {/strip}
        {/block}

        {block name="widget_emotion_component_video_play_button"}
            <a href="#play-video" class="play--video" data-play="icon--play" data-pause="icon--pause">
                <i class="icon--play"></i>
            </a>
        {/block}

        {block name="widget_emotion_component_video_text"}
            {if $Data.html_text}
                <div class="emotion--element-video--text"{if $Data.overlay} style="background: {$Data.overlay}"{/if}>
                    {$Data.html_text}
                </div>
            {/if}
        {/block}
    </div>
{/block}