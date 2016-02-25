
{extends file='frontend/index/index.tpl'}
{block name='frontend_index_header_title' prepend}{s name="ServiceIndexTitle"}Wartungsarbeiten{/s} | {/block}
{block name='frontend_index_content'}
<div class="grid_16 push_2 last">
<h2>{s name="ServiceHeader"}Wegen Wartungsarbeiten nicht erreichbar!{/s}</h2>
<p>
	{s name="ServiceText"}Aufgrund nötiger Wartungsarbeiten ist der Shop zur Zeit nicht erreichbar.{/s}
</p>
<div class="doublespace">&nbsp;</div>
</div>
{/block}

{block name='frontend_index_actions'}
    <div class="my_options">
        {action module=widgets controller=index action=shopMenu hideCurrency=true}
        <div class="clear">&nbsp;</div>
    </div>
{/block}
{block name='frontend_index_checkout_actions'}{/block}
{block name='frontend_index_search'}{/block}
{block name='frontend_index_content_left'}{/block}
{block name='frontend_index_footer'}{/block}
