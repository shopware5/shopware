{* Basket informations *}
{block name='frontend_checkout_error_messages_basket_error'}
{if $sBasketInfo}
	{include file="frontend/_includes/messages.tpl" type="error" content=$sBasketInfo}
	<div class="error center bold">
		{$sBasketInfo}
	</div>
{/if}
{/block}

{block name='frontend_checkout_error_messages_voucher_error'}
	{* Voucher error *}
	{if $sVoucherError}
		{include file="frontend/_includes/messages.tpl" type="error" list=$sVoucherError}
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