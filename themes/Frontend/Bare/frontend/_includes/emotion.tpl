{block name="frontend_includes_emotion"}
    <div class="emotion--wrapper" style="display: none"
         data-controllerUrl="{url module=widgets controller=emotion action=index emotionId=$emotion.id secret=$previewSecret controllerName=$Controller}"
         data-availableDevices="{$emotion.devices}"
         data-ajax="{if $theme.ajaxEmotionLoading}true{else}false{/if}"
         {if isset($showListing)} data-showListing="{if $showListing == 1}true{else}false{/if}"{/if}{block name="frontend_emotion_include_attributes"}{/block}>
        {block name="frontend_includes_emotion_inner"}
            {if !$theme.ajaxEmotionLoading}
                {block name="frontend_includes_emotion_template"}
                    <template style="display: none">
                        {block name="frontend_includes_emotion_template_inner"}
                            {action module=widgets controller=emotion action=index emotionId=$emotion.id secret=$previewSecret controllerName=$Controller}
                        {/block}
                    </template>
                {/block}
            {/if}
        {/block}
    </div>
{/block}
