{extends file='frontend/index/index.tpl'}
{block name="frontend_index_header_javascript" append}
<script type="text/javascript">
//<![CDATA[
	if(top!=self){
		top.location=self.location;
	}
//]]>
</script>
{/block}

{* Breadcrumb *}
{block name='frontend_index_start' append}
	{$sBreadcrumb = [['name'=>"{s name=PaymentTitle}Zahlung durchführen{/s}"]]}
{/block}


{* Main content *}
{block name='frontend_index_content'}
<div id="center" class="grid_13">
    
</div>
{/block}

{block name='frontend_index_actions'}{/block}
{*block name='frontend_index_checkout_actions'}{/block*}