{* Static sites *}
{if $sMenu.gLeft}
	{block name='frontend_index_left_menu_headline'}
		<h2 class="navigation--headline">{s name="MenuLeftHeading"}Informationen{/s}</h2>
	{/block}

	<ul class="sidebar--navigation navigation--list" role="menu">
		{block name='frontend_index_left_menu_before'}{/block}

		{foreach $sMenu.gLeft as $item}
			{block name='frontend_index_left_menu_entry'}
				<li class="navigation--entry" role="menuitem">
					<a class="navigation--link" href="{if $item.link}{$item.link}{else}{url controller='custom' sCustom=$item.id title=$item.description}{/if}" title="{$item.description}" {if $item.target}target="{$item.target}"{/if}>
						{$item.description}
					</a>
				</li>
			{/block}
		{/foreach}

		{block name='frontend_index_left_menu_after'}{/block}
	</ul>
{/if}