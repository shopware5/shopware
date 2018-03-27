{block name="newsletter_header"}
<div>
{block name="newsletter_header_content"}
<div align="left">
    {block name="newsletter_header_content_logo"}
        {* align left needed for old outlook versions *}
        {if $theme.mobileLogo}
            <img align="left" src="{link file=$theme.mobileLogo fullPath}" alt="{s name="NewsletterHeaderLogoDescription"}{/s}" style="max-width: 50%; height: auto;"/>
        {else}
            <img align="left" src="{link file='frontend/_public/src/img/logos/logo--mobile.png' fullPath}" alt="{s name="NewsletterHeaderLogoDescription"}{/s}"/>
        {/if}
    {/block}
</div>
<div align="right">
    {block name="newsletter_header_content_title"}
    <span style="color:#999; font-size:13px;">NEWSLETTER</span>
    {/block}
</div>
{/block}
</div>
{* Clear floating *}
<div style="clear:both; float:none; height: 0; line-height: 0;">&nbsp;</div>
<br>
{/block}