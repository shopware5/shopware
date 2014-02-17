
{extends file='frontend/index/index.tpl'}
{block name='frontend_index_header_title' prepend}{s name="ErrorIndexTitle"}{/s} | {/block}
{block name='frontend_index_content'}
<div id="center" class="grid_13">{include file='frontend/error/exception.tpl'}</div>
{/block}
{block name='frontend_index_actions'}{/block}
{block name='frontend_index_checkout_actions'}{/block}
{block name='frontend_index_search'}{/block}
{block name="frontend_index_header_javascript"}
{literal}
<script type="text/javascript">
//<![CDATA[
	if(top!=self){
		window.onload = function() {
			document.body.innerHTML = '<div style="padding:12px;background-color:#FFFFFF;">'+document.getElementById('center').innerHTML+'</div>';
		};
	}
//]]>
</script>
{/literal}
{/block}
