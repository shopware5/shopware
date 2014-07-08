
{extends file='frontend/index/index.tpl'}

{* Breadcrumb *}
{block name='frontend_index_start' append}
	{$sBreadcrumb = [['name'=>"{s name='AccountLogoutTitle'}{/s}", 'link'=>{url}]]}
{/block}

{block name='frontend_index_content'}
	<div class="heading">
		<h2>{se name='AccountLogoutHeader'}{/se}</h2>
	</div>
	<div class="text">
		<p>
			{se name='AccountLogoutText'}{/se}
		</p>
		<p>
			<a class="button-left large" href="{url controller='index'}" title="{s name='AccountLogoutButton'}{/s}">{se name="AccountLogoutButton"}{/se}</a>
		</p>
	</div>
{/block}
