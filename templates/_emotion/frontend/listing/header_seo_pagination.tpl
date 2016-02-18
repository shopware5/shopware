{* Previous rel tag *}
{if $sPage > 1}
    {$sCategoryContent.canonicalParams.sPage = $sPage - 1}
    <link rel="prev" href="{url params = $sCategoryContent.canonicalParams}">
{/if}

{* Next rel tag *}
{if $pages >= $sPage + 1}
    {$sCategoryContent.canonicalParams.sPage = $sPage + 1}
    <link rel="next" href="{url params = $sCategoryContent.canonicalParams}">
{/if}