{namespace name="frontend/address/index"}
<div class="account--success">
    {$content = ''}
    {if $type == 'create'}
        {s name="AddressesCreateSuccess" assign="content"}{/s}
    {elseif $type == 'default_billing'}
        {s name="AddressesSetDefaultBillingSuccess" assign="content"}{/s}
    {elseif $type == 'default_shipping'}
        {s name="AddressesSetDefaultShippingSuccess" assign="content"}{/s}
    {elseif $type == 'update'}
        {s name="AddressesUpdateSuccess" assign="content"}{/s}
    {elseif $type == 'delete'}
        {s name="AddressesDeleteSuccess" assign="content"}{/s}
    {/if}

    {* for your custom messages *}
    {block name="frontend_address_success_messages_content"}{/block}

    {include file="frontend/_includes/messages.tpl" type="success" content=$content}
</div>
