{extends file="backend/ext_js/index.tpl"}

{block name="backend_index_css" append}
<style type="text/css">
.action_icon {
	cursor: pointer;
	margin-right: 4px;
}
.action_hidden {
	display: none;
}
</style>
{/block}

{block name="backend_index_javascript" append}
<script type="text/javascript">
//<![CDATA[
Ext.application({
    name: 'PaymentBillsafe',
	appFolder: '{url action=load}',
	    
    controllers: [
    	'List'
    ],
    autoCreateViewport: false,
    
    launch: function() {
    	
    	//this.store = Ext.create('PaymentBillsafe.store.List');
    	//Ext.create('PaymentBillsafe.view.List');
    	/*
    	this.getView('List').setStore(
    		this.getView('Store')
    	);
    	this.getView('Viewport').addItem(
    		this.getView('List')
    	);
    	*/
    }
});
//]]>
</script>
{/block}