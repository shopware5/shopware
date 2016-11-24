<!DOCTYPE HTML>
<html>
<head>
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
</head>

<body style="height:100%; font-family:Arial, Helvetica, sans-serif; padding:0; background-color:#E9EBED;" background="#ffffff;margin:0;padding:0;" leftmargin="0" topmargin="0">

<table align="center" width="100%" border="0" cellspacing="25" cellpadding="0" style="color:#8c8c8c;font-family:Arial,Helvetica;">
    <tr>
        <td>
            <table align="center" width="560" bgcolor="#ffffff" border="0" cellspacing="25" cellpadding="0" style="color:#8c8c8c; border:1px solid #dfdfdf;font-family:Arial,Helvetica;">
                <tr>
                    <td>

                        {include file="newsletter/index/header.tpl"}

                        {foreach from=$sCampaign.containers item=sCampaignContainer}
                            {if $sCampaignContainer.type == "ctBanner"}
                                {include file="newsletter/container/banner.tpl"}
                            {elseif $sCampaignContainer.type == "ctText"}
                                {include file="newsletter/container/text.tpl"}
                            {elseif $sCampaignContainer.type == "ctSuggest"}
                                {include file="newsletter/container/suggest.tpl" sCampaignContainer=$sRecommendations}
                            {elseif $sCampaignContainer.type == "ctArticles"}
                                {include file="newsletter/container/article.tpl"}
                            {elseif $sCampaignContainer.type == "ctLinks"}
                                {include file="newsletter/container/link.tpl"}
                            {elseif isset($sCampaignContainer.templateName) }
                                {include file="newsletter/container/{$sCampaignContainer.templateName}.tpl"}
                            {/if}
                        {/foreach}

                        {include file="newsletter/index/footer.tpl"}

                        <!--FOOTER-->
                    </td>
                </tr>
            </table>

            <img src="{url module='backend' controller='newsletter' action='log' mailing=$sMailing.id mailaddress=$sUser.mailaddressID fullPath}" style="width:1px;height:1px">

        </td>
    </tr>
</table>
</body>
</html>
