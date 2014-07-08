<ul class="breadcrumb--list" role="menu">

	{* Prefix for the breadcrumb e.g. the configured shop name *}
	{block name="frontend_index_breadcrumb_prefix"}
		{if {config name=shopName}}
			<li class="breadcrumb--entry" role="menuitem" itemscope itemtype="http://data-vocabulary.org/Breadcrumb">
				<a class="breadcrumb--link" href="{url controller='index'}" title="{config name=shopName}" itemprop="url">
					<span class="breadcrumb--title" itemprop="title">{config name=shopName}</span>
				</a>
			</li>

            <li class="breadcrumb--separator">
                <i class="icon--arrow-right"></i>
            </li>
		{/if}
	{/block}

    {foreach $sBreadcrumb as $breadcrumb}
		{block name="frontend_index_breadcrumb_entry"}
			<li class="breadcrumb--entry{if $breadcrumb@last} is--active{/if}" role="menuitem" itemscope itemtype="http://data-vocabulary.org/Breadcrumb">
				{if $breadcrumb.name}
                    {block name="frontend_index_breadcrumb_entry_inner"}
                        <a class="breadcrumb--link" href="{if $breadcrumb.link}{$breadcrumb.link}{else}#{/if}" title="{$breadcrumb.name}" itemprop="url">
                            <span class="breadcrumb--title" itemprop="title">{$breadcrumb.name}</span>
                        </a>
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
</ul>