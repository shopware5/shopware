
{if $sLiveShopping}
	{foreach from=$sLiveShopping.liveshoppingData item=liveArt}
		{include file='frontend/listing/box_liveshopping.tpl'}
	{/foreach}
{/if}
