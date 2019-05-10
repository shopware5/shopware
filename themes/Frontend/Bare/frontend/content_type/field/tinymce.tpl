<div class="blog--box-description-short">
    {if $sAction === 'index'}
        {$content|strip_tags|truncate:220}
    {else}
        {$content}
    {/if}
</div>
