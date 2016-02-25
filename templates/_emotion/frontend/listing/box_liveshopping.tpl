
{if $liveArt.sDetails}
	<div class="clear">&nbsp;</div>
	{if 2==$liveArt.typeID || 3==$liveArt.typeID}
		{include file="frontend/detail/liveshopping/category_countdown.tpl" sLiveshoppingData=$liveArt}
	{else}
		{include file="frontend/detail/liveshopping/category.tpl" sLiveshoppingData=$liveArt}
	{/if}
	<div class="clear">&nbsp;</div>
{/if}
