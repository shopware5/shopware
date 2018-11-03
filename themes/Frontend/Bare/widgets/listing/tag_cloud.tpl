{if $sCloud}
    <div class="panel has--border is--rounded tagcloud--content">
        <div class="panel--body is--wide tagcloud">
            {foreach from=$sCloud item=sCloudItem}
                <a href="{$sCloudItem.link}" title="{$sCloudItem.name|escape}" class="{$sCloudItem.class}">
                    {$sCloudItem.name|truncate:15:"":false}
                </a>
            {/foreach}
        </div>
    </div>
{/if}
