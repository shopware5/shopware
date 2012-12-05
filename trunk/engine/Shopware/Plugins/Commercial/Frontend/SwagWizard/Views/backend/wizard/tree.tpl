<script type="text/javascript">
Shopware.Wizard.Tree = Ext.extend(Ext.tree.TreePanel,{
	region: 'west',
	split: true,
	fitToFrame: true,
	animate: false,
	title: 'Angelegte Berater',
	width: 250,
	height: '100%',
	margins: '0 0 0 0',
	minSize: 175,
	enableDD: true,
	autoScroll: true,
	rootVisible: false,
	//border:false,
	initComponent: function() {
		this.loader = new Ext.tree.TreeLoader({
			dataUrl:'{url action=wizardList}'
		});
		this.root = new Ext.tree.AsyncTreeNode({
			 id: 'root'
		});
		this.reload = function() {
			this.root.reload();
		};
		this.bbar = new Ext.Toolbar({
	    	items: [{
				text: 'Neu',
				handler: function (){
					var node = this.getSelectionModel().getSelectedNode();
					if (!node){
					 	Ext.MessageBox.show({
					           title: 'Hinweis',
					           msg: 'Bitte wählen Sie zuerst einen Shop aus, indem Sie einen neuen Berater anlegen möchten!',
					           width:300,
					           buttons: Ext.MessageBox.OK,
					           animEl: 'mb3'
			         	});
			         	return;
					}
					new Shopware.Wizard.Detail({ id: node.attributes.id, title: 'Neuer Berater', shopID: node.attributes.id });
				},
				scope: this
			},{
				text: 'Löschen',
				handler: function (){
					var node = this.getSelectionModel().getSelectedNode();
					if (!node) {
			         	return;
					}
					if(node.attributes.type=='wizard') {
						Ext.MessageBox.confirm('Bestätigung', 'Wollen Sie wirklich diesen Berater löschen?', function(r) {
							if(r!='yes') {
								this.reload();
								return;
							}
							$.ajax({
					    		url: '{url action="deleteWizard"}',
					    		method: 'post',
					    		context: this,
					    		data: { 'wizardID': node.attributes.wizardID },
					    		dataType: 'json',
					    		success: function(result) {
									this.reload();
					    		}
					    	});
						}, this);
					} else if(node.attributes.type=='filter') {
						Ext.MessageBox.confirm('Bestätigung', 'Wollen Sie wirklich diesen Filter löschen?', function(r) {
							if(r!='yes') {
								this.reload();
								return;
							}
							$.ajax({
					    		url: '{url action="deleteFilter"}',
					    		method: 'post',
					    		context: this,
					    		data: { 'filterID': node.attributes.filterID},
					    		dataType: 'json',
					    		success: function(result) {
									node.parentNode.reload();
					    		}
					    	});
						}, this);
					}
				},
				scope: this
			}]
	    });
	    this.on('click', function(e){
	 		var wizardID = e.attributes.wizardID;
	 		var filterID = e.attributes.filterID;
	 		var typeID = e.attributes.typeID;
	 		var text = e.attributes.text;
	 		var type = e.attributes.type;
	 		if (type=='wizard') {
	 			new Shopware.Wizard.Detail({ id: e.attributes.id, wizardID: wizardID, title: 'Berater: '+text });
	 		} else if(type=='filter') {
	 			new Shopware.Wizard.Filter({ id: e.attributes.id, filterID: filterID, wizardID: wizardID, typeID: typeID, title: 'Filter: '+text });
	 		}
		},this);


		this.on('nodedrop',function(e){
			if(e.point=='append') {
				var target = e.target;
			} else {
				var target = e.target.parentNode;
			}
			var node = e.dropNode;

			if(node.attributes.type=='wizard') {
				Ext.MessageBox.confirm('Confirm', 'Wollen Sie wirklich diesen Berater kopieren?', function(r) {
					if(r!='yes') {
						this.reload();
						return;
					}
					$.ajax({
			    		url: '{url action="copyWizard"}',
			    		method: 'post',
			    		context: this,
			    		data: { 'wizardID': node.attributes.wizardID, 'shopID': target.id },
			    		dataType: 'json',
			    		success: function(result) {
							this.reload();
			    		}
			    	});
				}, this);
			} else if(node.attributes.type=='filter') {
				if(node.parentNode!=target) {
					Ext.MessageBox.confirm('Confirm', 'Wollen Sie wirklich diesen Filter kopieren?', function(r) {
						if(r!='yes') {
							this.reload();
							return;
						}
						$.ajax({
				    		url: '{url action="copyFilter"}',
				    		method: 'post',
				    		context: this,
				    		data: { 'filterID': node.attributes.filterID, 'wizardID': target.attributes.wizardID },
				    		dataType: 'json',
				    		success: function(result) {
								this.reload();
				    		}
				    	});
					}, this);
				} else {
					var data = [];
					target.eachChild(function(child, i) {
						data[data.length] = child.attributes.filterID;
					}, this);
					$.ajax({
			    		url: '{url action="moveFilter"}',
			    		method: 'post',
			    		context: this,
			    		data: { 'filter[]': data },
			    		dataType: 'json'
			    	});
				}
			}
		},this);

		this.on('nodedragover',function(e){
			if(e.point=='append') {
				var target = e.target;
			} else {
				var target = e.target.parentNode;
			}
			var node = e.dropNode;
			if(node.attributes.type=='wizard' && target.attributes.type=='shop') {
				return true;
			} else if(node.attributes.type=='filter' && target.attributes.type=='wizard') {
				return true;
			} else {
				return false;
			}
		},this);

	    Shopware.Wizard.Tree.superclass.initComponent.call(this);
	}
});
</script>
