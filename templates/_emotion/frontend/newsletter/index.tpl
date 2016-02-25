{extends file="frontend/index/index.tpl"}

{* Breadcrumb *}
{block name='frontend_index_start' append}
	{$sBreadcrumb = [['name'=>"{s name='NewsletterTitle'}{/s}", 'link'=>{url}]]}
{/block}

{block name="frontend_index_content"}
	<div class="grid_16 tellafriend custom" id="center">
		<div class="col_center_custom">

			<h1>{s name=NewsletterRegisterHeadline}{/s}</h1>

			<p>{s name=sNewsletterInfo}{/s}</p>

		</div>
		{if $sStatus.code==3||$sStatus.code==2}
			<div class="success bold">
				{$sStatus.message}
			</div>
		{elseif $sStatus.code != 0}
			<div class="error bold">
				{$sStatus.message}
			</div>
		{/if}

		{if $voteConfirmed == false || $sStatus.code == 0}
			<div class="contact_box register">
				<h2 class="headingbox_dark largesize">{s name=NewsletterRegisterHeadline}{/s}</h2>

				<form action="{url controller='newsletter'}" method="post" id="letterForm">
					<fieldset>
						<div>
							<label>{s name=NewsletterLabelSelect}{/s}</label>
							<select id="chkmail" name="subscribeToNewsletter" class="text" onchange="refreshAction();">
								<option value="1">{s name=sNewsletterOptionSubscribe}{/s}</option>
								<option value="-1"
										{if $_POST.subscribeToNewsletter eq -1 || (!$_POST.subscribeToNewsletter && $sUnsubscribe == true)}selected{/if}>{s name=sNewsletterOptionUnsubscribe}{/s}</option>
							</select>
						</div>
						<div>
							<label for="newsletter">{s name=sNewsletterLabelMail}{/s}</label>
							<input name="newsletter" type="text" id="newsletter"
								   value="{if $_POST.newsletter}{$_POST.newsletter}{elseif $_GET.sNewsletter}{$_GET.sNewsletter|escape}{/if}"
								   class="text {if $sStatus.sErrorFlag.newsletter}instyle_error{/if}"/>
						</div>
						{if {config name=NewsletterExtendedFields}}
							<div id="sAdditionalForm">
								<div>
									<label for="salutation" class="normal">{s name=NewsletterRegisterLabelSalutation}{/s}</label>
									<select name="salutation" id="salutation"
											class="text{if $sStatus.sErrorFlag.salutation} instyle_error{/if}">
										<option value="">{s name=NewsletterRegisterPleaseChoose}{/s}</option>
										<option value="mr"
												{if $_POST.salutation eq "mr"}selected{/if}>{s name=NewsletterRegisterLabelMr}{/s}</option>
										<option value="ms"
												{if $_POST.salutation eq "ms"}selected{/if}>{s name=NewsletterRegisterLabelMs}{/s}</option>
									</select>
								</div>

								<div>
									<label for="firstname" class="normal">{s name=NewsletterRegisterLabelFirstname}{/s}</label>
									<input name="firstname" type="text" id="firstname" value="{$_POST.firstname|escape}"
										   class="text {if $sStatus.sErrorFlag.firstname}instyle_error{/if}"/>
								</div>

								<div>
									<label for="lastname" class="normal">{s name=NewsletterRegisterLabelLastname}{/s}</label>
									<input name="lastname" type="text" id="lastname" value="{$_POST.lastname|escape}"
										   class="text {if $sStatus.sErrorFlag.lastname}instyle_error{/if}"/>
								</div>

								<div>
									<label for="street" class="normal">{s name=NewsletterRegisterBillingLabelStreet}{/s}</label>
									<input name="street" type="text" id="street" value="{$_POST.street|escape}"
										   class="text {if $sStatus.sErrorFlag.street}instyle_error{/if}"/>
								</div>

								<div>
									<label for="zipcode" class="normal">{s name=NewsletterRegisterBillingLabelCity}{/s}</label>
									<input name="zipcode" type="text" id="zipcode" value="{$_POST.zipcode|escape}"
										   class="zipcode text {if $sStatus.sErrorFlag.zipcode}instyle_error{/if}"/>
									<input name="city" type="text" id="city" value="{$_POST.city|escape}" size="25"
										   class="city text {if $sStatus.sErrorFlag.city}instyle_error{/if}"/>
								</div>
							</div>
							{* @TODO - Move to a javascript file *}
							{literal}
								<script type="text/javascript">
									function refreshAction() {
										if ($('#chkmail').val() == -1) {
											$('#sAdditionalForm').hide();
										}
										else {
											$('#sAdditionalForm').show();
										}
									}
									refreshAction();
								</script>
							{/literal}
						{/if}
						<div class="clear">&nbsp;</div>

						{* Required fields hint *}
						<div class="required_fields">
							{s name='RegisterPersonalRequiredText' namespace='frontend/register/personal_fieldset'}{/s}
						</div>

						<input type="submit" value="{s name=sNewsletterButton}{/s}" class="button-right large"/>
					</fieldset>
				</form>
				<div class="clear"></div>
			</div>
		{/if}
	</div>
{/block}