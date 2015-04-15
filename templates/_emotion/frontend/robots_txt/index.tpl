
User-agent: *

Disallow: {url controller=compare fullPath=false}

Disallow: {url controller=checkout fullPath=false}

Disallow: {url controller=register fullPath=false}

Disallow: {url controller=account fullPath=false}

Disallow: {url controller=note fullPath=false}


Sitemap: {url controller=index}sitemap.xml
{if {config name=mobileSitemap}}
Sitemap: {url controller=index}sitemapMobile.xml
{/if}
