
{if $sCompareAddResult|is_bool}
	{include file="frontend/compare/index.tpl"}
{else}
	<div class="heading">
		<h2>{s name="CompareHeaderTitle"}{/s}</h2>
		

		<a href="#" class="modal_close" title="{s name='LoginActionClose'}{/s}">
			{s name='CompareActionClose'}{/s}
		</a>
	</div>
	<p class="text">
		{s name='CompareInfoMaxReached'}{/s}
	</p>
{/if}
