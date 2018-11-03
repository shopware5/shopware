<h2 style="font-size: 18px; font-weight: normal; margin: 15px 0pt 15px 0px; text-transform: uppercase; color:#E14900;">{include file="string:`$sCampaignContainer.description`"}</h2>

<table width="560" border="0" cellpadding="0" cellspacing="20" style="color:#8c8c8c; background-color: #fff; border:1px solid #dfdfdf;font-family:Arial,Helvetica;">
    <tr>
        <td style="padding:0;">

     <ul style="font-size:13px;font-weight:bold;list-style-position:inside;list-style-type:none; list-style-image:none;padding:0; margin:0;">
       {foreach from=$sCampaignContainer.data item=sLink}
        <li style="height:20px;">&nbsp;&nbsp;&nbsp;<a target="_blank" href="{$sLink.link}" style="text-decoration:none; color:#8c8c8c; font-size:11px;">{include file="string:`$sLink.description`"}</a></li>
       {/foreach}
    </ul>
        </td>
    </tr>
</table>
<div style="height:25px;">&nbsp;</div>