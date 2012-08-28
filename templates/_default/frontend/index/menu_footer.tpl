{* Footer menu *}
<div class="footer_menu">
<p>
	{foreach from=$sMenu.gBottom item=item  key=key name="counter"}
		<a href="{if $item.link}{$item.link}{else}{url controller='custom' sCustom=$item.id title=$item.description}{/if}" title="{$item.description}" {if $item.target}target="{$item.target}"{/if}>
			{$item.description}
		</a>
		{if !$smarty.foreach.counter.last}|{/if}
	{/foreach}	
</p>
<p>
	{foreach from=$sMenu.gBottom2 item=item key=key name="counter"}
		<a href="{if $item.link}{$item.link}{else}{url controller='custom' sCustom=$item.id title=$item.description}{/if}" title="{$item.description}" {if $item.target}target="{$item.target}"{/if}>
			{$item.description}
		</a>
		{if !$smarty.foreach.counter.last}|{/if}
	{/foreach}
</p>
</div>