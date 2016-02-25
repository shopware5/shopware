{function name=categories_top level=0}
<ul class="{if !$level}dropdown{else}droplevel{/if} droplevel{$level}">
	{foreach from=$categories item=category}
		{if !$category.hidetop}
			<li class="{if $category.flag}active{/if}{if $category.sub} sub{/if}">
				<a href="{$category.link}" class="{if $category.flag} active{/if}" {if $category.name}title="{$category.name|escape}"{/if}>{$category.name}</a>
				{if $category.sub}
					{call name=categories_top categories=$category.sub level=$level+1}
				{/if}
			</li>
		{/if}
	{/foreach}
</ul>
{/function}

<div id="mainNavigation">
	<ul>
        <li{if $sCategoryCurrent eq $sCategoryStart} class="active"{/if}>
            <a href="{url controller='index'}" title="{s name='IndexLinkHome'}{/s}" class="first{if $sCategoryCurrent eq $sCategoryStart} active{/if}">
                {se name='IndexLinkHome'}Home{/se}
            </a>
        </li>
	    {foreach from=$sAdvancedMenu item=sCategory}
            {if !$sCategory.hidetop}
				<li class="{if $sCategory.flag}active{/if}{if $sCategory.sub} dropactive{/if}">

					<a href="{$sCategory.link}" title="{$sCategory.name|escape}"{if $sCategory.flag} class="active"{/if}>
						<span>{$sCategory.name}</span>
					</a>

					{if $sCategory.sub}
						{call name=categories_top categories=$sCategory.sub}
					{/if}
				</li>
            {/if}
		{/foreach}
	</ul>
</div>