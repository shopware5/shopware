{extends file="backend/index/parent.tpl"}
{block name="backend_index_css" append}
	<!-- Common CSS -->
	<link href="{link file='engine/backend/css/icons4.css'}"  rel="stylesheet" type="text/css" />
	<link href="{link file='engine/backend/css/modules.css'}" rel="stylesheet" type="text/css" />
{/block}
{block name="backend_index_body_attributes"}marginheight="0" marginwidth="0" topmargin="0" leftmargin="0" style="padding: 0px; background-color: #fff;"{/block}
{block name="backend_index_body_inline"}<center><iframe id="payment_frame" width="550px" frameborder="0" border="0" src="{$HPUrl}" style="width: 550px; height: 2000px; border: 0px solid #000;"></iframe></center>{/block}
