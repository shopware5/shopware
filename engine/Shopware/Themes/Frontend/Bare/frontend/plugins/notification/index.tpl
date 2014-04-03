<div class="product--notification">
    <input type="hidden" value="{$NotifyHideBasket}" name="notifyHideBasket" id="notifyHideBasket" />

	{if $NotifyValid == true}
		<div class="success">
			{s name='DetailNotifyInfoValid'}{/s}
		</div>
	{elseif $NotifyInvalid == true && $NotifyAlreadyRegistered != true}
		<div class="notice">
			{s name='DetailNotifyInfoInvalid'}{/s}
		</div>
    {elseif $NotifyEmailError == true}
        <div class="error">
            {s name='DetailNotifyInfoErrorMail'}{/s}
        </div>
	{elseif $WaitingForOptInApprovement}
		<div id="articleNotificationWasSend" class="displaynone">
			<div class="success">
				{s name='DetailNotifyInfoSuccess'}{/s}
			</div>
		</div>
    {elseif $NotifyAlreadyRegistered == true}
        <div class="success">
			{s name='DetailNotifyAlreadyRegistered'}{/s}
        </div>
    {else}
        {if $NotifyValid != true}
        <div class="notice">
			{s name='DetailNotifyHeader'}{/s}
        </div>
        {/if}
    {/if}
    <form method="post" action="{url action='notify' sArticle=$sArticle.articleID}" class="notification--form">
		<input type="hidden" name="notifyOrdernumber" value="{$sArticle.ordernumber}" />
		<input name="sNotificationEmail" type="email" class="notification--field" placeholder="{s name='DetailNotifyLabelMail'}{/s}" />
		<button type="submit" class="btn btn--primary notification--button">
			{s name='DetailNotifyActionSubmit'}{/s}
		</button>
	</form>
</div>


<script type="text/javascript">
var variantOrdernumberArray = new Array();
{foreach from=$NotificationVariants item=notify}
	variantOrdernumberArray.push('{$notify}');
{/foreach}
var checkVariant = {if !$sArticle.sVariants}false{else}true{/if};
var checkOrdernumber = '{$sArticle.ordernumber}';
if (checkVariant==false){
	$.checkNotification(checkOrdernumber);
}
$('#sAdd').change(function() {
	$.checkNotification($(this).find(':selected').val())
});

</script>