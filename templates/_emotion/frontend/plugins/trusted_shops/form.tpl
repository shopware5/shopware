
{if !$sUserData.additional.charge_vat}
	{assign var="sRealAmount" value=$sAmountNet|replace:",":"."}
{else}
	{if $sAmountWithTax}
		{assign var="sRealAmount" value=$sAmountWithTax|replace:",":"."}
	{else}
		{assign var="sRealAmount" value=$sAmount|replace:",":"."}
	{/if}
{/if}
{if {config name=TSID}}
    <div class="trustedshops_form">
        <div class="grid_3">
            <form name="formSiegel" method="post" action="https://www.trustedshops.com/shop/certificate.php" target="_blank">
                <input type="image" src="{link file='templates/_default/frontend/_resources/images/logo_trusted_shop.gif'}" title="{s name='WidgetsTrustedShopsHeadline'}{/s}" />
                <input name="shop_id" type="hidden" value="{config name=TSID}" />
            </form>
        </div>
        <div class="grid_11">
            <form id="formTShops" name="formTShops" method="post" action="https://www.trustedshops.com/shop/protection.php" target="_blank">
                <input name="_charset_" type="hidden" value="{encoding}">
                <input name="shop_id" type="hidden" value="{config name=TSID}">
                <input name="email" type="hidden" value="{$sUserData.additional.user.email}">
                <input name="amount" type="hidden" value="{$sRealAmount}">
                <input name="curr" type="hidden" value="{config name=currency}">

                {* Payment type *}
                {*  <input name="paymentType" type="hidden" value="{ value paymentType}"> *}
                <input name="kdnr" type="hidden" value="{$sUserData.billingaddress.customernumber}">
                <input name="ordernr" type="hidden" value="{$sOrderNumber}">


                {* Descriptiontext *}
                <p>
                    {se name='WidgetsTrustedShopsText' class='actions'}{/se}
                </p>

                <input type="submit" class="button-right small" name="btnProtect" value="{s name='WidgetsTrustedShopsInfo'}{/s}" />
            </form>
        </div>
        <div class="clear">&nbsp;</div>
    </div>
{/if}
