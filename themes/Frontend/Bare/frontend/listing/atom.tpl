<?xml version="1.0" encoding="{encoding}" ?>
<feed xmlns="http://www.w3.org/2005/Atom">
<link href="{$sCategoryContent.atomFeed|escapeHtml}" rel="self" type="application/atom+xml" />
<author>
    <name>{$sShopname|escapeHtml}</name>
</author>
<title>{block name='frontend_listing_atom_title'}{$sCategoryContent.description|escapeHtml:'hexentity'}{/block}</title>
<id>{$sCategoryContent.rssFeed|escapeHtml:'hexentity'}</id>
<updated>{time()|date:atom}</updated>
{foreach $sArticles as $sArticle}
    {block name='frontend_listing_atom_entry'}
        <entry>
            <title type="text">{block name='frontend_listing_atom_article_title'}{$sArticle.articleName|strip_tags|strip|truncate:80:"...":true|escapeHtml}{/block}</title>
            <id>{block name='frontend_listing_atom_article_name'}{$sArticle.linkDetails|escapeHtml}{/block}</id>
            <link href="{block name='frontend_listing_atom_link'}{$sArticle.linkDetails|escapeHtml}{/block}"/>
            <summary type="html">
            <![CDATA[
                {block name='frontend_listing_atom_short_description'}
                {if $sArticle.description}
                    {$sArticle.description|strip_tags|strip|truncate:280:"...":true|escapeHtml}
                {else}
                    {$sArticle.description_long|strip_tags|strip|truncate:280:"...":true|escapeHtml}
                {/if}{/block}
            ]]>
            </summary>
            <content type="html">
            <![CDATA[
                {$sArticle.description_long|strip_tags|escapeHtml}
            ]]>
            </content>
            <updated>{if $sArticle.changetime}{$sArticle.changetime|date:atom}{else}{$sArticle.datum|date:atom}{/if}</updated>
        </entry>
    {/block}
{/foreach}
</feed>
