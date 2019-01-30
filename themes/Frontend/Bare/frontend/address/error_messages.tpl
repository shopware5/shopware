{namespace name="frontend/address/index"}
<div class="account--error">
    {$message = ''}
    {if $type == 'delete'}
        {s name="AddressesDeleteErrorMessage" assign="message"}{/s}
    {/if}

    {include file="frontend/register/error_message.tpl" error_messages=[$message]}
</div>