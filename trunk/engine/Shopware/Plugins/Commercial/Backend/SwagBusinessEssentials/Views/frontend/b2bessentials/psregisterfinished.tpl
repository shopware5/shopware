{extends file="parent:frontend/account/login.tpl"}

{block name='frontend_index_content'}
<div class="grid_20 psregister-finish" id="center">
	<h2 class="headingbox_dark largesize">{s name="PrivateRegisterConfirmHeader"}Registrierung abgeschlossen{/s}</h2>
	
	<div class="inner-container">
		<p class="notice">
			{se name="PrivateRegisterConfirmMessage"}Sie erhalten eine Benachrichtigung per eMail, sobald Ihr Zugang freigegeben wurde!{/se}
		</p>
		
		<div class="space"></div>
		
		<p class="action">
			<a href="{url controller='PrivateLogin' action='index'}" class="button-left large">
				{s name="PrivateRegisterBackLink"}Zurück{/s}
			</a>
		</p>
	</div>
</div>
{/block}