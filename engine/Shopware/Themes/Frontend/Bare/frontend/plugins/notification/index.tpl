<div class="product--notification">
    <input type="hidden" value="{$NotifyHideBasket}" name="notifyHideBasket" id="notifyHideBasket" />

	{if $NotifyValid == true}
		<div class="alert success">
			{s name='DetailNotifyInfoValid'}{/s}
		</div>
	{elseif $NotifyInvalid == true && $NotifyAlreadyRegistered != true}
		<div class="alert warning">
			{s name='DetailNotifyInfoInvalid'}{/s}
		</div>
    {elseif $NotifyEmailError == true}
        <div class="alert error">
            {s name='DetailNotifyInfoErrorMail'}{/s}
        </div>
	{elseif $WaitingForOptInApprovement}
		<div class="alert success">
			{s name='DetailNotifyInfoSuccess'}{/s}
		</div>
    {elseif $NotifyAlreadyRegistered == true}
        <div class="alert info">
			{s name='DetailNotifyAlreadyRegistered'}{/s}
        </div>
    {else}
        {if $NotifyValid != true}
        <div class="alert warning">
			{s name='DetailNotifyHeader'}{/s}
        </div>
        {/if}
    {/if}
	{block name="frontend_detail_index_notification_form"}
		<form method="post" action="{url action='notify' sArticle=$sArticle.articleID}" class="notification--form block-group">
			<input type="hidden" name="notifyOrdernumber" value="{$sArticle.ordernumber}" />
			{block name="frontend_detail_index_notification_field"}
				<input name="sNotificationEmail" type="email" class="notification--field block" placeholder="{s name='DetailNotifyLabelMail'}{/s}" />
			{/block}

			{block name="frontend_detail_index_notification_button"}
				<button type="submit" class="btn btn--primary notification--button block">
					<i class="icon--mail"></i>
				</button>
			{/block}
		</form>
	{/block}
</div>