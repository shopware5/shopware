{extends file='frontend/index/index.tpl'}

{block name="frontend_index_content_left"}{/block}

{* Breadcrumb *}
{block name='frontend_index_start' append}
    {assign var='sBreadcrumb' value=[['name'=>"{s name='ResetPassword'}Passwort zurücksetzen{/s}", 'link' =>{url action='resetPassword'}]]}
{/block}

{* Error messages *}
{block name='frontend_account_index_error_messages'}
    {if $sErrorMessages}
        <div class="grid_16 error_msg">
            {include file="frontend/register/error_message.tpl" error_messages=$sErrorMessages}
        </div>
    {/if}
{/block}


{* Empty sidebar left *}
{block name='frontend_index_content_left'}{/block}

{* Main content *}
{block name='frontend_index_content'}
    <div class="grid_20 password">

        {* Error messages *}
        {block name='frontend_account_error_messages'}
            {include file="frontend/register/error_message.tpl" error_messages=$sErrorMessages}
        {/block}

        {block name="frontend_account_reset_password"}
            {if !$success}
                <form method="post" action="{url action=resetPassword}">
                    <h2 class="headingbox_dark largesize">{s name='ResetPassword'}Passwort zurücksetzen{/s}</h2>
                    <div class="outer">
                        <p>{s name='PasswordResetNewHelpText'}{/s}</p>

                        <p>
                            <input name="hash" value="{$hash}" type="hidden" id="hash" class="text {if $sErrorFlag.hash}instyle_error{/if}" />
                        </p>

                        {* New password *}
                        <p>
                            <label for="newpwd">{s namespace="frontend/account/index" name="AccountLabelNewPassword"}{/s}</label>
                            <input name="password" type="password" id="newpwd" class="text {if $sErrorFlag.password}instyle_error{/if}" />
                        </p>

                        {* Repeat new Password *}
                        <p>
                            <label for="newpwdrepeat">{s namespace="frontend/account/index" name="AccountLabelRepeatPassword"}{/s}</label>
                            <input name="passwordConfirmation" id="newpwdrepeat" type="password" class="text {if $sErrorFlag.passwordConfirmation}instyle_error{/if}" />
                        </p>

                        <p class="buttons">
                            <input type="submit" value="{s namespace="frontend/account/index" name='AccountLinkChangePassword'}{/s}" class="button-right large" />
                        </p>
                    </div>
                </form>
            {/if}
        {/block}

    </div>
    <div class="doublespace">&nbsp;</div>
{/block}