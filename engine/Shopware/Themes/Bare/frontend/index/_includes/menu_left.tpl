{* Static sites *}
{if $sMenu.gLeft}
	<h2 class="navigation--headline">{s name="MenuLeftHeading"}Informationen{/s}</h2>
	<ul class="sidebar--navigation navigation--list" role="menu">
		{foreach $sMenu.gLeft as $item}
			<li class="navigation--entry" role="menuitem">
				<a class="navigation--link" href="{if $item.link}{$item.link}{else}{url controller='custom' sCustom=$item.id title=$item.description}{/if}" title="{$item.description}" {if $item.target}target="{$item.target}"{/if}>
					{$item.description}
				</a>
			</li>
		{/foreach}
	</ul>
{/if}