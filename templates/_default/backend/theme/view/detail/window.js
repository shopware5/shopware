

Ext.define('Shopware.apps.Theme.view.detail.Window', {
    extend: 'Enlight.app.Window',
    alias: 'widget.theme-detail-window',
    title : '{s name=title}Theme details{/s}',
    height: 420,
    width: 1080,
    layout: 'fit',

    initComponent: function() {
        var me = this;

        me.items = [ me.createFormPanel() ];
        me.dockedItems = [ me.createToolbar() ];

        me.callParent(arguments);
    },

    createFormPanel: function() {
        var me = this;

        var counter = Math.round(me.elements.length / 2);

        me.formPanel = Ext.create('Ext.form.Panel', {
            layout: 'column',
            bodyPadding: 20,
            items: [
                me.createContainer(me.elements.slice(0, counter)),
                me.createContainer(me.elements.slice(counter))
            ]
        });
        return me.formPanel;
    },

    createContainer: function(fields) {
        return Ext.create('Ext.container.Container', {
            columnWidth: 0.5,
            defaults: {
                labelWidth: 150,
                anchor: '95%'
            },
            layout: 'anchor',
            items: fields
        });
    },



    createToolbar: function () {
        var me = this;

        me.toolbar = Ext.create('Ext.toolbar.Toolbar', {
            items: me.createToolbarItems(),
            dock: 'bottom'
        });

        return me.toolbar;
    },

    createToolbarItems: function() {
        var me = this, items = [];

        items.push({ xtype: 'tbfill' });

        items.push(me.createCancelButton());

        items.push(me.createSaveButton());

        return items;
    },


    /**
     * Creates the cancel button which will be displayed
     * in the bottom toolbar of the detail window.
     * The button handler will be raised to the internal
     * function me.onCancel
     *
     * @return Ext.button.Button
     */
    createCancelButton: function () {
        var me = this;

        me.cancelButton = Ext.create('Ext.button.Button', {
            cls: 'secondary',
            name: 'cancel-button',
            text: 'Cancel',
            handler: function () {

            }
        });
        return me.cancelButton;
    },

    /**
     * Creates the save button which will be displayed
     * in the bottom toolbar of the detail window.
     * The button handler will be raised to the internal
     * function me.onSave
     *
     * @return Ext.button.Button
     */
    createSaveButton: function () {
        var me = this;

        me.saveButton = Ext.create('Ext.button.Button', {
            cls: 'primary',
            name: 'detail-save-button',
            text: 'Save',
            handler: function () {
                me.fireEvent(
                    'saveConfig',
                    me.theme,
                    me.shop,
                    me.formPanel,
                    me
                );
            }
        });
        return me.saveButton;
    }

});
