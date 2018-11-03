{block name="newsletter_footer_table_upper"}
<table width="560" border="0" cellspacing="0" cellpadding="0" style="color:#8c8c8c;width:560px; height:50px;font-family:Arial,Helvetica;">
    <tr>
        <td>
            {block name="newsletter_footer_table_navigation"}
            <div id="navi_unten">{s name='NewsletterFooterNavigation'}{/s}</div>
            {/block}
            {block name="newsletter_footer_table_tax_notice"}
            <div style="font-size:13px; text-align:left; color:#8c8c8c; padding:8px 0 0 0;margin-top:10px;line-height:14px;">
                {if $sUserGroupData.tax}
                    {s name='NewsletterFooterInfoIncludeVat'}{/s}
                {else}
                    {s name='NewsletterFooterInfoExcludeVat'}{/s}
                {/if}
            </div>
            {/block}
        </td>
    </tr>
    <tr>
        <td style="font-size:13px; text-align:left; color:#8c8c8c;margin:0;padding:0;">
            <div style="border-bottom:1px solid #dfdfdf; height:6px;line-height:6px;padding:0;margin:0;">&nbsp;</div>
        </td>
    </tr>
    <tr>
        <td style="font-size:13px; text-align:left; color:#8c8c8c;margin:0;padding:0;padding-top:10px;">
            {s name='NewsletterFooterCopyright'}{/s}
        </td>
    </tr>
</table>
{/block}
<br/>
{block name="newsletter_footer_table_lower"}
<table width="560" height="30" border="0" cellspacing="0" cellpadding="0" style="background-color:#fff;line-height:14px; font-size:13px; color:#8c8c8c !important;">
    {block name="newsletter_footer_table_lower_links"}
    <tr>
        <td width="20" style="font-size:13px;margin:0;padding:0;padding-left:10px;">&rArr;&nbsp;</td>
        <td style="font-size:13px;margin:0;padding:0;">
            <a href="{url module='frontend' controller='newsletter' sUnsubscribe=1}" target="_blank" style="color:#000 !important;">
                {s name='NewsletterFooterLinkUnsubscribe'}{/s}
            </a>
        </td>
    </tr>
    <tr>
        <td width="20" style="font-size:13px;margin:0;padding:0;padding-left:10px;">&rArr;&nbsp;</td>
        <td style="font-size:13px;margin:0;padding:0;">
            <a href="{$sStart}/backend/newsletter?campaign={$sCampaign.id}&mailaddress={$sUser.mailaddressID}&hash={$sCampaignHash}" target="_blank" style="color:#000 !important;">
                {s name='NewsletterFooterLinkNewWindow'}{/s}
            </a>
        </td>
    </tr>
    {/block}
</table>
{/block}
<br/>
