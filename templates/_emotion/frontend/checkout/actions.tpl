
<div class="actions">
    {* Contiune shopping *}
    {if $sBasket.sLastActiveArticle.link}
    	{block name="frontend_checkout_actions_link_last"}
    	 <a href="{$sBasket.sLastActiveArticle.link}" title="{s name='CheckoutActionsLinkLast'}{/s}" class="button-left large">
    	 	{se name="CheckoutActionsLinkLast"}{/se}
    	 </a>
    	 {/block}
    {/if}	
    
    {if !$sMinimumSurcharge && ($sInquiry || $sDispatchNoOrder)}
    	{block name="frontend_checkout_actions_inquiry"}
		<a href="{$sInquiryLink}" title="{s name='CheckoutActionsLinkOffer'}{/s}" class="button-middle large">
			{se name="CheckoutActionsLinkOffer"}{/se}
		</a>
		{/block}
	{/if}
	
	{* Checkout *}
	{if !$sMinimumSurcharge && !$sDispatchNoOrder}
		{block name="frontend_checkout_actions_confirm"}
        <a href="{if {config name=always_select_payment}}{url controller='checkout' action='shippingPayment'}{else}{url controller='checkout' action='confirm'}{/if}" title="{s name='CheckoutActionsLinkProceed'}{/s}" class="button-right large right checkout" >
			{se name="CheckoutActionsLinkProceed"}{/se}
        </a>
        {/block}
    {/if}
		
	<div class="clear">&nbsp;</div>
</div>
