
<div class="select_billing grid_6">
	
	{block name="frontend_account_select_billing_address"}	
	<h5 class="bold">{$key+1}. {if $sAddress.company}{$sAddress.company},{/if}{$sAddress.firstname} {$sAddress.lastname}</h5>
	
	{if $sAddress.company}
		<p>
	    	{$sAddress.company}{if $sAddress.department} - {$sAddress.department}{/if}
		</p>
	{/if}
    <p>
        {if $sAddress.salutation eq "mr"}
        	{se name="SelectAddressSalutationMr"}{/se}
        {else}
        	{se name="SelectAddressSalutationMs"}{/se}
        {/if}
        {$sAddress.firstname} {$sAddress.lastname}<br />
        {$sAddress.street}<br />
		{if $sAddress.additional_address_line1}{$sAddress.additional_address_line1}<br />{/if}
		{if $sAddress.additional_address_line2}{$sAddress.additional_address_line2}<br />{/if}
        {$sAddress.zipcode} {$sAddress.city}<br />
		{if {$sAddress.statename}}{$sAddress.statename}<br />{/if}
        {$sAddress.countryname}<br />
	</p>
	<div class="change">
		<input type="submit" class="button-right small" value="{s name='SelectAddressSubmit'}{/s}" />
	</div>
	{/block}
</div>
