{foreach from=$sCampaign.containers item=sCampaignContainer}
{if $sCampaignContainer.type == "ctText"}
{$sCampaignContainer.description|strip_tags|trim|strip}
#################################################################
{include file="string:{$sCampaignContainer.data.html|strip|strip_tags|strip|trim}"}
{/if}
{if $sCampaignContainer.type == "ctLinks"}
{$sCampaignContainer.description|strip}
{foreach from=$sCampaignContainer.data item=sLink}
{$sLink.description|strip_tags|strip}
#################################################################
** {$sLink.link}
{/foreach}
{/if}

{if $sCampaignContainer.type == "ctSuggest"}
{$sRecommendations.description|strip_tags|strip}
#################################################################
{foreach from=$sRecommendations.data item=sArticle name=artikelListe}
{$sArticle.articleName|truncate:40:"[..]"|strip_tags}

{$sArticle.description_long|truncate:50:"..."|strip_tags|trim}

{if $sArticle.pseudoprice}

statt {$sArticle.pseudoprice|currency:use_shortname}
{/if}

{$sArticle.price|currency:use_shortname}

{$sArticle.linkDetails|rewrite:$sArticle.articleName}

#################################################################
{/foreach}
{/if}

{if $sCampaignContainer.type == "ctArticles"}
{$sCampaignContainer.description|strip_tags}
#################################################################
{foreach from=$sCampaignContainer.data item=sArticle name=artikelListe}
{$sArticle.articleName|truncate:40:"[..]"|strip_tags}

{$sArticle.description_long|truncate:50:"..."|strip_tags|trim}

{if $sArticle.pseudoprice}

statt {$sArticle.pseudoprice|currency:use_shortname}
{/if}

{$sArticle.price|currency:use_shortname}

{$sArticle.linkDetails|rewrite:$sArticle.articleName}

#################################################################
{/foreach}

{/if}
{/foreach}


Sie erhalten diesen Newsletter in der Text-Darstellung, besuchen Sie bitte unseren Shop
um auf die Angebote zugreifen zu können.
{if $sUserGroup.tax}* Alle Preise inkl ges. MwSt{else}* Alle Preise zzgl. ges. MwSt{/if} zzgl. Versand und ggf. Nachnahmegeb&uuml;hren, wenn nicht anders beschrieben
realisiert mit shopware von www.shopware.ag
Copyright &copy; 2008 shopware AG - Alle Rechte vorbehalten