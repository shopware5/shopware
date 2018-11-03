{block name="newsletter_index_start"}{/block}
{block name="newsletter_index_doctype"}
<!DOCTYPE HTML>
{/block}

<html {block name="newsletter_index_html_attributes"}{/block}>

<head>
{block name="newsletter_index_index_head"}
    <meta charset="UTF-8" />
    <title>Newsletter</title>
    <style type="text/css">
        td {
            font-family:Arial,Helvetica;
        }
        a:link, a:visited {
            color:#8c8c8c;
            font-size:13px;
            text-decoration:none;
        }
        a:hover, a:active {
            color:#fff;
            font-size:13px;
            text-decoration:none;
        }
        a:hover {
            color:#fff;
            font-size:13px;
            text-decoration:none;
        }
        div#navi_unten a {
            color:#8c8c8c;
            font-size: 13px !important;
            text-decoration:none;
        }
    </style>
{/block}
</head>

<body {block name="newsletter_index_body_attributes"}style="height:100%; font-family:Arial, Helvetica, sans-serif; padding:0; background-color:#E9EBED;" background="#ffffff;margin:0;padding:0;" leftmargin="0" topmargin="0"{/block}>

{block name="newsletter_index_table"}
<table align="center" width="100%" border="0" cellspacing="25" cellpadding="0" style="color:#8c8c8c;font-family:Arial,Helvetica;">
    <tr>
        <td>
            <table align="center" width="560" bgcolor="#ffffff" border="0" cellspacing="25" cellpadding="0" style="color:#8c8c8c; border:1px solid #dfdfdf;font-family:Arial,Helvetica;">
                <tr>
                    <td>
                        {block name="newsletter_index_table_inner"}
                            {block name="newsletter_index_table_inner_header"}
                                {include file="newsletter/index/header.tpl"}
                            {/block}

                            {block name="newsletter_index_table_inner_content"}{/block}

                            {$path = ""}

                            {foreach from=$sCampaign.containers item=sCampaignContainer}
                                {if $sCampaignContainer.type == "ctBanner"}
                                    {$path = "newsletter/container/banner.tpl"}
                                {elseif $sCampaignContainer.type == "ctText"}
                                    {$path = "newsletter/container/text.tpl"}
                                {elseif $sCampaignContainer.type == "ctSuggest"}
                                    {$path = "newsletter/container/suggest.tpl"}
                                {elseif $sCampaignContainer.type == "ctArticles"}
                                    {$path = "newsletter/container/article.tpl"}
                                {elseif $sCampaignContainer.type == "ctLinks"}
                                    {$path = "newsletter/container/link.tpl"}
                                {elseif isset($sCampaignContainer.templateName) }
                                    {$path = "newsletter/container/{$sCampaignContainer.templateName}.tpl"}
                                {/if}

                                {if $path != ""}
                                    {if $sCampaignContainer.type == "ctSuggest"}
                                        {include file=$path sCampaignContainer=$sRecommendations}
                                    {else}
                                        {include file=$path}
                                    {/if}
                                {/if}
                            {/foreach}

                            {block name="newsletter_index_table_inner_footer"}
                                {include file="newsletter/index/footer.tpl"}
                            {/block}
                        {/block}
                    </td>
                </tr>
            </table>
            {block name="newsletter_index_log"}
            <img src="{url module='backend' controller='newsletter' action='log' mailing=$sMailing.id mailaddress=$sUser.mailaddressID fullPath}" style="width:1px;height:1px">
            {/block}
        </td>
    </tr>
</table>
{/block}
</body>
</html>