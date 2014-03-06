<ul class="breadcrumb--list" role="menu">

	{* Prefix for the breadcrumb e.g. the configured shop name *}
	{block name="frontend_index_breadcrumb_prefix"}
		{if {config name=shopName}}
			<li class="breadcrumb--entry" role="menuitem" itemscope itemtype="http://data-vocabulary.org/Breadcrumb">
				<a class="breadcrumb--link" href="{url controller='index'}" title="{config name=shopName}" itemprop="url">
					<span class="breadcrumb--title" itemprop="title">{config name=shopName}</span>
				</a>
			</li>
		{/if}
	{/block}

    {foreach $sBreadcrumb as $breadcrumb}
		{block name="frontend_index_breadcrumb_entry"}
			<li class="breadcrumb--entry" role="menuitem" itemscope itemtype="http://data-vocabulary.org/Breadcrumb">
				{if $breadcrumb.name}
					{if $breadcrumb@last}
						{block name="frontend_index_breadcrumb_entry_last"}
							<a class="breadcrumb--link is--last" href="{if $breadcrumb.link}{$breadcrumb.link}{else}#{/if}" title="{$breadcrumb.name}" itemprop="url">
								<strong class="breadcrumb--title" itemprop="title">{$breadcrumb.name}</strong>
							</a>
						{/block}
					{else}
						{block name="frontend_index_breadcrumb_entry_inner"}
							<a class="breadcrumb--link" href="{if $breadcrumb.link}{$breadcrumb.link}{else}#{/if}" title="{$breadcrumb.name}" itemprop="url">
								<span class="breadcrumb--title" itemprop="title">{$breadcrumb.name}</span>
							</a>
						{/block}
					{/if}
				{/if}
			</li>
		{/block}
    {/foreach}
</ul>