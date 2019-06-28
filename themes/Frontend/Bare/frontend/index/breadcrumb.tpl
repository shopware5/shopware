<ul class="breadcrumb--list" role="menu" itemscope itemtype="http://schema.org/BreadcrumbList">

    {* Prefix for the breadcrumb e.g. the configured shop name *}
    {block name="frontend_index_breadcrumb_prefix"}{/block}

    {block name="frontend_index_breadcrumb_content"}
        {foreach $sBreadcrumb as $breadcrumb}
            {block name="frontend_index_breadcrumb_entry"}
                <li role="menuitem" class="breadcrumb--entry{if $breadcrumb@last} is--active{/if}" itemprop="itemListElement" itemscope itemtype="http://schema.org/ListItem">
                    {if $breadcrumb.name}
                        {block name="frontend_index_breadcrumb_entry_inner"}
                            {if $breadcrumb.link}
                                <a class="breadcrumb--link" href="{$breadcrumb.link}" title="{$breadcrumb.name|escape}" itemprop="item">
                                    <link itemprop="url" href="{$breadcrumb.link}" />
                                    <span class="breadcrumb--title" itemprop="name">{$breadcrumb.name}</span>
                                </a>
                            {else}
                                <span class="breadcrumb--link" itemprop="item">
                                    <span class="breadcrumb--title" itemprop="name">{$breadcrumb.name}</span>
                                </span>
                            {/if}
                            <meta itemprop="position" content="{$breadcrumb@index}" />
                        {/block}
                    {/if}
                </li>
                {if !$breadcrumb@last}
                    <li role="none" class="breadcrumb--separator">
                        <i class="icon--arrow-right"></i>
                    </li>
                {/if}
            {/block}
        {/foreach}
    {/block}

    {block name="frontend_index_breadcrumb_suffix"}{/block}
</ul>
