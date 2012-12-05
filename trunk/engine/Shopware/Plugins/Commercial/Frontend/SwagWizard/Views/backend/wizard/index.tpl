{extends file="backend/index/parent.tpl"}

{block name="backend_index_css" append}

<link href="{link file='backend/_resources/styles/Ext.ux.FileUploadField.css'}" rel="stylesheet" type="text/css" />
<style type="text/css">
	a.ico {
		height:20px;
		margin:0 0 0 5px;
		padding:0;
		width:20px;
		cursor:pointer;
		float:left;
	}
</style>
{/block}


{block name="backend_index_javascript" append}
<script type="text/javascript" src="{link file='engine/Library/TinyMce/tiny_mce.js'}"></script>
<script type="text/javascript" src="{link file='backend/_resources/javascript/plugins/Ext.ux.TinyMCE-Wizard.js'}"></script>
<script type="text/javascript" src="{link file='backend/_resources/javascript/plugins/Ext.ux.FileUploadField.js'}"></script>
<script type="text/javascript" src="{link file='templates/_default/frontend/_resources/javascript/jquery-1.7.2.min.js'}"></script>
<script type="text/javascript">

/* {if !$licenceCheck} */
alert('License check for module "SwagWizard" has failed.');
/* {/if} */


Ext.ns('Shopware.Wizard');
</script>



{include file='backend/wizard/filter.tpl'}
{include file='backend/wizard/filter_form.tpl'}
{include file='backend/wizard/filter_products.tpl'}
{include file='backend/wizard/detail.tpl'}

{include file='backend/wizard/detail_form.tpl'}

{include file='backend/wizard/detail_products.tpl'}
{include file='backend/wizard/detail_categories.tpl'}
{include file='backend/wizard/products_categories.tpl'}
{include file='backend/wizard/products_pool.tpl'}
{include file='backend/wizard/products_selection.tpl'}
{include file='backend/wizard/tree.tpl'}
{include file='backend/wizard/view.tpl'}

{include file='backend/wizard/statistics.tpl'}
{include file='backend/wizard/detail_statistics.tpl'}
{include file='backend/wizard/statistics_overview.tpl'}
{include file='backend/wizard/statistics_orders.tpl'}
<script type="text/javascript">
var Wizard;
Ext.onReady(function(){
	Ext.QuickTips.init();
	Wizard = new Shopware.Wizard.View;
});
</script>
{/block}
