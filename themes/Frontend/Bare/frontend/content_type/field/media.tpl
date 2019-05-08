<a href="{url action=detail id=$sItem.id}">
    {if !empty($content.thumbnails)}
        <img srcset="{$content.thumbnails[0].sourceSet}"
             alt="{$sArticle.title|escape}"
             title="{$sArticle.title|escape|truncate:160}" />
    {else}
        <img src="{$content.source}"
             alt="{$sArticle.title|escape}"
             title="{$sArticle.title|escape|truncate:160}" />
    {/if}
</a>
