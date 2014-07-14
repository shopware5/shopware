{extends file="backend/index/parent.tpl"}

{block name="backend_index_body_inline"}
<script>
Ext.ns('Shopware.Document.Settings');
Ext.ux.IFrameComponent = Ext.extend(Ext.BoxComponent, {
 onRender : function(ct, position){
      this.el = ct.createChild({ tag: 'iframe', id: 'framepanel'+this.id, frameBorder: 0, src: this.url});
 }
});
	
(function(){
	View = Ext.extend(Ext.Viewport, {
	    layout: 'border',
	    addDoc: function(){
			var url = '{url module=backend controller=document action=addDocument}';
			Ext.Ajax.request({
				url : url,
				scope:this,
				method: 'GET',
					success: function ( result, request ) { 
						this.tree.root.reload();
					},
					failure: function ( result, request) { 
					} 
			});
	    	
	    },
	    deleteDoc: function(){
	    	var id = this.tree.getSelectionModel().getSelectedNode();
	    
			if (!id){
				alert('Bitte wählen Sie zunächst einen Beleg!');
				return;
			}
			id = id.attributes.id;
			
			if (id == 1 || id == 2 || id == 3 || id == 4){
				alert('Die Shopware Standardbelege können nicht enfernt werden');
				return;
			}
			var url = '{url module=backend controller=document action=deleteDocument}/id/'+id;
			Ext.Ajax.request({
				url : url,
				scope:this,
				method: 'GET',
					success: function ( result, request ) { 
						this.tree.root.reload();
					},
					failure: function ( result, request) { 
					} 
			});
			
	    },
	    initComponent: function() {
	    	this.tree = new Ext.tree.TreePanel( {
						region:'west',
						split:true,
						fitToFrame: true,
						animate:false,
						title:'Belegarten',
						width: 200,
						height:'100%',
						margins:'0 0 0 0',
						minSize: 175,
						loader: new Ext.tree.TreeLoader({ dataUrl:'{url module=backend controller=document action=getDocuments}'}),
						enableDD:false,
						enableEdit:false,
						autoScroll: true,
						rootVisible: false,
						root: new Ext.tree.AsyncTreeNode({ 
							 text: 'Test',
							 draggable:true,
							 id:'1'
						}),
						tbar: [
							new Ext.Button  ({
				            	text: 'Hinzufügen',
				            	handler: this.addDoc,
				            	scope:this
			             	}),
			             	new Ext.Button  ({
				            	text: 'Löschen',
				            	handler: this.deleteDoc,
				            	scope:this
			             	})
						]
			});
			this.tree.on('click', function(e){
				//e.attributes.id
				
		 		var url = '{url module=backend controller=document action=detail}/id/'+e.attributes.id;
		 		this.center.el.dom.src = url;
			},this);
			
			this.center = new Ext.ux.IFrameComponent({ 
							region:'center',
							split:true,
							animate:true, 
							fitToFrame: true,
							title:'Übersicht',
							width:700,
					        height:500,
							collapsible: true,
							id: "iframe", 
							url: '{url module=backend controller=document action=detail}' 
			});
						
	    	this.items = [
	    		this.tree, this.center
	    	];
	    	View.superclass.initComponent.call(this); 	
	    }
	});
	Shopware.Document.Settings.View = View;
})();
Ext.onReady(function(){
	Settings = new Shopware.Document.Settings.View;
});
</script>
{/block}