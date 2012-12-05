{block name='frontend_account_order_item_overview_row'}
{if $pi_klarna_invoice_ids[0]['id']== $offerPosition.paymentID || $pi_klarna_invoice_ids[1]['id']== $offerPosition.paymentID}
    <div class="grid_3" style="vertical-align:middle">
        {$offerPosition.datum|date}
    </div>
    <div class="grid_2 bold">
        {$offerPosition.ordernumber}
    </div>
    <div class="grid_3">
        {if $offerPosition.dispatch.name}
            {$offerPosition.dispatch.name}
        {else}
            {$pi_Klarna_lang['notSet']}
        {/if}
    </div>
    <div class="grid_5">
        <div class="status{$offerPosition.cleared}">&nbsp;</div>
        {if $offerPosition.cleared==0}
            {$pi_Klarna_lang['payment']['check']}
        {elseif $offerPosition.cleared==1}
            {$pi_Klarna_lang['stats']['work']}
        {elseif $offerPosition.cleared==2}
            {$pi_Klarna_lang['stats']['complete']}
        {elseif $offerPosition.cleared==3}
            {$pi_Klarna_lang['stats']['part']}
        {elseif $offerPosition.cleared==4}
            {$pi_Klarna_lang['stats']['cancel']}
        {elseif $offerPosition.cleared==18}
            <div class="status0">&nbsp;</div>{$pi_Klarna_lang['payment']['check']}
        {elseif $offerPosition.cleared==$klarnaStatusIds['pending']}
            <div class="status0">&nbsp;</div>{$pi_Klarna_lang['payment']['check']}
        {elseif $offerPosition.cleared==6}
            {$pi_Klarna_lang['stats']['part']}
        {elseif $offerPosition.cleared== $klarnaStatusIds['accepted'] &&  $offerPosition.status==0}
            <div class="status2">&nbsp;</div>{$pi_Klarna_lang['payment']['accepted']}<br />
            <div class="status4">&nbsp;</div>{$pi_Klarna_lang['stats']['open']}
        {elseif $offerPosition.cleared==$klarnaStatusIds['declined']}
            <div class="status4">&nbsp;</div>{$pi_Klarna_lang['payment']['denied']}<br />
            <div class="status4">&nbsp;</div>{$pi_Klarna_lang['stats']['return']}
        {elseif $offerPosition.cleared==$klarnaStatusIds['accepted']  &&  $offerPosition.status==6}
            <div class="status2">&nbsp;</div>{$pi_Klarna_lang['payment']['accepted']}<br />
            <div class="status0">&nbsp;</div>{$pi_Klarna_lang['stats']['part']}
        {elseif $offerPosition.cleared==$klarnaStatusIds['accepted']  &&  $offerPosition.status==$klarnaStatusIds['completeReturned']}
            <div class="status4">&nbsp;</div>{$pi_Klarna_lang['stats']['complete_return']}
        {elseif $offerPosition.cleared==$klarnaStatusIds['accepted']  &&  $offerPosition.status==7}
            <div class="status2">&nbsp;</div>{$pi_Klarna_lang['payment']['accepted']}<br />
            <div class="status2">&nbsp;</div>{$pi_Klarna_lang['stats']['complete']}
        {elseif $offerPosition.cleared==$klarnaStatusIds['accepted']  &&  $offerPosition.status==$klarnaStatusIds['partReturned']}
            <div class="status2">&nbsp;</div>{$pi_Klarna_lang['payment']['accepted']}<br />
            <div class="status4">&nbsp;</div>{$pi_Klarna_lang['stats']['return']}
        {elseif $offerPosition.status==$klarnaStatusIds['completeCanceled']}
            <div class="status4">&nbsp;</div>{$pi_Klarna_lang['stats']['return']}
        {elseif $offerPosition.cleared==7}
            {$pi_Klarna_lang['stats']['complete']}
        {/if}
    </div>
    <div class="grid_2" style=" width:103px">
        <div class="textright">
            <strong>
                <a href="#order{$offerPosition.ordernumber}" title="{$pi_Klarna_lang['order']['show']} {$offerPosition.ordernumber}" class="orderdetails button-middle small" rel="order{$offerPosition.ordernumber}">
                    Anzeigen
                </a>
                {$order}
                {$myorderid=$offerPosition.ordernumber}
                {if $offerPosition.cleared==18 || ( $offerPosition.cleared==$klarnaStatusIds['accepted'] &&  $offerPosition.status==0)|| $offerPosition.cleared==$klarnaStatusIds['pending']  && ($offerPosition.paymentID == $pi_klarna_paymentids[0].id || $offerPosition.paymentID ==$pi_klarna_paymentids[1].id)}
                    <a href="{url controller='PiPaymentKlarna' action='stornoOrder' Pi_Klarna=$myorderid}15719816655" title="{$pi_Klarna_lang['storno_href']}" rel="order{$offerPosition.ordernumber}">
                        <img src="{link file='templates/_default/frontend/_resources/images/icons/ico_delete.png' fullPath}"  width="20px" style="position: relative; top: 5px;"/>
                    </a>
                {/if}
            </strong>
        </div>
    </div>
{else}
    {$smarty.block.parent}
{/if}
{/block}

