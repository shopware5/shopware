{block name="frontend_index_minimal_footer"}
    <div class="container footer-minimal">

        {* Service menu *}
        {block name="frontend_index_minimal_footer_menu"}
            <div class="footer--service-menu">
                {include file="widgets/index/menu.tpl" sGroup=left}
            </div>
        {/block}

        {* Vat info *}
        {if !$hideCopyrightNotice}
            {block name='frontend_index_minimal_footer_vat_info'}
                <div class="footer--vat-info">
                    <p class="vat-info--text">
                        {if $sOutputNet}
                            {s name='FooterInfoExcludeVat' namespace="frontend/index/footer"}{/s}
                        {else}
                            {s name='FooterInfoIncludeVat' namespace="frontend/index/footer"}{/s}
                        {/if}
                    </p>
                </div>
            {/block}

            {* Copyright *}
            {block name="frontend_index_minimal_footer_copyright"}
                <div class="footer--copyright">
                    {s name="IndexCopyright" namespace="frontend/index/footer"}{/s}
                </div>
            {/block}
        {/if}
    </div>
{/block}
