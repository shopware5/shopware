<div id="article_notification">
    <input type="hidden" value="{$NotifyHideBasket}" name="notifyHideBasket" id="notifyHideBasket" />


	{if $NotifyValid == true}
		<div class="success">
			{se name='DetailNotifyInfoValid'}{/se}
		</div>
	{elseif $NotifyInvalid == true && $NotifyAlreadyRegistered != true}
		<div class="notice">
			{se name='DetailNotifyInfoInvalid'}{/se}
		</div>
    {elseif $NotifyEmailError == true}
        <div class="error">
            {se name='DetailNotifyInfoErrorMail'}{/se}
        </div>
	{elseif $WaitingForOptInApprovement}
		<div id="articleNotificationWasSend" class="displaynone">
			<div class="success">
				{se name='DetailNotifyInfoSuccess'}{/se}
			</div>
		</div>
    {elseif $NotifyAlreadyRegistered == true}
        <div class="success">
            <div class="center">
                <strong>
                    {se name='DetailNotifyAlreadyRegistered'}{/se}
                </strong>
            </div>
        </div>
    {else}
        {if $NotifyValid != true}
        <div class="notice">
        <div class="center">
                <strong>
                    {se name='DetailNotifyHeader'}{/se}
                </strong>
            </div>
        </div>
        {/if}
    {/if}
    <form method="post" action="{url action='notify' sArticle=$sArticle.articleID}" id="sendArticleNotification">
		<input type="hidden" name="notifyOrdernumber" value="{$sArticle.ordernumber}" id="variantOrdernumber" />
		<fieldset>
			
			<div>
				<label>{se name='DetailNotifyLabelMail'}{/se}</label>
				<input name="sNotificationEmail" type="text" id="txtmail" class="text" />
				
				<div class="clear">&nbsp;</div>
				
				<input type="submit"  value="{s name='DetailNotifyActionSubmit'}{/s}" class="button-right small_right" />
			</div>
		</fieldset>
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