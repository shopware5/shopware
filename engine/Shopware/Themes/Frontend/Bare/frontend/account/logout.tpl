{extends file='frontend/index/index.tpl'}

{namespace name="frontend/account/ajax_logout"}

{* Breadcrumb *}
{block name='frontend_index_start' append}
	{$sBreadcrumb = [['name'=>"{s name='AccountLogoutTitle'}{/s}", 'link'=>{url}]]}
{/block}

{block name='frontend_index_content'}
	<div class="content block account--content is--wide">

		{block name="frontend_account_logout_info"}
			<div class="account--welcome panel">

				{block name="frontend_account_logout_info_headline"}
					<h1 class="panel--title">{s name="AccountLogoutHeader"}{/s}</h1>
				{/block}

				{block name="frontend_account_logout_info_content"}
					<div class="panel--body is--wide">
						<p>{s name="AccountLogoutText"}{/s}</p>
					</div>
				{/block}

				{block name="frontend_account_logout_info_actions"}
					<div class="panel--actions">
						<a class="btn btn--secondary" href="{url controller='index'}" title="{s name='AccountLogoutButton'}{/s}">
							{s name="AccountLogoutButton"}{/s}
						</a>
					</div>
				{/block}
			</div>
		{/block}

	</div>
{/block}