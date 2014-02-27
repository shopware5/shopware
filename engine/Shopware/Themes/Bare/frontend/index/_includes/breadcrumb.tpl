<ul class="breadcrumb--list" role="menu">
    {if {config name=shopName}}
        <li class="breadcrumb--entry" role="menuitem" itemscope itemtype="http://data-vocabulary.org/Breadcrumb">
            <a class="breadcrumb--link" href="{url controller='index'}" title="{config name=shopName}" itemprop="url">
                <span class="breadcrumb--title" itemprop="title">{config name=shopName}</span>
            </a>
        </li>
    {/if}

    {foreach $sBreadcrumb as $breadcrumb}
        <li class="breadcrumb--entry" role="menuitem" itemscope itemtype="http://data-vocabulary.org/Breadcrumb">
            {if $breadcrumb.name}
                {if $breadcrumb@last}
                    <a class="breadcrumb--link is--last" href="{if $breadcrumb.link}{$breadcrumb.link}{else}#{/if}" title="{$breadcrumb.name}" itemprop="url">
                        <strong class="breadcrumb--title" itemprop="title">{$breadcrumb.name}</strong>
                    </a>
                {else}
                    <a class="breadcrumb--link" href="{if $breadcrumb.link}{$breadcrumb.link}{else}#{/if}" title="{$breadcrumb.name}" itemprop="url">
                        <span class="breadcrumb--title" itemprop="title">{$breadcrumb.name}</span>
                    </a>
                {/if}
            {/if}
        </li>
    {/foreach}
</ul>