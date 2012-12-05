{extends file='parent:frontend/account/shipping.tpl'}

{block name='frontend_account_shipping_error_messages'}
    <h1>{se name='ShippingHeadline'}Lieferadresse ändern{/se}</h1>
    {include file="frontend/register/error_message.tpl" error_messages=$sErrorMessages}
{/block}