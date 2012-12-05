{if 1 != 1}<script>{/if}
Ext.define('Shopware.B2B.TreeMenu',
{
	extend: 'Ext.tree.Panel',
    initComponent: function(){
		this.store = Ext.create('Ext.data.TreeStore', {
			root: {
				expanded: true,
				children: [
					{ text: "Kundengruppen Registrierung", leaf: true, id: 'UserRegister'},
					{ text: "Template-Variablen", leaf: true, id: 'TplConfigVariables' },
					{ text: "Template-Konfiguration", leaf: true, id: 'TplConfig' },
					{ text: "Private-Shopping / Shopping Club", leaf: true,id: 'PrivateShopping' }
				]
			}
		});
		
        Ext.apply(this, {
			region: 'west',
			title: 'Verfügbare Module',
			parent: this,
			useArrows: true,
			resizable: true,
			width: 300,
			rootVisible: false
        });

		this.on('itemclick',function(view,data){
			if (data.data.id){
				var useIfComponentExists = Ext.getCmp(data.data.id);

				if (!useIfComponentExists){
					var tabComponent = Ext.create('Shopware.B2B.'+data.data.id);
					tabComponent.autoScroll = true;
					// Doing to extjs core bug - disable close possibility on config-variables
					//if (tabComponent.id != "TplConfigVariables"){
						tabComponent.closable = true;
					//}

					this.tabPanel.add(tabComponent).show();
				}else {
					useIfComponentExists.show();
				}
			}
		});

        this.callParent(arguments);
    }
}
);