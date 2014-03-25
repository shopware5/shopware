<nav class="shop--navigation block-group">
	<ul class="navigation--list block-group" role="menubar">

		{* Menu (Off canvas left) trigger *}
		{block name='frontend_index_offcanvas_left_trigger'}
			<li class="navigation--entry entry--menu-left block" role="menuitem">
				<a class="entry--link entry--trigger" href="#offcanvas--left" data-offcanvas="true" data-selector=".sidebar-main">
					<i class="icon--menu"></i> {s name="IndexLinkMenu"}Menü{/s}
				</a>
			</li>
		{/block}

		{* Spacer *}
		{block name="frontend_index_shop_navigation_spacer"}
			<li class="navigation--entry entry--spacer block"></li>
		{/block}

		{* Search form *}
		{block name='frontend_index_search'}
			<li class="navigation--entry entry--search block" role="menuitem" data-search-dropdown="true" aria-haspopup="true">
				<a class="entry--link entry--trigger" href="#show-hide--search" title="{s name="IndexTitleSearchToggle"}Suche anzeigen / schließen{/s}">
					<i class="icon--search"></i>

					{block name='frontend_index_search_display'}
						<span class="search--display">{s name="IndexSearchFieldSubmit"}Suchen{/s}</span>
					{/block}
				</a>

				{* Include of the search form *}
				{block name='frontend_index_search_include'}
					{include file="frontend/index/_includes/search.tpl"}
				{/block}
			</li>
		{/block}

		{* My account entry *}
		{block name="frontend_index_checkout_actions_my_options"}
			<li class="navigation--entry entry--account block" role="menuitem">
				{block name="frontend_index_checkout_actions_account"}
					<a href="{url controller='account'}" title="{s namespace='frontend/index/checkout_actions' name='IndexLinkAccount'}{/s}" class="entry--link">
						<i class="icon--account"></i> {s namespace='frontend/index/checkout_actions' name='IndexLinkAccount'}{/s}
					</a>
				{/block}
			</li>
		{/block}

		{* Cart entry *}
		{block name='frontend_index_checkout_actions'}
			<li class="navigation--entry entry--cart block" role="menuitem">
				<a class="entry--link entry--trigger" href="#show-hide--search" title="{s name="IndexTitleCartToggle"}Warenkorb anzeigen / ausblenden{/s}">
					<i class="icon--basket"></i>
				</a>

				{* Include of the cart *}
				{block name='frontend_index_checkout_actions_include'}
					{action module=widgets controller=checkout action=info}
				{/block}
			</li>
		{/block}
	</ul>
</nav>