<div class="top-bar block-group">

	{* Language and Currency switcher *}
	{block name='frontend_index_actions'}
		<div class="top-bar--switches block">
			{action module=widgets controller=index action=shopMenu}
		</div>
	{/block}

	{* Top bar navigation *}
	{block name="frontend_index_top_bar_nav"}
		<nav class="top-bar--navigation block">
			<ul class="navigation--list" role="menubar">

				{* Compare - TODO - Check syntax *}
				{block name='frontend_index_navigation_inline'}
					{if $sCompareShow}
						<li class="navigation--entry entry--compare" role="menuitem" aria-haspopup="true">
							{block name='frontend_index_navigation_compare'}
								{action module=widgets controller=compare}
							{/block}
						</li>
					{/if}
				{/block}

				{* Notepad *}
				{block name="frontend_index_checkout_actions_notepad"}
					<li class="navigation--entry entry--notepad" role="menuitem">
						<a href="{url controller='note'}" title="{s namespace='frontend/index/checkout_actions' name='IndexLinkNotepad'}{/s}" class="note">
							{s namespace='frontend/index/checkout_actions' name='IndexLinkNotepad'}{/s} {if $sNotesQuantity > 0}<span class="notes_quantity">{$sNotesQuantity}</span>{/if}
						</a>
					</li>
				{/block}

				{* Service / Support drop down *}
				{block name="frontend_index_checkout_actions_service_menu"}
					<li class="navigation--entry entry--service has--drop-down" role="menuitem" aria-haspopup="true">
						{s name='IndexLinkService'}Service/Hilfe{/s}

						{* Include of the widget *}
						{block name="frontend_index_checkout_actions_service_menu_include"}
							{action module=widgets controller=index action=menu group=gLeft}
						{/block}
					</li>
				{/block}
			</ul>
		</nav>
	{/block}
</div>