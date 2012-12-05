{extends file='frontend/index/index.tpl'}
{* Breadcrumb *}
{block name='frontend_index_start' append}
    {$sBreadcrumb = [['name'=>"{s name=PaymentTitle}Zahlung durchf&uuml;hren{/s}"]]}
{/block}
{block name='frontend_index_content_left'}{/block}
{block name="frontend_index_content"}
<iframe src="{$gatewayUrl}" style="border:none; height:660px; margin-left:220px; width:560px;" name="paymorrow_iframe"
        id="payment_frame">
    <p>
        Ihr Browser kann leider keine eingebetteten Frames anzeigen:
        Sie k&ouml;nnen die eingebettete Seite &uuml;ber den folgenden Verweis
        aufrufen: <a href="http://www.paymorrow.de">Paymorrow</a>
    </p>
</iframe>
{/block}

