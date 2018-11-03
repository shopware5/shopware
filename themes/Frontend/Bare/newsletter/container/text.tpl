{if !$sCampaignContainer.data.image}
<table width="560" border="0" cellspacing="10" cellpadding="0" style="color:#494949; line-height:17px;font-family:Arial,Helvetica;font-size:13px; padding:15px; width:560px; background-color: #fff;border: 1px solid #dfdfdf;">
  <tr>
    <td>
    <h2 style="color:#000; line-height:20px; font-size:16px; font-weight:bold; padding:0px 0px 0px 0px; margin:0px; text-transform:uppercase;">
    {include file="string:`$sCampaignContainer.description`"}
    </h2>
    {include file="string:{$sCampaignContainer.data.html|replace:'<h2':'<h2 style="font-size:13px;color:#e14900; text-transform:uppercase;line-height:14px;" '}"}
    </td>
  </tr>
</table>
<div style="height: 25px;">&nbsp;</div>
{else}
    {if $sCampaignContainer.description && $sCampaignContainer.description!="none"}
    <h2 style="color:#000; font-size:18px; font-weight:bold; margin:15px 0 15px 0px;text-transform:uppercase;">{include file="string:`$sCampaignContainer.description`"}</h2>
    {/if}
    {if $sCampaignContainer.data.alignment=="left"}
    <table width="560" height="120" border="0" cellspacing="0" cellpadding="0" style="font-family:Arial,Helvetica;background-color: #fff;border:1px solid #dfdfdf;">
    <tbody style="margin:0;padding:0;">
      <tr>
        <td width="180" align="center" height="120" valign="top" style="padding:0;">
        <a target="_blank" href="{$sCampaignContainer.data.link}">
        <img src="{$sCampaignContainer.data.image}" alt="Banner" border="0" align="middle" style="height:120px" height="120" />
        </a>
        </td>
       <td height="120" valign="top" style="padding:0px;font-size:11px;color:#e14900;">

       <table width="100%" height="100%" border="0" cellspacing="10" cellpadding="0" style="padding:0px;font-size:13px;color:#494949;font-family:Arial,Helvetica;">
       <tr>
       <td>
       {include file="string:{$sCampaignContainer.data.html|replace:'<h2':'<h2 style="font-size:13px;color:#e14900; text-transform:uppercase;line-height:14px;" '}"}
       </td>
       </tr>
       </table>
       </td>
      </tr>
      </tbody>
    </table>
    <div style="height:25px;">&nbsp;</div>
    {else}
    <table width="560" height="120" border="0" cellspacing="0" cellpadding="0" style="font-family:Arial,Helvetica;background-color: #fff;border:1px solid #dfdfdf;">
      <tr>
        <td height="120" valign="top" style="padding:0px;font-size:13px;color:#494949;">
        <table width="100%" height="100%" border="0" cellspacing="10" cellpadding="0" style="padding:0px;font-size:13px;color:#494949;">
       <tr>
       <td>
       {include file="string:{$sCampaignContainer.data.html|replace:'<h2':'<h2 style="font-size:13px;color:#e14900; text-transform:uppercase;line-height:14px;" '}"}
        </td>
        </tr>
        </table>
        <td width="180" align="center" height="120" valign="top" style="padding:0;">
        <a target="_blank" href="{$sCampaignContainer.data.link}">
        <img src="{$sCampaignContainer.data.image}" alt="Banner" border="0" align="middle" style="height:120px" height="120" />
        </a>
        </td>
      </tr>
    </table>
    <div style="height:25px;">&nbsp;</div>
    {/if}
{/if}