{namespace name="frontend/address/index"}
<div class="account--success">
    {$content = ''}
    {if $type == 'save'}
        {$content = "{s name='AddressesSuccess'}{/s}"}
    {elseif $type == 'delete'}
        {$content = "{s name='AddressesDeleteSuccess'}{/s}"}
    {/if}

    {include file="frontend/_includes/messages.tpl" type="success" content="{$content}"}
</div>