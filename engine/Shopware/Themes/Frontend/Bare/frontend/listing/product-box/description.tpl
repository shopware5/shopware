<p class="product--description">
    {if $sTemplate}
        {$sArticle.description_long|strip_tags|truncate:$size}
    {/if}
</p>