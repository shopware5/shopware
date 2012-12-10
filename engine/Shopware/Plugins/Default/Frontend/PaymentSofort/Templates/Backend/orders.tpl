{extends file="backend/index/parent.tpl"}

{block name="backend_index_header"}
<style type="text/css">

.historyGrid div.x-grid3-cell-inner{
	font-style:italic;
}

.historyGrid {
	
}

.cancelInvoice {
	
}

.sofort-gradient {
	padding: 0;
	margin: 0;
	clear: both;
	background: url(../engine/vendor/ext/resources/images/default/grid/grid3-hrow.gif) repeat-x;
}

</style>
<script type="text/javascript" src="http://extjs.cachefly.net/ext-3.4.0/adapter/ext/ext-base.js"></script>
<script type="text/javascript" src="http://extjs.cachefly.net/ext-3.4.0/ext-all.js"></script>
<link rel="stylesheet" type="text/css" href="http://extjs.cachefly.net/ext-3.4.0/resources/css/ext-all.css" />
{/block}
{block name="backend_index_javascript"}
<script type="text/javascript" src="http://extjs.cachefly.net/ext-3.4.0/adapter/ext/ext-base.js"></script>
<script type="text/javascript" src="http://extjs.cachefly.net/ext-3.4.0/ext-all.js"></script>
<link rel="stylesheet" type="text/css" href="http://extjs.cachefly.net/ext-3.4.0/resources/css/ext-all.css" />
{/block}

{block name="backend_index_body_inline"}
	{include file="order/sofortActions.tpl"}
	{include file="order/checkColumn.tpl"}
	{include file="order/rowexpander.tpl"}
	{include file="order/gridfilters.tpl"}
	{include file="order/listfilter.tpl"}
	{include file="order/cookieManager.tpl"}
	{include file="order/grid.tpl"}
	<script type="text/javascript">
	Ext.ns('sofort.Extjs');
	 
	var sofortOrderView = Ext.extend(Ext.Viewport, {
		layout: 'border',
		initComponent: function() {
			grid = new sofortGrid();
			this.items = [grid];
			sofortOrderView.superclass.initComponent.call(this);
		}
	});
	
	Ext.onReady(function(){
		new sofortOrderView();
	});
	</script>
{/block}