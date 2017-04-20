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

{s name="NewsletterIndexPseudoInsteadOf"}{/s}{$sArticle.pseudoprice|currency:use_shortname}
{/if}

{$sArticle.price|currency:use_shortname}

{$sArticle.linkDetails}

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

{s name="NewsletterIndexPseudoInsteadOf"}{/s}{$sArticle.pseudoprice|currency:use_shortname}
{/if}

{$sArticle.price|currency:use_shortname}

{$sArticle.linkDetails}

#################################################################
{/foreach}

{/if}
{/foreach}

{s name="NewsletterIndexInfoPlain"}{/s}
{if $sUserGroupData.tax}
{s name="NewsletterIndexInfoIncludeVat"}{/s}
{else}
{s name="NewsletterIndexInfoExcludeVat"}{/s}
{/if}
{s name="NewsletterIndexCopyright"}{/s}
