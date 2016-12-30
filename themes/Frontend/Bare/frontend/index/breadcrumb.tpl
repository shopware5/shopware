<ul class="breadcrumb--list" role="menu" itemscope itemtype="http://schema.org/BreadcrumbList">

    {* Prefix for the breadcrumb e.g. the configured shop name *}
    {block name="frontend_index_breadcrumb_prefix"}{/block}

    {block name="frontend_index_breadcrumb_content"}
        {foreach $sBreadcrumb as $breadcrumb}
            {block name="frontend_index_breadcrumb_entry"}
                <li class="breadcrumb--entry{if $breadcrumb@last} is--active{/if}" itemprop="itemListElement" itemscope itemtype="http://schema.org/ListItem">
                    {if $breadcrumb.name}
                        {block name="frontend_index_breadcrumb_entry_inner"}
                            <a class="breadcrumb--link" href="{if $breadcrumb.link}{$breadcrumb.link}{else}#{/if}" title="{$breadcrumb.name|escape}" itemprop="item">
                                <link itemprop="url" href="{if $breadcrumb.link}{$breadcrumb.link}{else}#{/if}" />
                                <span class="breadcrumb--title" itemprop="name">{$breadcrumb.name}</span>
                            </a>
                            <meta itemprop="position" content="{$breadcrumb@index}" />
                        {/block}
                    {/if}
                </li>
                {if !$breadcrumb@last}
                    <li class="breadcrumb--separator">
                        <i class="icon--arrow-right"></i>
                    </li>
                {/if}
            {/block}
        {/foreach}
    {/block}

    {block name="frontend_index_breadcrumb_suffix"}{/block}
</ul>
