<div id="footer">

	{block name='frontend_index_footer_menu'}
		{include file='frontend/index/menu_footer.tpl'}
		<div class="clear"></div>
	{/block}

</div>

{block name='frontend_index_footer_copyright'}
	<div class="bottom">
		{block name='frontend_index_footer_vatinfo'}
		<div class="footer_info">
			{if $sOutputNet}
				<p>{s name='FooterInfoExcludeVat'}&nbsp;{/s}</p>
			{else}
				<p>{s name='FooterInfoIncludeVat'}&nbsp;{/s}</p>
			{/if}
		</div>
		{/block}
		<div class="footer_copyright">
			<span>{s name="IndexCopyright"}Copyright &copy; 2014 shopware AG{/s}</span>
		</div>
	</div>
{/block}
