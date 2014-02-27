{if $sCloud}
    <h2 class="headline">{s name="TagcloudHead" namespace="frontend/plugins/index/tagcloud"}{/s}</h2>
    <section class="tagcloud">
        {foreach from=$sCloud item=sCloudItem}
            <a href="{$sCloudItem.link|rewrite:$sCloudItem.name}" title="{$sCloudItem.name}" class="{$sCloudItem.class}">
                {$sCloudItem.name|truncate:15:"":false}
            </a>
        {/foreach}
    </section>
{/if}