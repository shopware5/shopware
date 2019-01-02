<?xml version="1.0" encoding="UTF-8"?>
<sitemapindex xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
{strip}
    {block name="frontend_sitemap_index_xml_index"}
        {foreach $sitemaps as $sitemap}
            {include file="frontend/sitemap_index_xml/entry.tpl" sitemap=$sitemap}
        {/foreach}
    {/block}
{/strip}
</sitemapindex>
