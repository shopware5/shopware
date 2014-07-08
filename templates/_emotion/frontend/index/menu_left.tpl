{* Static sites *}
{if $sMenu.gLeft}
<ul id="servicenav">
	<li class="heading">{se name="MenuLeftHeading"}Informationen{/se}</li>
	{foreach from=$sMenu.gLeft item=item}
		<li>
			<a href="{if $item.link}{$item.link}{else}{url controller='custom' sCustom=$item.id title=$item.description}{/if}" title="{$item.description}" {if $item.target}target="{$item.target}"{/if}>
				{$item.description}
			</a>
		</li>
	{/foreach}
</ul>
{/if}