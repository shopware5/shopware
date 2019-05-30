<div class="product--notification">
    <input type="hidden" value="{$NotifyHideBasket}" name="notifyHideBasket" id="notifyHideBasket" />

    {if $NotifyValid == true}
        {$messageType="success"}
        {s name="DetailNotifyInfoValid" assign="messageContent"}{/s}
    {elseif $NotifyInvalid == true && $NotifyAlreadyRegistered != true}
        {$messageType="warning"}
        {s name="DetailNotifyInfoInvalid" assign="messageContent"}{/s}
    {elseif $NotifyEmailError == true}
        {$messageType="error"}
        {s name="DetailNotifyInfoErrorMail" assign="messageContent"}{/s}
    {elseif $WaitingForOptInApprovement}
        {$messageType="success"}
        {s name="DetailNotifyInfoSuccess" assign="messageContent"}{/s}
    {elseif $NotifyAlreadyRegistered == true}
        {$messageType="warning"}
        {s name="DetailNotifyAlreadyRegistered" assign="messageContent"}{/s}
    {else}
        {if $NotifyValid != true}
            {$messageType="warning"}
            {s name="DetailNotifyHeader" assign="messageContent"}{/s}
        {/if}
    {/if}

    {* Include the message template component *}
    {include file="frontend/_includes/messages.tpl" type=$messageType content=$messageContent}

    {block name="frontend_detail_index_notification_form"}
        {if !$NotifyAlreadyRegistered}
            <form method="post" action="{url action='notify' sArticle=$sArticle.articleID number=$sArticle.ordernumber}" class="notification--form block-group">
                <input type="hidden" name="notifyOrdernumber" value="{$sArticle.ordernumber}" />
                {block name="frontend_detail_index_notification_field"}
                    <input name="sNotificationEmail" type="email" class="notification--field block" placeholder="{s name='DetailNotifyLabelMail'}{/s}" />
                {/block}

                {block name="frontend_detail_index_notification_button"}
                    <button type="submit" class="notification--button btn is--center block">
                        <i class="icon--mail"></i>
                    </button>
                {/block}

                {* Data protection information *}
                {block name="frontend_detail_index_notification_privacy"}
                    {if {config name=ACTDPRTEXT} || {config name=ACTDPRCHECK}}
                        {include file="frontend/_includes/privacy.tpl"}
                    {/if}
                {/block}
            </form>
        {/if}
    {/block}
</div>