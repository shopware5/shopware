//{namespace name=backend/application/main}
//{block name="backend/application/Shopware.form.field.Media"}
Ext.define('Shopware.form.field.Media', {

    extend: 'Ext.form.FieldContainer',

    /**
     * List of short aliases for class names. Most useful for defining xtypes for widgets
     * @type { String }
     */
    alias: 'widget.shopware-media-field',

    /**
     * List of classes to mix into this class.
     * @type { Object }
     */
    mixins: [
        'Shopware.model.Helper',
        'Ext.form.field.Base'
    ],

    /**
     * Contains the shopware base path.
     * Used to display the images.
     * @type { String }
     */
    mediaPath: '',

    /**
     * Url for the "no picture" image.
     * This image is displayed when the media field contains no value.
     * @type { String }
     */
    noMedia: '{link file="backend/_resources/images/index/no-picture.jpg"}',

    /**
     * Current value of the media field.
     * Can be set over the { @link #setValue } function.
     * To get the value use the { @link #getValue } function.
     */
    value: undefined,

    /**
     * Contains the media path of the current selected medium.
     * Event set if the media field use the id property of a medium.
     * @type { String }
     */
    path: undefined,

    /**
     * Contains the id of the media model.
     * @type { int }
     */
    mediaId: undefined,

    /**
     * Configuration which { @link #Shopware.apps.Base.model.Media } property
     * will be set as field value.
     * Possible values: 'path', 'id'
     *
     * @type { String }
     */
    valueField: 'id',

    /**
     * Defines if the media selection window can be minimized
     * @type { bool }
     */
    minimizable: true,

    /**
     * Contains the instance of the select button,
     * which created in the { @link #createSelectButton } function.
     * @type { Ext.button.Button }
     */
    selectButton: undefined,

    /**
     * Contains the instance of the reset button,
     * which created in the { @link #createResetButton } function.
     * @type { Ext.button.Button }
     */
    resetButton: undefined,

    /**
     * Contains the instance of the preview image.
     * The preview image is created in the { @link #showPreview } function.
     * @type { Ext.Img }
     */
    preview: undefined,

    /**
     * Contains an \Shopware\Models\Media\Album id
     * to filter the album tree of the media selection.
     * @type { int }
     */
    albumId: undefined,

    /**
     * Array of file types which allows to be select.
     *
     * @type { Array }
     */
    validTypes: [ ],

    /**
     * Record of the current selected media object.
     * This property is set through the { @link #requestMediaData } function.
     *
     * @type { Shopware.data.Model }
     */
    record: undefined,

    /**
     * Contains an instance of Ext.container.Container
     * which holds the { @link #selectButton } and the { @link #resetButton }
     * @type { Ext.container.Container }
     */
    buttonContainer: undefined,

    /**
     * Contains an instance of Ext.container.Container
     * which holds the { @link #preview } element.
     * This container is displayed on the right side of the media field.
     * @type { Ext.container.Container }
     */
    previewContainer: undefined,

    /**
     * Contains the text for the { @link #selectButton }.
     * The button is used to open the media selection and allows the user to select
     * a single media.
     * @type { String }
     */
    selectButtonText: '{s name="media_field/select_button_text"}Select media{/s}',

    /**
     * Contains the text for the { @link #resetButton }.
     * The reset button is used to remove an already assign media object.
     * @type { String }
     */
    resetButtonText: '{s name="media_field/reset_button_text"}Reset media{/s}',

    /**
     * Removes the white background of the media
     * @type { boolean }
     */
    removeBackground: false,

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
         * To set the shopware configuration, you can use the configure function and set an object as return value
         *
         * @example
         *      Ext.define('Shopware.apps.Product.view.detail.Media', {
         *          extend: 'Shopware.form.field.Media',
         *          configure: function() {
         *              return {
         *                  selectButtonText: 'Select medium',
         *                  ...
         *              }
         *          }
         *      });
         */
        displayConfig: {

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
     * Override required!
     * This function is used to override the { @link #displayConfig } object of the statics() object.
     *
     * @returns { Object }
     */
    configure: function() {
        return { };
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

    /**
     * The initComponent template method is an important initialization step for a Component.
     * It is intended to be implemented by each subclass of Ext.Component to provide any needed constructor logic.
     * The initComponent method of the class being created is called first, with each initComponent method up the hierarchy
     * to Ext.Component being called thereafter. This makes it easy to implement and, if needed, override the constructor
     * logic of the Component at any step in the hierarchy.
     * The initComponent method must contain a call to callParent in order to ensure that the parent class'
     * initComponent method is also called.
     * All config options passed to the constructor are applied to this before initComponent is called, so you
     * can simply access them with this.someOption.
     */
    initComponent: function() {
        var me = this;

        me.items = me.createItems();

        me.callParent(arguments);

        if (me.value) {
            me.requestMediaData(me.value);
        }
    },

    /**
     * Overwrite to create help text if passed
     *
     * @override
     */
    afterRender: function() {
        var me = this;

        me.callParent(arguments);

        if (me.helpText) {
            me.createHelp();
        }

        if (me.supportText) {
            me.createSupport()
        }
    },

    /**
     * Creates all components for this class.
     * The Shopware.form.field.Media component creates
     * a container for the { @link #resetButton } and { @link #selectButton }.
     * Additionally the media field contains a container to display
     * the current select image.
     * This container contains a { @link #Ext.Img } object.
     *
     * @returns { Ext.container.Container }
     */
    createItems: function() {
        var me = this,
            mainContainer = Ext.create('Ext.container.Container', {
                layout: {
                    type: 'hbox',
                    align: 'stretch'
                },
                items: [
                    me.createButtonContainer(),
                    me.createPreviewContainer()
                ]
            });

        return [
            mainContainer
        ]
    },

    /**
     * Creates the container for the { @link #selectButton } and
     * { @link #resetButton }.
     * This container will be displayed on the left side of the media field.
     *
     * @returns { Ext.container.Container }
     */
    createButtonContainer: function() {
        var me = this;

        me.buttonContainer = Ext.create('Ext.container.Container', {
            width: 180,
            padding: '0 10',
            style: me.removeBackground ? '' : 'background: #fff',
            layout: {
                type: 'vbox',
                align: 'stretch'
            },
            items: [
                me.createSelectButton(),
                me.createResetButton()
            ]
        });
        return me.buttonContainer;
    },

    /**
     * Creates the container for the { @link #preview } image.
     * This container is displayed on the right side of the media field.
     *
     * @returns { Ext.container.Container }
     */
    createPreviewContainer: function() {
        var me = this;

        me.previewContainer = Ext.create('Ext.container.Container', {
            flex: 1,
            style: me.removeBackground ? '' : 'background: #fff',
            items: [ me.createPreview() ]
        });
        return me.previewContainer;
    },

    /**
     * Creates the { @link #selectButton }, which displayed within the button
     * container on the left side of the media field.
     * The button handler function calls the internal { @link #openMediaManager } function
     * which opens the media selection.
     *
     * @returns { Ext.button.Button }
     */
    createSelectButton: function() {
        var me = this;

        me.selectButton = Ext.create('Ext.button.Button', {
            text: me.selectButtonText,
            iconCls: 'sprite-inbox-select',
            cls: 'secondary small',
            margin: '10 0',
            handler: function() {
                me.openMediaManager()
            }
        });

        return me.selectButton;
    },

    /**
     * Creates the { @link #resetButton }, which displayed within
     * the button container on the left side of the media field.
     * The button handler calls the internal { @link #removeMedia } function, which
     * reset the internal value properties and the preview image.
     *
     * @returns { Ext.button.Button }
     */
    createResetButton: function() {
        var me = this;

        me.resetButton = Ext.create('Ext.button.Button', {
            text: me.resetButtonText,
            iconCls: 'sprite-inbox--minus',
            cls: 'secondary small',
            handler: function() {
                me.removeMedia();
            }
        });

        return me.resetButton;
    },

    /**
     * Helper function which resets the internal value properties
     * and the preview image.
     */
    removeMedia: function() {
        var me = this;

        me.value = null;
        me.path = null;
        me.mediaId = null;
        me.preview.setSrc(me.noMedia);
    },

    /**
     * Creates the { @link #preview } image which displayed
     * within the preview container on the right side of the media field.
     *
     * @returns { Ext.Img }
     */
    createPreview: function() {
        var me = this, value;

        if (me.value == undefined) {
            value = me.noMedia;
        } else {
            value = me.mediaPath + me.value;
        }

        me.preview = Ext.create('Ext.Img', {
            src: value,
            height: 100,
            maxHeight: 100,
            padding: 5,
            margin: 5,
            style: "border-radius: 6px; border: 1px solid #c4c4c4;"
        });

        return me.preview;
    },

    /**
     * Helper function which opens the media manager in selection mode.
     * The media manager album tree can be filtered for a specify album id.
     * This id can be configured in the { @link #albumId } property of this
     * component.
     */
    openMediaManager: function() {
        var me = this;

        if (!(me.fireEvent('before-open-media-manager', me))) {
            return false;
        }

        Shopware.app.Application.addSubApplication({
            name: 'Shopware.apps.MediaManager',
            layout: 'small',
            eventScope: me,
            params: {
                albumId: me.albumId
            },
            mediaSelectionCallback: me.onSelectMedia,
            selectionMode: false,
            validTypes: me.validTypes || [],
            minimizable: me.minimizable
        });

        me.fireEvent('after-open-media-manager', me);
    },


    /**
     * Event listener function of the media manager.
     * This function is called, when the user selects
     * an image in the media selection and clicks the "accept selection" button.
     * The selected media object values will be assigned to the internal properties
     * { @link #path }, { @link #record }, { @link #mediaId } and { @link #value }.
     * Additionally the media image will be displayed in the { @link #preview } object.
     *
     * @param { Ext.button.Button } button
     * @param { Enlight.app.Window } window
     * @param { Array } selection
     * @returns { boolean }
     */
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

    /**
     * Helper function to update the { @link #preview } image.
     *
     * @param { String } image
     */
    updatePreview: function(image) {
        var me = this, src;

        if (Ext.isEmpty(image)) {
            src = me.noMedia;
        } else {
            src = me.mediaPath + image;
        }

        me.preview.setSrc(src);
    },

    /**
     * Returns the current value of the media field.
     * This function can returns different values:
     *  - undefined => No image is selected
     *  - string => Path of the media model (If the { @link #valueField } parameter is set to `path`)
     *  - int => Id of the media model (If the { @link #valueField } parameter is set to `id`)
     *
     * @returns { string|undefined|int }
     */
    getValue: function() {
        return this.value;
    },

    /**
     * Sets the current value of the media field.
     * This function is used by the { @link Ext.form.Base } object
     * to load a record into the form panel.
     *
     * @param value
     */
    setValue: function(value) {
        var me = this;

        if (value !== me.value) {
            me.requestMediaData(value);
        }

        this.value = value;
    },

    /**
     * This function is used if an { @link Ext.data.Model } will be
     * updated with the form data.
     * The function has to return an object with the values which will
     * be updated in the model.
     *
     * @returns { Object }
     */
    getSubmitData: function() {
        var value = {};
        value[this.name] = this.value;
        return value;
    },

    /**
     * Helper function which request the whole
     * media data for the current value.
     * This function is required to display the preview
     * image even if only a media id is passed to the media field.
     *
     * @param { string|int } value - Current value of the media field.
     */
    requestMediaData: function(value) {
        var me = this, params = {};

        if (!value) {
            me.updatePreview(null);
            return;
        }
        params[me.valueField] = value;

        Ext.Ajax.request({
            url: '{url controller=mediaManager action=getMedia}',
            method: 'POST',
            params: params,
            success: function(response) {
                var operation = Ext.decode(response.responseText);

                if (operation.success == true) {
                    me.record = Ext.create('Shopware.apps.Base.model.Media', operation.data);
                    me.mediaId = me.record.get('id');
                    me.path = me.record.get('path');
                    me.updatePreview(me.path);
                }
            }
        });
    },

    /**
     * Inserts the globe icon into the image
     *
     * @param { Ext.dom.Element } globe
     */
    insertGlobeIcon: function (globe) {
        var me = this;

        globe.setStyle({
            position: 'absolute',
            top: '14px',
            left: '14px',
            right: 'auto'
        });

        if (Ext.isDefined(me.previewContainer.getEl())) {
            me.previewContainer.getEl().appendChild(globe);
        }
    },

    isValid: function() {
        if (this.allowBlank || this.disabled || !Ext.isDefined(this.allowBlank)) {
            return true;
        }

        return typeof this.value === 'number' && this.value !== 0;
    }
});
//{/block}
