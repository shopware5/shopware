{extends file="frontend/index/index.tpl"}

{* Breadcrumb *}
{block name='frontend_index_start' append}
	{$sBreadcrumb = [['name'=>"{s name='NewsletterTitle'}{/s}", 'link'=>{url}]]}
{/block}

{block name="frontend_index_content"}
	<div class="newsletter--content c">
		<div class="newsletter--headline panel--body is--wide">

			<h1>{s name="NewsletterRegisterHeadline"}{/s}</h1>
			<p>{s name="sNewsletterInfo"}{/s}</p>

		</div>
		{if $sStatus.code==3||$sStatus.code==2}
			{include file="frontend/_includes/messages.tpl" type='success' content=$sStatus.message}
		{elseif $sStatus.code != 0}
			{include file="frontend/_includes/messages.tpl" type='error' content=$sStatus.message}
		{/if}

		{if $voteConfirmed == false || $sStatus.code == 0}
			<div class="newsletter--form panel has--border">
				<h1 class="panel--title is--underline">{s name="NewsletterRegisterHeadline"}{/s}</h1>

				<form action="{url controller='newsletter'}" method="post" id="letterForm">
					<div class="panel--body is--wide">

						<div class="newsletter--subscription">
							<select name="subscribeToNewsletter" required="required" class="field--select" onchange="refreshAction();">
								<option value="1">{s name="sNewsletterOptionSubscribe"}{/s}</option>
								<option value="-1" {if $_POST.subscribeToNewsletter eq -1 || (!$_POST.subscribeToNewsletter && $sUnsubscribe == true)}selected{/if}>{s name="sNewsletterOptionUnsubscribe"}{/s}</option>
							</select>
						</div>

						<div class="newsletter--email">
							<input name="newsletter" type="text" placeholder="{s name="sNewsletterLabelMail"}{/s}" required="required" aria-required="true" id="newsletter" value="{if $_POST.newsletter}{$_POST.newsletter}{elseif $_GET.sNewsletter}{$_GET.sNewsletter|escape}{/if}" class="newsletter--field is--required{if $sStatus.sErrorFlag.newsletter} has--error{/if}"/>
						</div>

						{if {config name=NewsletterExtendedFields}}
							<div id="sAdditionalForm">
								<div class="newsletter--salutation">
									<select name="salutation" id="salutation" required="required" class="field--select{if $sStatus.sErrorFlag.salutation} has--error{/if}">
										<option value="">{s name="NewsletterRegisterPleaseChoose"}{/s}</option>
										<option value="mr" {if $_POST.salutation eq "mr"}selected{/if}>{s name="NewsletterRegisterLabelMr"}{/s}</option>
										<option value="ms" {if $_POST.salutation eq "ms"}selected{/if}>{s name="NewsletterRegisterLabelMs"}{/s}</option>
									</select>
								</div>

								<div class="newsletter--firstname">
									<input name="firstname" type="text" placeholder="{s name="NewsletterRegisterLabelFirstname"}{/s}" id="firstname" value="{$_POST.firstname|escape}" class="newsletter--field{if $sStatus.sErrorFlag.firstname} has--error{/if}"/>
								</div>

								<div class="newsletter--lastname">
									<input name="lastname" type="text" placeholder="{s name="NewsletterRegisterLabelLastname"}{/s}" id="lastname" value="{$_POST.lastname|escape}" class="newsletter--field{if $sStatus.sErrorFlag.lastname} has--error{/if}"/>
								</div>

								<div class="newsletter--street">
									<input name="street" type="text" placeholder="{s name="NewsletterRegisterBillingLabelStreet"}{/s}" id="street" value="{$_POST.street|escape}" class="newsletter--field newsletter--field-street{if $sStatus.sErrorFlag.street} has--error{/if}"/>
									<input name="streetnumber" type="text" placeholder="" id="streetnumber" value="{$_POST.streetnumber|escape}" class="newsletter--field newsletter--field-streetnumber{if $sStatus.sErrorFlag.streetnumber} has--error{/if}"/>
								</div>

								<div class="newsletter--zip-city">
									<input name="zipcode" type="text" placeholder="{s name="NewsletterRegisterBillingLabelCity"}{/s}" id="zipcode" value="{$_POST.zipcode|escape}" class="newsletter--field newsletter--field-zipcode{if $sStatus.sErrorFlag.zipcode} has--error{/if}"/>
									<input name="city" type="text" id="city" value="{$_POST.city|escape}" size="25" class="newsletter--field newsletter--field-city{if $sStatus.sErrorFlag.city} has--error{/if}"/>
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

						{* Required fields hint *}
						<div class="newsletter--required-info">
							{s name='RegisterPersonalRequiredText' namespace="frontend/register/personal_fieldset"}{/s}
						</div>

						<input type="submit" value="{s name="sNewsletterButton"}{/s}" class="btn btn--primary right"/>

					</div>
				</form>
			</div>
		{/if}
	</div>
{/block}