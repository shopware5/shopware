{extends file='frontend/index/index.tpl'}

{* Breadcrumb *}
{block name='frontend_index_start' prepend}
	{assign var='sBreadcrumb' value=[['name'=>'Paypal Express Order Pending Page'|snippet:'PalpalPendingTitle']]}
{/block}

{block name='frontend_index_content'}
<div class="grid_16" id="center">
	<h2>{s name="PalpalPendingTitle"}{/s}</h2>
	<div>
		{s name="PalpalPendingInfo"}{/s}
	</div>
	<a href="{url controller='index'}" title="{"{s name='PalpalPendingLinkHomepage'}{/s}"|escape}" class="button-left large modal_close">
		{s name="PalpalPendingLinkHomepage"}{/s}
	</a>
</div>
{/block}