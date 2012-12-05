{extends file="backend/index/parent.tpl"}

{block name="backend_index_css" append}
	<!-- Common CSS -->
	<link href="{link file='engine/backend/css/icons4.css'}"  rel="stylesheet" type="text/css" />
	<link href="{link file='engine/backend/css/modules.css'}" rel="stylesheet" type="text/css" />
{/block}
{block name="backend_index_body_attributes"}marginheight="0" marginwidth="0" topmargin="0" leftmargin="0" style="padding: 0px; background-color: #fff"{/block}
{block name="backend_index_body_inline"}
<center>
<div style="border: 4px solid #f00; padding: 10px; background-color: #fcc; font-size: 20px; line-height: 30px"><b>{$ERROR}</b></div>
</center>
{/block}
