<h2 style="color:#e14900; font-size:18px; font-weight:normal; margin:15px 0 15px 0;text-transform:uppercase;">
    {include file="string:`$sCampaignContainer.description`"}
</h2>

<table width="560" border="0" cellpadding="0" cellspacing="0" style="margin:0;padding:0;font-family:Arial,Helvetica;">
    <tr>
        <td width="100%">
            <table width="100%" border="0" cellpadding="0" cellspacing="0" style="margin:0;padding:0;font-family:Arial,Helvetica;">
                {foreach from=$sCampaignContainer.data item=sArticle name=artikelListe}
                    {if $sArticle@index%3==0}<tr>{/if}
                    <!-- beginning article -->
                    <td height="320" width="180" align="center" valign="top" border="0" cellpadding="0" cellspacing="0" style="border: 1px solid #dfdfdf; padding:0;margin:0; width:180px;">
                        <!-- article content -->
                        <table width="100%" height="100%" align="center" border="0" cellpadding="0" cellspacing="0" style="padding:0;margin:0;background-color:#ffffff;font-family:Arial,Helvetica;">
                            <tr>
                                <td height="180" valign="center" style="text-align:center;background-color:#fff;">
                                    <div align="center" style="overflow:hidden;">
                                        <a target="_blank" href="{url controller=detail sArticle=$sArticle.articleID}" title="{$sArticle.articleName}">
                                            {if $sArticle.image.source}
                                                <img src="{$sArticle.image.thumbnails[0].source}" border="0" alt="{$sArticle.articleName|escape|truncate:155}">
                                            {else}
                                                <img src="{link file='frontend/_public/src/img/no-picture.jpg' fullPath}" alt="{s name="ListingBoxNoPicture" namespace="frontend/listing/box_article"}{/s}" border="0"/>
                                            {/if}
                                        </a>
                                    </div>
                                </td>
                            </tr>
                            <tr>
                                <td valign="top" style="color:#000; font-size:13px; background-color:#fff; height:5px; padding: 8px 10px 5px 10px; font-weight:bold;">
                                    <a href="{url controller=detail sArticle=$sArticle.articleID}" target="_blank" title="{$sArticle.articleName}" style="color:#000000;text-decoration:underline;font-size:13px;">{$sArticle.articleName|truncate:20:"[..]"}</a>
                                </td>
                            </tr>
                            <tr>
                                <td height="50" valign="top" style="font-size:13px; color:#8c8c8c; padding: 0 10px 8px 10px;">
                                    {$sArticle.description_long|truncate:80:"..."}
                                </td>
                            </tr>
                            <tr>
                                <td height="40" style="text-align:left; padding:10px;">
                                    <table width="100%" border="0" cellspacing="0" cellpadding="0" style="font-family:Arial,Helvetica;">
                                        <tr>
                                            <td width="160">
                                                {if $sArticle.purchaseunit}
                                                    <div style="font-size:13px;color:#888;margin-bottom:8px;">
                                                        <p style="font-size:13px;margin:0;">
                                                            <strong>{s name="ListingBoxArticleContent" namespace="frontend/listing/box_article"}{/s}:</strong> {$sArticle.purchaseunit} {$sArticle.sUnit.description}
                                                        </p>
                                                        {if $sArticle.purchaseunit != $sArticle.referenceunit}
                                                            <p style="font-size:13px;margin:0">
                                                                {if $sArticle.referenceunit}
                                                                    <strong class="baseprice">{s name="ListingBoxBaseprice"  namespace="frontend/listing/box_article"}{/s}:</strong>
                                                                    {$sArticle.referenceunit} {$sArticle.sUnit.description} = {$sArticle.referenceprice|currency} {s name="Star" namespace="frontend/listing/box_article"}{/s}
                                                                {/if}
                                                            </p>
                                                        {/if}
                                                    </div>
                                                {/if}
                                                {if $sArticle.has_pseudoprice}
                                                    <span style="color:#999; font-size:13px; line-height:13px;"><s>{$sArticle.pseudoprice|currency} {s name="Star" namespace="frontend/listing/box_article"}{/s}</s></span>
                                                    <br/>
                                                    <strong style="color:#990000;font-size:14px;">
                                                        {if $sArticle.priceStartingFrom}{s name='NewsletterBoxArticleStartsAt'}ab{/s} {/if}
                                                        {$sArticle.price|currency} {s name="Star" namespace="frontend/listing/box_article"}{/s}
                                                    </strong>
                                                {else}
                                                    <strong style="color:#000;font-size:14px;">
                                                        {if $sArticle.priceStartingFrom}{s name='NewsletterBoxArticleStartsAt'}ab{/s} {/if}
                                                        {$sArticle.price|currency} {s name="Star" namespace="frontend/listing/box_article"}{/s}
                                                    </strong>
                                                {/if}
                                            </td>
                                        </tr>
                                    </table>
                                </td>
                            </tr>
                        </table>
                    <!-- end#article content-->
                    </td>
                    {if $sArticle@index%3==2}
                        </tr>
                        {if !$sArticle@last}
                            <tr>
                                <td style="height:10px;"></td>
                            </tr>
                        {/if}
                    {elseif !$sArticle@last}
                        <td style="width:10px;height:10px;"></td>
                    {/if}
                {/foreach}
            </table>
        <!--CONTENT-->
        </td>
    </tr>
</table>
<div style="height:25px;">&nbsp;</div>
