<table width="560"  border="0" cellspacing="0" cellpadding="0" style="color:#8c8c8c;width:560px; height:50px;font-family:Arial,Helvetica;">
  <tr>
    <td>
 	<div id="navi_unten">{s name='NewsletterFooterNavigation'}<a href="#" target="_blank" style="font-size:10px;">Kontakt</a> | <a href="#" target="_blank" style="font-size:10px;">Impressum</a>{/s}</div>
 	
	<div style="font-size:13px; text-align:left; color:#8c8c8c; padding:8px 0 0 0;margin-top:10px;line-height:14px;">
		{if $sUserGroupData.tax}
			{s name='NewsletterFooterInfoIncludeVat'}* Alle Preise inkl. gesetzl. Mehrwertsteuer zzgl. Versand{/s}
		{else}
			{s name='NewsletterFooterInfoExcludeVat'}* Alle Preise verstehen sich zzgl. Mehrwertsteuer und Versand{/s}
		{/if}
	</div>
	    </td>
	    
	<tr>
	<td style="font-size:13px; text-align:left; color:#8c8c8c;margin:0;padding:0;">	<div style="border-bottom:1px solid #dfdfdf; height:6px;line-height:6px;padding:0;margin:0;">&nbsp;</div>
</td>
	</tr>
	    
	<tr>
	<td style="font-size:13px; text-align:left; color:#8c8c8c;margin:0;padding:0;padding-top:10px;">
		{se name='NewsletterFooterCopyright'}Copyright � 2010 shopware AG - Alle Rechte vorbehalten.{/se}
	</td>
	</tr>
   
  </tr>
</table>
<br/>
<table width="560" height="30" border="0" cellspacing="0" cellpadding="0" style="background-color:#fff;line-height:14px; font-size:13px; color:#8c8c8c !important;">
  <tr>
    <td width="20" style="font-size:13px;margin:0;padding:0;padding-left:10px;">&rArr;&nbsp;</td>
    <td style="font-size:13px;margin:0;padding:0;">
    	<a href="{url module='frontend' controller='newsletter'}" target="_blank" style="color:#000 !important;">
    		{se name='NewsletterFooterLinkUnsubscribe'}Vom Newsletter abmelden{/se}</a>
    </td>
  </tr>
  <tr>
    <td width="20" style="font-size:13px;margin:0;padding:0;padding-left:10px;">&rArr;&nbsp;</td>
    <td style="font-size:13px;margin:0;padding:0;">
    	<a href="{$sStart|dirname}/backend/newsletter?campaign={$sCampaign.id}&mailaddress={$sUser.mailaddressID}&hash={$sCampaignHash}" target="_blank" style="color:#000 !important;">
    		{se name='NewsletterFooterLinkNewWindow'}Newsletter im Browser öffnen{/se}</a>
    	</a>
    </td>
  </tr>
</table>
<br/>