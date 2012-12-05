<div id="footer" class="first last">

	{block name='frontend_index_footer_menu'}
		{include file='frontend/index/menu_footer.tpl'}
	{/block}
	
	<div class="bottom">
		<div class="grid_5 border">
		{block name='frontend_index_footer_copyright'}
			{s name="IndexCopyright"}Copyright &copy; 2010 shopware AG{/s}
		{/block}
		</div>
		<div class="grid_14">
		{block name='frontend_index_footer_vatinfo'}
			{if $sOutputNet}
				<p>{s name='FooterInfoExcludeVat'}&nbsp;{/s}</p>
			{else}
				<p>{s name='FooterInfoIncludeVat'}&nbsp;{/s}</p>
			{/if}
		{/block}
		</div>
	</div>
</div>