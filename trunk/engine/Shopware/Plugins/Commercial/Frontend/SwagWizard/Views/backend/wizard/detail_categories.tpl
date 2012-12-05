<script type="text/javascript">
Shopware.Wizard.DetailCategories = Ext.extend(Ext.tree.TreePanel,{
	title:'Kategorie-Auswahl',
	buttonAlign:'right',
	enableDD: false,
    bodyStyle:'padding:10px',
	autoScroll: true,
	containerScroll: true,
	rootVisible:false,
	initComponent: function() {
		if(!this.wizardID) {
			this.disabled = true;
		}
		this.sm = new Ext.tree.MultiSelectionModel();
		this.loader = new Ext.tree.TreeLoader({
			dataUrl:'{url action="getCategories"}',
			baseParams: { wizardID: this.wizardID },
			baseAttr: {
				checked: false,
				uiProvider: Ext.tree.TreeNodeUI
			}
		});
		this.root = new Ext.tree.AsyncTreeNode({
			id:'1'
		});
		this.on('render', function(e){
			$.ajax({
				url:'{url action="getCategoryPaths"}',
				type: 'POST',
		    	context: this,
				data: { wizardID: this.wizardID },
				dataType: 'json', 
				success: function(result){
					for(var i = 0; i < result.data.length; i++){
						this.expandPath(result.data[i]);
					}
				}
			});
		}, this);
		
		this.buttons = [{
            text: 'Speichern',
            handler: function(){
            	var nodes = this.getChecked();
            	var categories = [];
            	for(var i = 0; i < nodes.length; i++){
	            	categories[i] = nodes[i].id;
	            }
				$.ajax({
		    		url: '{url action="saveCategories"}',
		    		type: 'POST',
		    		context: this, 
		    		data: { 'categories':categories, wizardID: this.wizardID },
		    		dataType: 'json',
		    		success: function(result) {
						Ext.MessageBox.show({
				           title: 'Hinweis',
				           msg: 'Kategorie-Auswahl wurde erfolgreich gespeichert',
				           buttons: Ext.MessageBox.OK,
				           animEl: 'mb9',
				           icon: Ext.MessageBox.INFO
						});
		    		}
		    	});
	        },
       		scope:this
        }];
        this.fbar = {
	    	height: 45,
		    items: this.buttons
		};
		this.buttons = null;
		
		Shopware.Wizard.DetailCategories.superclass.initComponent.call(this);
	}
});
</script>