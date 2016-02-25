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

{if $sArticle.has_pseudoprice}

{s name="NewsletterIndexPseudoInsteadOf"}statt {/s}{$sArticle.pseudoprice|currency:use_shortname}
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

{if $sArticle.has_pseudoprice}

{s name="NewsletterIndexPseudoInsteadOf"}statt {/s}{$sArticle.pseudoprice|currency:use_shortname}
{/if}

{$sArticle.price|currency:use_shortname}

{$sArticle.linkDetails|rewrite:$sArticle.articleName}

#################################################################
{/foreach}

{/if}
{/foreach}

{s name="NewsletterIndexInfoPlain"}Sie erhalten diesen Newsletter in der Text-Darstellung, besuchen Sie bitte unseren Shop um auf die Angebote zugreifen zu können.{/s}
{if $sUserGroupData.tax}
{s name="NewsletterIndexInfoIncludeVat"}* Alle Preise inkl. gesetzl. Mehrwertsteuer zzgl. Versand{/s}
{else}
{s name="NewsletterIndexInfoExcludeVat"}* Alle Preise verstehen sich zzgl. Mehrwertsteuer und Versand{/s}
{/if}
{s name="NewsletterIndexCopyright"}Copyright &copy; shopware AG - Alle Rechte vorbehalten{/s}
