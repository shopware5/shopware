{* Breadcrumb *}
<div id="breadcrumb">
	{if $sShopname}
		<a href="{url controller='index'}">
			{$sShopname}
		</a>
	{/if}
	{foreach name=breadcrumb from=$sBreadcrumb item=breadcrumb}
	{if $breadcrumb.name}
		{if $smarty.foreach.breadcrumb.last}
			<span class="sep">/</span>
			<a href="{if $breadcrumb.link}{$breadcrumb.link}{else}#{/if}" title="{$breadcrumb.name}" class="last">
				<strong>{$breadcrumb.name}</strong>
			</a>
		{else} 
			<span class="sep">/</span>
			<a href="{if $breadcrumb.link}{$breadcrumb.link}{else}#{/if}" title="{$breadcrumb.name}">
				{$breadcrumb.name}
			</a>
		{/if}
	{/if}
	{/foreach}
</div>