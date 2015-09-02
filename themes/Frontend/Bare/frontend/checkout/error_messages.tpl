{* Basket informations *}
{block name='frontend_checkout_error_messages_basket_error'}
	{if $sBasketInfo}
		{include file="frontend/_includes/messages.tpl" type="error" content=$sBasketInfo}
	{/if}
{/block}

{block name='frontend_checkout_error_messages_voucher_error'}
	{* Voucher error *}
	{if $sVoucherError}
		{include file="frontend/_includes/messages.tpl" type="error" content=$sVoucherError[0]}
	{/if}
{/block}

{block name="frontend_checkout_error_messages_esd_note"}
	{if $sShowEsdNote}
		{include file="frontend/_includes/messages.tpl" type="warning" content="{s name='ConfirmInfoPaymentNotCompatibleWithESD'}{/s}"}
	{/if}
{/block}

{block name='frontend_checkout_error_messages_no_shipping'}
	{if $sDispatchNoOrder}
		{include file="frontend/_includes/messages.tpl" type="warning" content="{s name='ConfirmInfoNoDispatch'}{/s}"}
	{/if}
{/block}
	
{* Minimum sum not reached *}
{block name='frontend_checkout_error_messages_minimum_not_reached'}
	{if $sMinimumSurcharge}
		{include file="frontend/_includes/messages.tpl" type="error" content="{s name='ConfirmInfoMinimumSurcharge'}{/s}"}
	{/if}
{/block}

{* Service article tos not accepted *}
{block name='frontend_checkout_error_messages_service_error'}
    {if $agreementErrors && $agreementErrors.serviceError}
        {include file="frontend/_includes/messages.tpl" type="error" content="{s name="ServiceErrorMessage"}{/s}"}
    {/if}
{/block}

{* ESD article tos not accepted *}
{block name='frontend_checkout_error_messages_esd_error'}
    {if $agreementErrors && $agreementErrors.esdError && {config name="showEsdWarning"}}
        {include file="frontend/_includes/messages.tpl" type="error" content="{s name="EsdErrorMessage"}{/s}"}
    {/if}
{/block}
