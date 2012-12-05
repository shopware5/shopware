Ext.define('Shopware.apps.Jira.view.create.Form', {
    extend: 'Ext.form.Panel',
    alias: 'widget.jira-view-create-form',
    border: 0,
    autoScroll: true,
    region: 'center',
    autoHeight:true,
    title: 'Neues Ticket erstellen',

    initComponent: function() {
        var me = this;
        //Creates an assigns the items of the form
        me.items = me.createFormItems();
        //Creates the buttons of the form
        me.buttons = me.createFormButtons();
        me.addEvents('createIssue');

        me.callParent( arguments );
    },

    createFormItems: function() {
        var me = this;


        me.fieldType = Ext.create('Ext.form.field.ComboBox', {
            fieldLabel: 'Ticket-Typ',
            labelWidth: 140,
            anchor: '100%',
            forceSelection: true,
            allowBlank: false,
            name: 'type',
            valueField: 'value',
            displayField: 'display',
            store: Ext.create('Ext.data.Store', {
                fields: [ 'value', 'display' ],
                data: [
                    { value: 4, display: 'Verbesserungsvorschlag (Improvement)' },
                    { value: 1, display: 'Fehler (Bug)' }
                ]
            })
        });

        me.fieldName = Ext.create('Ext.form.field.Text', {
            fieldLabel: 'Bezeichnung',
            labelWidth: 140,
            anchor: '100%',
            allowBlank: false,
            helpText: 'Kurzbeschreibung des Fehlers / Feature-Vorschlags - Diese sollte auch den Bereich enthalten, um den es geht - also z.B. REST-API oder Artikel-Konfigurator!',
            name: 'name'
        });

        me.fieldAuthor = Ext.create('Ext.form.field.Text', {
            fieldLabel: 'Ihre Name',
            labelWidth: 140,
            anchor: '100%',
            allowBlank: false,
            name: 'author'
        });

        me.fieldEmail = Ext.create('Ext.form.field.Text', {
            fieldLabel: 'Ihre eMail-Adresse',
            regex: Ext.data.validations.emailRe,
            regexText: 'Geben sie bitte eine g&uuml;ltige eMail-Adresse ein!',
            labelWidth: 140,
            anchor: '100%',
            allowBlank: false,
            name: 'email'
        });

        me.fieldDescription = Ext.create('Ext.form.field.TextArea', {
            fieldLabel: 'Beschreibung',
            labelWidth: 140,
            anchor: '100%',
            helpText: 'Geben Sie hier eine möglichst detailierte Beschreibung ein. Sollte es sich um einen Fehler handeln, beschreiben Sie möglichst detailiert, wie sich der Fehler reproduzieren lässt.',
            height: 200,
            name: 'description'
        });

        me.fieldSet = Ext.create('Ext.form.FieldSet', {
            margin: 20,
            labelWidth: 140,
            anchor: '100%',
            items: [
                me.fieldType,
                me.fieldName,
                me.fieldAuthor,
                me.fieldEmail,
                me.fieldDescription,
                { xtype: 'tbseparator' }
            ]
        });

        return [ me.fieldSet ];
    },

    createFormButtons: function() {
        var me = this;
        return [{
            text: 'Ticket anlegen',
            handler: function(el, e) {
            	var me = this,
                    values = me.getForm().getValues();

                //Checks if the form is valid
                if(!me.getForm().isValid()) {
                    Ext.Msg.show({
                        title: 'Hinweis',
                        msg: 'F&uuml;llen Sie bitte alle Felder aus!',
                        icon: Ext.Msg.ERROR,
                        buttons: Ext.Msg.OK
                    });                    
                    return;
                }

                me.fireEvent('createIssue', me, values);
            },
            cls:'primary',
            scope: me
        }];
    }
});