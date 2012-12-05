<script type="text/javascript">
Shopware.Wizard.ProductsCategories = Ext.extend(Ext.tree.TreePanel,{
	title:'Kategorien auswählen',
	//region:'west',
	//split:true,
	//fitToFrame: true,
	animate:false,
	width: 200,
	//height:'100%',
	//margins:'10 0 0 0',
	//minSize: 175,
	//enableDD: false,
	//enableEdit: false,
	autoScroll: true,
	rootVisible: false,
	initComponent: function() {

		this.loader = new Ext.tree.TreeLoader({ dataUrl: '{url action=getCategories}'});
		this.root = new Ext.tree.AsyncTreeNode({ id: '1' });
		/*
		this.bbar = new Ext.Toolbar({
			items: [new Ext.Button({ text: 'Kategorie komplett hinzufügen',
				handler: function (){
					var node = this.getSelectionModel().getSelectedNode();
					if (!node) {
						return;
					}
					Ext.MessageBox.show({
				           title: 'Frage',
				           msg: 'Sollen  '+ node.attributes.countArticles +' Artikel der Kategorie '+node.attributes.text+' hinzugefügt werden?',
				           width:300,
				           buttons: Ext.MessageBox.OKCANCEL,
				           //fn: this.addCategory(node.attributes,[this,0],true),
				           animEl: 'mb3'
					});
				},
				scope:this
			})]
		});
		*/
		Shopware.Wizard.ProductsCategories.superclass.initComponent.call(this);
	}
});
</script>
