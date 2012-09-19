{extends file='parent:frontend/account/billing.tpl'}

{block name="frontend_account_error_messages"}
    <h1>{se name='BillingHeadline'}Rechnungsadresse ändern{/se}</h1>
    {include file="frontend/register/error_message.tpl" error_messages=$sErrorMessages}
{/block}