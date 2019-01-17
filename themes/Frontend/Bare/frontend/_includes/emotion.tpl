<div class="emotion--wrapper" style="display: none"
     data-controllerUrl="{url module=widgets controller=emotion action=index emotionId=$emotion.id controllerName=$Controller}"
     data-availableDevices="{$emotion.devices}"
     data-ajax="{if $theme.ajaxEmotionLoading}true{else}false{/if}"
     {if isset($showListing)} data-showListing="{if $showListing == 1}true{else}false{/if}"{/if}{block name="frontend_emotion_include_attributes"}{/block}>
     {if !$theme.ajaxEmotionLoading}
         {action module=widgets controller=emotion action=index emotionId=$emotion.id controllerName=$Controller}
     {/if}
</div>