{extends file='frontend/index/index.tpl'}

{* Breadcrumb *}
{block name='frontend_index_start' append}
	{$sBreadcrumb = [['name'=>"{s name=PaymentTitle}Zahlung durchführen{/s}"]]}
{/block}

{block name='frontend_index_content_left'}{/block}

{if $hideLogin}
    {$height = '720'}
{else}
    {$height = '600'}
{/if}

{block name="frontend_index_content"}
<div id="payment" class="grid_20" style="margin:10px 0 10px 20px;width:959px;height="{$height}px !important;">
    <iframe src="{$gatewayUrl}"
            scrolling="yes"
            style="x-overflow: none;"
            frameborder="0">
    </iframe>
</div>
{/block}