<div class="account--billing-address panel has--border is--rounded">
	
	{block name="frontend_account_select_billing_address"}
		{block name="frontend_account_select_headline"}
			<h5 class="panel--title is--underline">{$key+1}. {if $sAddress.company}{$sAddress.company},{/if} {$sAddress.firstname} {$sAddress.lastname}</h5>
		{/block}

		{block name="frontend_account_select_content"}
			<div class="panel--body is--wide">
				{if $sAddress.company}
					{block name="frontend_account_select_company_address"}
						<p class="is--bold">
							{$sAddress.company}{if $sAddress.department} - {$sAddress.department}{/if}
						</p>
					{/block}
				{/if}
				{block name="frontend_account_select_address_information"}
					<p>
						{if $sAddress.salutation eq "mr"}
							{s name="SelectAddressSalutationMr"}{/s}
						{else}
							{s name="SelectAddressSalutationMs"}{/s}
						{/if}
						{$sAddress.firstname} {$sAddress.lastname}<br />
						{$sAddress.street}<br />
						{if $sAddress.additional_address_line1}{$sAddress.additional_address_line1}<br />{/if}
						{if $sAddress.additional_address_line2}{$sAddress.additional_address_line2}<br />{/if}
                        {if {config name=showZipBeforeCity}}{$sAddress.zipcode} {$sAddress.city}{else}{$sAddress.city} {$sAddress.zipcode}{/if}<br />
						{if $sUserData.additional.stateShipping.name}{$sUserData.additional.stateShipping.name}<br />{/if}
						{$sAddress.countryname}<br />
					</p>
				{/block}
			</div>
		{/block}

		{block name="frontend_account_select_actions"}
			<div class="panel--actions is--wide">
				<input type="submit" class="btn is--primary is--small" value="{s name="SelectAddressSubmit"}{/s}" />
			</div>
		{/block}

	{/block}

</div>