{extends file='parent:frontend/account/index.tpl'}

	{* Breadcrumb *}
	{block name='frontend_index_breadcrumb'}
		<div id="breadcrumb" class="account">
			{se name='AccountHeaderWelcome'}{/se}, <strong style="font-weight:bold;">{$sUserData.billingaddress.firstname} {$sUserData.billingaddress.lastname}</strong>
		</div>
	{/block}

	{* General user informations *}
	{block name="frontend_account_index_info"}
	<div id="userinformations" class="grid_8 first">
		<h2 class="headingbox_dark largesize">{se name="AccountHeaderBasic"}{/se}</h2>
		<div class="inner_container">
			<p>
				{$sUserData.billingaddress.firstname} {$sUserData.billingaddress.lastname}<br />
				{$sUserData.additional.user.email}
			</p>
			<div class="change">
				<a href="#" class="button-middle small change_password">{se name="AccountLinkChangePassword"}{/se}</a>
				<a href="#" class="button-middle small change_mail">{se name='AccountLinkChangeMail'}{/se}</a>
			</div>
		</div>
	</div>
	{/block}
