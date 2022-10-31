{block name="widget_emotion_component_youtube"}
    <div class="emotion--youtube">
        {if $Data && $Data.video_id}
            {if $Data.video_hd}{$options[] = 'hd=1&vq=hd720'}{/if}
            {if $Data.video_autoplay}{$options[] = 'autoplay=1'}{/if}
            {if $Data.video_related}{$options[] = 'rel=0'}{/if}
            {if $Data.video_controls}{$options[] = 'controls=0'}{/if}
            {if $Data.video_start}{$options[] = 'start='|cat:$Data.video_start}{/if}
            {if $Data.video_end}{$options[] = 'end='|cat:$Data.video_end}{/if}
            {if $Data.video_info}{$options[] = 'showinfo=0'}{/if}
            {if $Data.video_branding}{$options[] = 'modestbranding=1'}{/if}
            {if $Data.video_loop}{$options[] = 'loop=1&playlist='|cat:$Data.video_id}{/if}
            {if $options|@count > 0}
                {foreach $options as $option}
                    {if $option@first}
                        {$params = "?{$option}"}
                    {else}
                        {$params = "$params&$option"}
                    {/if}
                {/foreach}
            {/if}
            {if $Data.load_video_on_confirmation}
                <div
                    class="emotion--youtube--gdpr"
                    data-videoUrl="https://www.youtube-nocookie.com/embed/{$Data.video_id|escape}{if $params}{$params}{/if}" style="background-image: url({$Data.preview_image});">
                    <div class="emotion--youtube--gdpr--inner center">
                        <p>{s name="PrivacyNotice" namespace="widgets/emotion/components/component_youtube"}By viewing the video you agree that your data will be transferred to YouTube and that you have read the Privacy policy.{/s}</p>
                        <a class="gdpr--view--button btn is--secondary">{s name="AcceptButtonLabel" namespace="widgets/emotion/components/component_youtube"}Accept{/s}</a>
                    </div>
                </div>
            {else}
                <iframe class="external--content content--youtube"
                        width="100%"
                        height="100%"
                        src="https://www.youtube-nocookie.com/embed/{$Data.video_id|escape}{if $params}{$params}{/if}"
                        frameborder="0"
                        allowfullscreen>
                </iframe>
            {/if}
        {/if}
    </div>
{/block}
