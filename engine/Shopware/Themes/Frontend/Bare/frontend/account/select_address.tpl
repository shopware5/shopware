<div class="account--billing-address account--box panel has--border">
	
	{block name="frontend_account_select_billing_address"}
		<h5 class="panel--title is--underline">{$key+1}. {if $sAddress.company}{$sAddress.company},{/if} {$sAddress.firstname} {$sAddress.lastname}</h5>

		<div class="panel--body is--wide">
			{if $sAddress.company}
				<strong>
					{$sAddress.company}{if $sAddress.department} - {$sAddress.department}{/if}
				</strong>
			{/if}
			<p>
				{if $sAddress.salutation eq "mr"}
					{s name="SelectAddressSalutationMr"}{/s}
				{else}
					{s name="SelectAddressSalutationMs"}{/s}
				{/if}
				{$sAddress.firstname} {$sAddress.lastname}<br />
				{$sAddress.street} {$sAddress.streetnumber}<br />
				{if $sAddress.additional_address_line1}{$sAddress.additional_address_line1}<br />{/if}
				{if $sAddress.additional_address_line2}{$sAddress.additional_address_line2}<br />{/if}
				{$sAddress.zipcode} {$sAddress.city}<br />
				{if $sUserData.additional.stateShipping.name}{$sUserData.additional.stateShipping.name}<br />{/if}
				{$sAddress.countryname}<br />
			</p>
		</div>

		<div class="panel--actions is--wide">
			<input type="submit" class="btn btn--secondary is--small" value="{s name='SelectAddressSubmit'}{/s}" />
		</div>
	{/block}

</div>