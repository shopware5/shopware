
//{namespace name=backend/theme/main}

Ext.define('Shopware.apps.Theme.view.list.extensions.Info', {
    extend: 'Shopware.listing.InfoPanel',
    alias: 'widget.theme-listing-info-panel',
    cls: 'theme-info-panel',
    width: 360,

    configure: function() {
        return {
            model: 'Shopware.apps.Theme.model.Theme',
            fields: {
                screen: '{literal}<div class="screen"><img src="{screen}" title="{name}" /></div>{/literal}',
                name: '<div class="info-item"> <p class="label">{s name=name}Name{/s}:</p> <p class="value">{literal}{name}{/literal}</p> </div>',
                author: '<div class="info-item"> <p class="label">{s name=author}Author{/s}:</p> <p class="value">{literal}{author}{/literal}</p> </div>',
                license: '<div class="info-item"> <p class="label">{s name=license}License{/s}:</p> <p class="value">{literal}{license}{/literal}</p> </div>',
                description: '<div class="info-item"> <p class="label">{s name=description}Description{/s}:</p> <p class="value">{literal}{description}{/literal}</p> </div>'
            }
        };
    },

    initComponent: function() {
        var me = this;

        me.dockedItems = [ me.createToolbar() ];

        me.callParent(arguments);
    },

    createToolbar: function() {
        var me = this;

        return Ext.create('Ext.toolbar.Toolbar', {
            dock: 'bottom',
            items: [
                me.createAssignButton(),
                me.createPreviewButton(),
                '-',
                me.createConfigureButton()
            ]
        });
    },

    createAssignButton: function () {
        var me = this;

        me.assignButton = Ext.create('Ext.button.Button', {
            text: '{s name=assign}Select theme{/s}',
            cls: 'small primary',
            disabled: true,
            handler: function() {
                me.fireEvent('assign-theme', me);
            }
        });


        return me.assignButton;
    },

    createPreviewButton: function () {
        var me = this;

        me.previewButton = Ext.create('Ext.button.Button', {
            text: '{s name=preview}Preview theme{/s}',
            disabled: true,
            cls: 'small',
            handler: function() {
                me.fireEvent('preview-theme', me);
            }
        });

        return me.previewButton;
    },

    createConfigureButton: function() {
        var me = this;

        me.configureButton = Ext.create('Ext.button.Button', {
            text: '{s name=configure}Configure theme{/s}',
            disabled: true,
            cls: 'small',
            handler: function() {
                me.fireEvent('configure-theme', me);
            }
        });

        return me.configureButton;
    },

    checkRequirements: function() { },
    addEventListeners: function() { }

});
