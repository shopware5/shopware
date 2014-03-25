{* Footer menu *}
{block name='frontend_index_footer_menu'}
    <div class="footer--columns block-group">
        {include file='frontend/index/_includes/footer-navigation.tpl'}
    </div>
{/block}

{* Copyright in the footer *}
{block name='frontend_index_footer_copyright'}
	<div class="footer--bottom">

        {* Vat info *}
		{block name='frontend_index_footer_vatinfo'}
            <div class="footer--vat-info">
                {if $sOutputNet}
                    <p>{s name='FooterInfoExcludeVat' namespace="frontend/index/footer"}&nbsp;{/s}</p>
                {else}
                    <p>{s name='FooterInfoIncludeVat' namespace="frontend/index/footer"}&nbsp;{/s}</p>
                {/if}
            </div>
		{/block}

        {* Shopware footer *}
        {block name="frontend_index_shopware_footer"}

			{* Copyright *}
			{block name="frontend_index_shopware_footer_copyright"}
				<div class="footer--copyright">
					{s name="IndexCopyright"}Copyright &copy; 2014 shopware AG{/s}
				</div>
			{/block}

			{* Logo *}
			{block name="frontend_index_shopware_footer_logo"}
				<div class="footer--logo">
					<i class="icon--shopware"></i>
				</div>
			{/block}
        {/block}
	</div>
{/block}
