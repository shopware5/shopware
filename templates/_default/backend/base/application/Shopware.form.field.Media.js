
//{namespace name=backend/application/main}

Ext.define('Shopware.form.field.Media', {

    extend: 'Ext.form.FieldContainer',

    alias: 'widget.shopware-media-field',

    mediaPath: '{link file=""}',
    noMedia: '{link file="templates/_default/frontend/_resources/images/no_picture.jpg"}',

    layout: {
        type: 'hbox',
        align: 'stretch'
    },

    /**
     * List of classes to mix into this class.
     * @type { Object }
     */
    mixins: [
        'Shopware.model.Helper',
        'Ext.form.field.Base'
    ],

    height: 115,

    /**
     * Override required!
     * This function is used to override the { @link #displayConfig } object of the statics() object.
     *
     * @returns { Object }
     */
    configure: function() {
        return { };
    },

    /**
     * Get the reference to the class from which this object was instantiated. Note that unlike self, this.statics()
     * is scope-independent and it always returns the class from which it was called, regardless of what
     * this points to during run-time.
     *
     * The statics object contains the shopware default configuration for
     * this component. The different shopware configurations are stored
     * within the displayConfig object.
     *
     * @type { object }
     */
    statics: {
        /**
         * The statics displayConfig contains the default shopware configuration for
         * this component.
         * To set the shopware configuration, you can set the displayConfig directly
         * as property of the component:
         *
         * @example
         *      Ext.define('Shopware.apps.Product.controller.Detail', {
         *          extend: 'Shopware.detail.Controller',
         *          displayConfig: {
         *              eventAlias: 'product',
         *              ...
         *          }
         *      });
         */
        displayConfig: {
            selectButtonText: '{s name="media_field/select_button_text"}Select media{/s}',
            resetButtonText: '{s name="media_field/reset_button_text"}Reset media{/s}'
        },

        /**
         * Static function to merge the different configuration values
         * which passed in the class constructor.
         * @param { Object } userOpts
         * @param { Object } definition
         * @returns Object
         */
        getDisplayConfig: function (userOpts, definition) {
            var config = { };

            if (userOpts && typeof userOpts.configure == 'function') {
                config = Ext.apply({ }, config, userOpts.configure());
            }
            if (definition && typeof definition.configure === 'function') {
                config = Ext.apply({ }, config, definition.configure());
            }
            config = Ext.apply({ }, config, this.displayConfig);

            return config;
        },


        /**
         * Static function which sets the property value of
         * the passed property and value in the display configuration.
         *
         * @param prop
         * @param val
         * @returns boolean
         */
        setDisplayConfig: function (prop, val) {
            var me = this;

            if (!me.displayConfig.hasOwnProperty(prop)) {
                return false;
            }
            me.displayConfig[prop] = val;
            return true;
        }
    },


    /**
     * Class constructor which merges the different configurations.
     * @param opts
     */
    constructor: function (opts) {
        var me = this;

        me._opts = me.statics().getDisplayConfig(opts, this);
        me.callParent(arguments);
    },


    /**
     * Helper function to get config access.
     * @param prop string
     * @returns mixed
     * @constructor
     */
    getConfig: function (prop) {
        var me = this;
        return me._opts[prop];
    },

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
            text: me.getConfig('selectButtonText'),
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
            text: me.getConfig('resetButtonText'),
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