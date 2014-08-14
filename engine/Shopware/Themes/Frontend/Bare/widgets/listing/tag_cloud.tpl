{if $sCloud}
    <div class="panel has--border">
         <div class="panel--title is--underline">
            {s name="TagcloudHead" namespace="frontend/plugins/index/tagcloud"}{/s}
         </div>
        <div class="panel--body is--wide tagcloud">
            {foreach from=$sCloud item=sCloudItem}
                <a href="{$sCloudItem.link|rewrite:$sCloudItem.name}" title="{$sCloudItem.name|escape}" class="{$sCloudItem.class}">
                    {$sCloudItem.name|truncate:15:"":false}
                </a>
            {/foreach}
        </div>
    </div>
{/if}