<script type="text/javascript">
Shopware.Wizard.DetailForm = Ext.extend(Ext.FormPanel,{
    labelWidth: 180,
    layout:'form',
    //frame:true,
    bodyStyle:'padding:5px 5px 0',
    title: 'Stammdaten',
    //autoHeight: true,
    autoScroll: true,
    fileUpload: true,
    defaultType: 'textfield',
    defaults:{ anchor: '-20', defaults:{ anchor: '100%' } },


    initComponent: function() {
        this.items = [{
            xtype: 'fieldset',
            title: 'Einstellungen',
            items: [{
                xtype:'textfield',
                fieldLabel: 'Name',
                name: 'name',
                allowBlank: false
            }, {
                fieldLabel: 'Beschreibung',
                xtype: "tinymce",
                height: 350,
                name:'description',
                value: ""
            },{
                xtype: 'combo',
                fieldLabel: 'Aktiv',
                store: [
                    [0, 'Nein'],
                    [1, 'Ja']
                ],
                value: 1,
                typeAhead: true,
                forceSelection: true,
                triggerAction: 'all',
                selectOnFocus:true,
                name:'active',
                hiddenName:'active',
            },{
                name: 'shopID',
                fieldLabel: 'Shop',
                xtype: 'combo',
                hiddenName:'shopID',
                store:  new Ext.data.Store({
                    url: '{url action="shopList"}',
                    autoLoad: true,
                    reader: new Ext.data.JsonReader({
                        root: 'data',
                        totalProperty: 'count',
                        id: 'id',
                        fields: ['id', 'name']
                    })
                }),
                emptyText:'Bitte wählen...',
                valueField: 'id',
                displayField: 'name',
                mode: 'remote',
                editable:false,
                selectOnFocus:true,
                triggerAction:'all',
                forceSelection : true
            },{
                xtype: 'fileuploadfield',
                emptyText: 'Bitte wählen...',
                fieldLabel: 'Grafik für Sidebar (Format: jpg, png oder gif)',
                name: 'image',
                buttonText: 'Auswählen'

            },{
                xtype: 'combo',
                fieldLabel: 'Artikel mit 0 Punkten verstecken',
                store: [
                    [0, 'Nein'],
                    [1, 'Ja']
                ],
                value: 0,
                typeAhead: true,
                forceSelection: true,
                triggerAction: 'all',
                selectOnFocus:true,
                name:'hide_empty',
                hiddenName:'hide_empty'
            },{
                xtype: 'combo',
                fieldLabel: 'Vorschau anzeigen',
                store: [
                    [0, 'Nein'],
                    [1, 'Ja']
                ],
                value: 1,
                typeAhead: true,
                forceSelection: true,
                triggerAction: 'all',
                selectOnFocus:true,
                name:'preview',
                hiddenName:'preview'
            },{
                xtype: 'numberfield',
                fieldLabel: 'Max. Menge an Ergebnissen',
                name:'max_quantity'
            },{
                xtype: 'combo',
                fieldLabel: 'Weitere Artikel anzeigen',
                store: [
                    [0, 'Nein'],
                    [1, 'Ja']
                ],
                value: 1,
                typeAhead: true,
                forceSelection: true,
                triggerAction: 'all',
                selectOnFocus:true,
                name:'show_other',
                hiddenName:'show_other'
            },{
                xtype: 'combo',
                fieldLabel: 'Als Seitenlisting ausgeben',
                store: [
                    [0, 'Nein'],
                    [1, 'Ja']
                ],
                value: 0,
                typeAhead: true,
                forceSelection: true,
                triggerAction: 'all',
                selectOnFocus:true,
                name:'listing',
                hiddenName:'listing'
            },{
                xtype: 'combo',
                fieldLabel: 'Banner anzeigen',
                store: [
                    [0, 'Nein'],
                    [1, 'Ja']
                ],
                value: 1,
                typeAhead: true,
                forceSelection: true,
                triggerAction: 'all',
                selectOnFocus:true,
                name:'sidebar',
                hiddenName:'sidebar'
            },{
                fieldLabel: 'Banner-Ausgabe',
                name: 'block',
                hiddenName:'block',
                valueField:'id',
                displayField:'name',
                triggerAction:'all',
                xtype: 'combo',
                allowBlank:false,
                mode: 'remote',
                emptyText:'Bitte wählen...',
                selectOnFocus:true,
                forceSelection : true,
                store:  new Ext.data.Store({
                    url: '{url action="getBlocks"}',
                    autoLoad: true,
                    reader: new Ext.data.JsonReader({
                        root: 'data',
                        totalProperty: 'count',
                        id: 'id',
                        fields: ['id', 'name']
                    })
                })
            }
            ]},{
            xtype: 'fieldset',
            title: 'Filter hinzufügen',
            disabled: !this.wizardID,
            items: [{
                xtype: 'panel',
                title: 'Filter-Typen',
                height: 100,
                style: { marginBottom: '10px'},
                html: '<strong><ul><li>'
                        + '<span style="font-weight:bold">Frage/Slider mit Produktausschluss - </span>Frage / Antwort Konstrukt um bestimmte Produkte auszuschließen.</li>'
                        + '<li><span style="font-weight:bold">Frage/Slider mit Scoring -</span> Frage / Antwort Konstrukt mit Punktvergabe je Antwort / Produkt.</li>'
                        + '<li><span style="font-weight:bold">Frage mit Mehrfach-Antworten -</span> Es können mehrere Antworten per Checkbox gegeben werden.</li>'
                        + '<li><span style="font-weight:bold">Filter nach Eigenschaften, Varianten, oder  -</span> Filtern nach einer im Artikel hinterlegten Eigenschaft.</li>'
                        + '<li><span style="font-weight:bold">Slider nach Preis -</span> Eine Preisspane kann mithilfe eines Sliders festgelegt werden.</li>'
                        + '</ul></strong>',
                border: true,
                frame: true
            },{
                xtype: 'combo',
                hiddenName:'typeID',
                name: 'addFilter',
                store:  new Ext.data.Store({
                    url: '{url action="filterTypeList"}',
                    autoLoad: true,
                    reader: new Ext.data.JsonReader({
                        root: 'data',
                        totalProperty: 'count',
                        id: 'id',
                        fields: ['id', 'name']
                    })
                }),
                emptyText:'Bitte wählen...',
                valueField: 'id',
                displayField: 'name',
                mode: 'remote',
                selectOnFocus: true,
                triggerAction: 'all',
                forceSelection : true,
                emptyText: 'Keinen neuen Filter einfügen',
                editable: false,
                listeners: {
                    'change': { fn:function(el, value, oldValue) {
                        new Shopware.Wizard.Filter({ typeID: value, wizardID: this.wizardID, title: 'Neuer Filter' });
                        el.setValue(null);
                    }, scope:this }
                }
            }]
        }];

        this.fbar = {
            height: 45,
            items:[{
                text: 'Speichern',
                handler: function(){
                    if(!this.getForm().isValid()) {
                        return;
                    }
                    this.getForm().findField('description').syncValue();
                    this.getForm().submit({ url: '{url action=saveWizard}', params: { id: this.wizardID}, success: function (form, action){
                        Ext.MessageBox.show({
                            title: 'Hinweis',
                            msg: 'Berater wurde erfolgreich gespeichert',
                            buttons: Ext.MessageBox.OK,
                            animEl: 'mb9',
                            icon: Ext.MessageBox.INFO
                        });
                        if(this.shopID) {
                            Wizard.Tree.getNodeById(this.shopID).reload();
                        }
                        var text = form.findField('name').getValue();
                        if (!this.wizardID && action.result.id){
                            Wizard.Tabs.remove(this.Parent);
                            new Shopware.Wizard.Detail({ wizardID: action.result.id, title: 'Berater: '+text });
                            this.Parent.destroy();
                        } else {
                            this.Parent.setTitle('Berater: '+text);
                        }
                    },
                        failure: function(form, action) {
                            switch (action.failureType) {
                                case Ext.form.Action.CLIENT_INVALID:
                                    //Ext.Msg.alert('Failure', 'Form fields may not be submitted with invalid values');
                                    break;
                                case Ext.form.Action.CONNECT_FAILURE:
                                    Ext.Msg.alert('Failure', 'Ajax communication failed');
                                    break;
                                case Ext.form.Action.SERVER_INVALID:
                                default:
                                    Ext.Msg.alert('Failure', action.result.message);
                                    break;
                            }
                        }, scope: this });
                },
                scope:this
            }]
        };

        Shopware.Wizard.DetailForm.superclass.initComponent.call(this);
        this.load({ url: '{url action=getWizard}', params: { id: this.wizardID, shopID: this.shopID } });
    },

    afterRender: function(){
        Shopware.Wizard.DetailForm.superclass.afterRender.call(this);
    }
});
</script>
