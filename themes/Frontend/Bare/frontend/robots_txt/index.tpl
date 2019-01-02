{block name="frontend_robots_txt_user_agent"}
User-agent: *
{/block}

{block name="frontend_robots_txt_disallows"}
Disallow: {url controller=compare fullPath=false}

Disallow: {url controller=checkout fullPath=false}

Disallow: {url controller=register fullPath=false}

Disallow: {url controller=account fullPath=false}

Disallow: {url controller=address fullPath=false}

Disallow: {url controller=note fullPath=false}

Disallow: {url controller=widgets fullPath=false}

Disallow: {url controller=listing fullPath=false}

Disallow: {url controller=ticket fullPath=false}
{/block}

{block name="frontend_robots_txt_allows"}
Allow: {url module=widgets controller=emotion fullPath=false}
{/block}

{block name="frontend_robots_txt_sitemap"}
Sitemap: {url controller=index}sitemap_index.xml
{/block}

{*
    @deprecated

    Will be removed in 5.6 without alternative
*}
{block name="frontend_robots_txt_sitemap_mobile"}{/block}
