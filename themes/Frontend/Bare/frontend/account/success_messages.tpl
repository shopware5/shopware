{if $sSuccessAction}
    {$successText=''}
    {if $sSuccessAction == 'address'}
        {s name="AccountAddressSuccess" assign="successText"}{/s}
    {elseif $sSuccessAction == 'payment'}
        {s name="AccountPaymentSuccess" assign="successText"}{/s}
    {elseif $sSuccessAction == 'account'}
        {s name="AccountAccountSuccess" assign="successText"}{/s}
    {elseif $sSuccessAction == 'newsletter'}
        {s name="AccountNewsletterSuccess" assign="successText"}{/s}
    {elseif $sSuccessAction == 'optinnewsletter'}
        {s name="sMailConfirmation" namespace="frontend" assign="successText"}{/s}
    {elseif $sSuccessAction == 'deletenewsletter'}
        {s name="NewsletterMailDeleted" namespace="frontend/account/internalMessages" assign="successText"}{/s}
    {elseif $sSuccessAction == 'resetPassword'}
        {s name="PasswordResetNewSuccess" namespace="frontend/account/reset_password" assign="successText"}{/s}
    {/if}

    <div class="account--success">
        {include file="frontend/_includes/messages.tpl" type="success" content=$successText}
    </div>
{/if}