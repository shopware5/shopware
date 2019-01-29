{extends file="frontend/index/index.tpl"}

{* Breadcrumb *}
{block name="frontend_index_start"}
    {s name="SitemapTitle" assign="snippetSitemapTitle"}{/s}
    {$sBreadcrumb = [['name' => $snippetSitemapTitle, 'link' => {url controller=sitemap}]]}
    {$smarty.block.parent}
{/block}

{block name="frontend_index_content"}
    <div class="sitemap--content content block">

    {block name="frontend_sitemap_headline"}
        <div class="sitemap--headline panel--body is--wide has--border is--rounded">

            {block name="frontend_sitemap_headline_title"}
                <h1 class="sitemap--title">{s name='SitemapTitle'}{/s}</h1>
            {/block}

            {block name="frontend_sitemap_headline_text"}
                <div class="sitemap--text">
                    <p class="sitemap--headline-text">{s name='SitemapSubHeader'}{/s}</p>
                </div>
            {/block}
        </div>
    {/block}


    {block name="frontend_sitemap_content"}
        {$i = 0}

        {foreach $sCategoryTree as $categoryTree}
            {if ($categoryTree.name == 'SitemapStaticPages' || $categoryTree.name == 'SitemapSupplierPages' || $categoryTree.name == 'SitemapLandingPages') && !$categoryTree.sub}
                {continue}
            {/if}

            {if $i == 0}
                <div class="block-group">
            {/if}

            {block name="frontend_sitemap_category"}
                <div class="sitemap--category block is--rounded">

                    {block name="frontend_sitemap_navigation"}
                        <ul class="sitemap--navigation list--unstyled">

                            {block name="frontend_sitemap_navigation_headline"}
                                <li class="sitemap--navigation-head is--bold is--rounded">

                                    {if $categoryTree.name == 'SitemapStaticPages'}
                                        {block name="frontend_sitemap_navigation_staticpages"}
                                            {s name="SitemapStaticPages" assign="snippetSitemapStaticPages"}{/s}
                                            <a href="{$categoryTree.link}" title="{$snippetSitemapStaticPages|escape}" class="sitemap--navigation-head-link is--active">
                                                {s name='SitemapStaticPages'}{/s}
                                            </a>
                                        {/block}
                                    {elseif $categoryTree.name == 'SitemapSupplierPages'}
                                        {block name="frontend_sitemap_navigation_supplierpages"}
                                            {s name="SitemapSupplierPages" assign="snippetSitemapSupplierPages"}{/s}
                                            <a href="{$categoryTree.link}" title="{$snippetSitemapSupplierPages|escape}" class="sitemap--navigation-head-link is--active">
                                                {s name='SitemapSupplierPages'}{/s}
                                            </a>
                                        {/block}
                                    {elseif $categoryTree.name == 'SitemapLandingPages'}
                                        {block name="frontend_sitemap_navigation_landingpages"}
                                            {s name="SitemapLandingPages" assign="snippetSitemapLandingPages"}{/s}
                                            <a href="{$categoryTree.link}" title="{$snippetSitemapLandingPages|escape}" class="sitemap--navigation-head-link is--active">
                                                {s name='SitemapLandingPages'}{/s}
                                            </a>
                                        {/block}
                                    {else}
                                        {block name="frontend_sitemap_navigation_defaultpages"}
                                            <a href="{$categoryTree.link}" title="{$categoryTree.name|escape}" class="sitemap--navigation-head-link is--active"{if $categoryTree.external && $categoryTree.externalTarget} target="{$categoryTree.externalTarget}"{/if}>
                                                {$categoryTree.name}
                                            </a>
                                        {/block}
                                    {/if}

                                </li>
                            {/block}

                            {if $categoryTree.sub}
                                {include file="frontend/sitemap/recurse.tpl" sCategoryTree=$categoryTree.sub depth=1}
                            {/if}
                        </ul>
                    {/block}

                </div>
            {/block}

            {if $i == 3 || $categoryTree@last}
                </div>
                {$i = 0}
            {else}
                {$i = $i + 1}
            {/if}
        {/foreach}
    {/block}

    </div>
{/block}
