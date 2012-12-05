{extends file="backend/index/parent.tpl"}
{block name="backend_index_css" append}
	<!-- Common CSS -->
<link href="{link file='engine/backend/css/icons4.css'}"  rel="stylesheet" type="text/css" />

<style>
.body {
    font:normal 11px tahoma, arial, helvetica, sans-serif;
}
.search-item {
    font:normal 11px tahoma, arial, helvetica, sans-serif;
    padding:3px 10px 3px 10px;
    border:1px solid #fff;
    border-bottom:1px solid #eeeeee;
    white-space:normal;
    color:#555;
    cursor:pointer;
    height:50px;
}
.search-item h3 {
    display:block;
    font:inherit;
    font-weight:bold;
    color:#222;
}

.search-item h3 span {
    float: right;
    font-weight:normal;
    margin:0 0 5px 5px;
    width:100px;
    display:block;
    clear:none;
}
.x-action-col-cell .x-grid3-cell-inner {
    padding-top: 1px;
    padding-bottom: 1px;
}

.x-action-col-icon {
    cursor: pointer;
}

.x-grid3-hd-inner {
    position:relative;
	cursor:inherit;
	padding:4px 3px 4px 5px;
}

.x-grid3-row-body {
    white-space:normal;
}

.x-grid3-body-cell {
    -moz-outline:0 none;
    outline:0 none;
}
.statistics {
	font-size:12px;
	font-weight: bold;
}
</style>
{/block}

{block name="backend_index_body_inline"}
<script>
Ext.ns('Shopware.Recommendation');	


Shopware.Recommendation.Form = Ext.extend(Ext.FormPanel,
{
	labelWidth: 75, // label settings here cascade unless overridden
    frame:true,
    bodyStyle:'padding:5px 5px 0',
   //defaults: { width: 230},
    defaultType: 'textfield',
	initComponent: function() {
		
		
		this.items = [
				new Ext.Panel(
				{
					title: 'Wichtige Hinweise',
					height: 80,
					width: 650,
					style: { marginBottom: '0px'},
					html: '<strong>Die Namen der gültigen Blöcke können Sie in den korrespondierenden Template-Dateien einsehen! Für die Startseite /home/index.tpl. Für die Kategorie /listing/index.tpl.</strong>',
					border: true,
					frame: true
				}
				),
	            new Ext.form.FieldSet({
	            	title: 'Banner zu Slider zusammenfassen',
	            	width: 650,
	            	style: { marginTop: '0px',marginLeft:'0px'},
	            	frame: false,
	            	items: [
		           	 	new Ext.form.Checkbox({
			            	name: 'banner_active',
			            	checked: false,
			            	labelStyle: 'width:287px;padding:0px 0px 0px 0px',
			            	fieldLabel: 'Aktivieren',
			            	inputValue: 1
		           		 }) 
	            	]
	            }),
				new Ext.form.FieldSet({
	            	title: 'Neuheiten der Kategorie',
	            	width: 650,
	            	style: { marginTop: '0px',marginLeft:'0px'},
	            	frame: false,
	            	items: [
		            new Ext.form.Checkbox({
			            	name: 'new_active',
			            	checked: false,
			            	labelStyle: 'width:287px;padding:0px 0px 0px 0px',
			            	fieldLabel: 'Aktivieren',
			            	inputValue: 1
		           		 }) 
	            	]
	            }),
	            new Ext.form.FieldSet({
	            	title: '"Kunden haben sich ebenfalls angeschaut"',
	            	width: 650,
	            	style: { marginTop: '0px',marginLeft:'0px'},
	            	frame: false,
	            	items: [
		            	new Ext.form.Checkbox({
			            	name: 'bought_active',
			            	checked: false,
			            	labelStyle: 'width:287px;padding:0px 0px 0px 0px',
			            	fieldLabel: 'Aktivieren',
			            	inputValue: 1
		           		 }) 
	            	]
	            }), new Ext.form.FieldSet({
	            	title: 'Hersteller aus dieser Kategorie anzeigen',
	            	width: 650,
	            	style: { marginTop: '0px',marginLeft:'0px'},
	            	frame: false,
	            	items: [
		            	new Ext.form.Checkbox({
			            	name: 'supplier_active',
			            	checked: false,
			            	labelStyle: 'width:287px;padding:0px 0px 0px 0px',
			            	fieldLabel: 'Aktivieren',
			            	inputValue: 1
		           		 }) 
	            	]
	            })
        	];
        	
	        this.buttons = [{
		            text: 'Speichern',
		            handler: function(){
		            	
		            	this.getForm().submit({ url: '{url module=backend controller=RecommendationAdmin action=setConfig}',params: { id: this.edit}});
		            	
		            },
		            scope:this
	        }];
			Shopware.Recommendation.Form.superclass.initComponent.call(this);
			this.url = '{url module=backend controller=RecommendationAdmin action=saveCoupon}';
	}
}
);


Ext.QuickTips.init();
(function(){
	View = Ext.extend(Ext.Viewport, {
		layout: 'border',
		initComponent: function() {
			this.tree = new Ext.tree.TreePanel( {
					region:'west',
					split:true,
					fitToFrame: true,
					animate:false,
					title:'Kategorien',
					width: 200,
					height:'100%',
					margins:'0 0 0 0',
					minSize: 175,
					loader: new Ext.tree.TreeLoader({ dataUrl:'{url module=backend controller=RecommendationAdmin action=getCategories}'}),
					enableDD:false,
					enableEdit:false,
					autoScroll: true,
					rootVisible: false,
					root: new Ext.tree.AsyncTreeNode({ 
						 text: 'Test',
						 draggable:true,
						 id:'1'
					})
			});
			this.tree.parent = this;
			this.tree.on('click', function(e){
		 		var id = e.attributes.id;
		 		var text = e.attributes.text;
		 		var coupon = new Shopware.Recommendation.Form ({ parent: this, edit: id});
		 		coupon.load({ url: '{url module=backend controller=RecommendationAdmin action=getConfig}/id/'+id});
		 		
		 		coupon.on('actioncomplete',function(form,action){
		 			if (action.type=="load"){
		 				return;
		 			}
			    	if (action.result.id != null){
			    		form.findField('id').setValue(action.result.id);
					}
					//form.parent.tree.root.reload();
					Ext.MessageBox.show({
			           title: 'Hinweis',
			           msg: 'Kategorie wurde erfolgreich gespeichert',
			           buttons: Ext.MessageBox.OK,
			           animEl: 'mb9',
			           icon: Ext.MessageBox.INFO
		  			});
		    	});
		 		this.tabs.add({
		            title: 'Kategorie '+text,
		            items: [coupon],
		            closable:true
		        }).show();
				
			},this);
			
		   
		    
		    

			this.tabs = new Ext.TabPanel({
		        region: 'center',
		        activeTab: 0,
		        bodyBorder: false,
		        border: false,
		        plain:true,
		        hideBorders:false,
		        defaults:{ autoScroll: true},
		        items:[
		        ]
		    });
		

			this.items = [this.tree,this.tabs];
	        View.superclass.initComponent.call(this);
		}
	});
	Shopware.Recommendation.View = View;
})();;
Ext.onReady(function(){
	Ext.QuickTips.init();
	Recommendation = new Shopware.Recommendation.View;
});
</script>
{/block}