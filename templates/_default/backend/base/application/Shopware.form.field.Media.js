
Ext.define('Shopware.form.field.Media', {

    extend: 'Ext.form.FieldContainer',

    alias: 'widget.shopware-media-field',

    mediaPath: '{link file=""}',
    noMedia: '{link file="templates/_default/frontend/_resources/images/no_picture.jpg"}',

    layout: {
        type: 'hbox',
        align: 'stretch'
    },

    mixins: [
        'Ext.form.field.Base'
    ],
    height: 115,


    value: undefined,

    path: undefined,
    mediaId: undefined,

    valueField: 'id',

    initComponent: function() {
        var me = this;

        me.items = me.createItems();
        me.callParent(arguments);
    },

    createItems: function() {
        var me = this;

        return [
            me.createButtonContainer(),
            me.createPreviewContainer()
        ];
    },

    createButtonContainer: function() {
        var me = this;

        return Ext.create('Ext.container.Container', {
            width: 180,
            padding: '0 10',
            style: "background: #fff",
            layout: {
                type: 'vbox',
                align: 'stretch'
            },
            items: [
                me.createMediaButton(),
                me.createDeleteButton()
            ]
        });
    },

    createPreviewContainer: function() {
        var me = this;

        return Ext.create('Ext.container.Container', {
            flex: 1,
            style: "background: #fff",
            items: [ me.createPreview() ]
        });
    },

    createMediaButton: function() {
        var me = this;

        return Ext.create('Ext.button.Button', {
            text: 'Select media',
            iconCls: 'sprite-inbox-image',
            cls: 'secondary small',
            margin: '10 0',
            handler: function() {
                me.openMediaManager()
            }
        });
    },

    createDeleteButton: function() {
        var me = this;

        return Ext.create('Ext.button.Button', {
            text: 'Reset media',
            iconCls: 'sprite-inbox--minus',
            cls: 'secondary small',
            handler: function() {
                me.removeMedia();
            }
        });
    },

    removeMedia: function() {
        var me = this;

        me.value = null;
        me.path = null;
        me.mediaId = null;
        me.preview.setSrc(me.noMedia);
    },

    createPreview: function() {
        var me = this;

        me.preview = Ext.create('Ext.Img', {
            src: me.mediaPath + me.value,
            height: 100,
            maxHeight: 100,
            padding: 5,
            margin: 5,
            style: "border-radius: 6px; border: 1px solid #c4c4c4;"
        });

        return me.preview;
    },


    openMediaManager: function() {
        var me = this;

        me.fireEvent('beforeOpenMediaManager', me);
        Shopware.app.Application.addSubApplication({
            name: 'Shopware.apps.MediaManager',
            layout: 'small',
            eventScope: me,
            params: {
                albumId: me.albumId
            },
            mediaSelectionCallback: me.onSelectMedia,
            selectionMode: me.multiSelect,
            validTypes: me.validTypes || []
        });
        me.fireEvent('afterOpenMediaManager', me);
    },


    onSelectMedia: function(button, window, selection) {
        var me = this,
            record = selection[0];

        if (!(record instanceof Ext.data.Model)) {
            return true;
        }

        me.record = record;
        me.path = record.get('path');
        me.mediaId = record.get('id');

        me.value = record.get(me.valueField);
        me.updatePreview(me.path);

        window.close();
    },


    updatePreview: function(image) {
        this.preview.setSrc(
            this.mediaPath + image
        );
    },

    getValue: function() {
        return this.value;
    },
    
    setValue: function(value) {
        var me = this;

        if (me.valueField === 'path') {
            me.path = value;
            me.mediaId = null;
        } else if (me.valueField === 'id') {
            me.mediaId = value;
            me.requestMediaPath(value);
        }

        this.value = value;
        this.updatePreview(value);
    },

    getSubmitData: function() {
        var value = {};
        value[this.name] = this.value;
        return value;
    },


    requestMediaPath: function(mediaId) {
        var me = this;

        Ext.Ajax.request({
            url: '{url controller=mediaManager action=getMedia}',
            method: 'POST',
            params: {
                mediaId: mediaId
            },
            success: function(response) {
                var operation = Ext.decode(response.responseText);

                if (operation.success == true) {
                    me.mediaId = operation.data.id;
                    me.path = operation.data.path;
                    me.updatePreview(me.path);
                }
            }
        });
    }
});